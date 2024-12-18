<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Budget;
use App\Models\Contract;
use App\Models\BudgetHistory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\TimeHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\{Request, JsonResponse};

class Approval1Controller extends Controller
{
    protected $contract;

    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
    }

    public function searchOngoing(Request $request)
    {
        $tableColumn = $this->contract->getTable();
        try {
            $payload = $request->all();
            $countBuilderAll = $this->customSearchData($payload, $tableColumn);
            $dataBuilder = $this->setUpPayload($payload, $tableColumn);
            $builder = $dataBuilder['builder'];
            $countBuilderDistinct = $dataBuilder['distinct'];
            $dataGet = $builder->distinct()->get();
            $totalRecord = $countBuilderDistinct->get()->count();
            $totalShowData = $dataGet->count();
            $countPriorityData = $countBuilderAll->where('con_priority_id', '1')->count();
            $countNotPriorityData = $countBuilderAll->where('con_priority_id', '2')->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved Approcal level 1 on going data',
                'data' => [
                    'rows' => $dataGet,
                    'total_data' => $totalShowData,
                    'total_record' => $totalRecord,
                    'total_priority' => $countPriorityData,
                    'total_not_priority' => $countNotPriorityData
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve Approcal level 1 on going data',
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }

    public function searchCompleted(Request $request)
    {
        $tableColumn = $this->contract->getTable();
        try {
            $payload = $request->all();
            $countBuilderAll = $this->customSearchData($payload, $tableColumn);
            $dataBuilder = $this->setUpPayload($payload, $tableColumn);
            $builder = $dataBuilder['builder'];
            $countBuilderDistinct = $dataBuilder['distinct'];
            $dataGet = $builder->distinct()->get();
            $totalRecord = $countBuilderDistinct->get()->count();
            $totalShowData = $dataGet->count();
            $countExpiredData = $countBuilderAll->where('con_is_expired', '1')->count();
            $countNotExpiredData = $countBuilderAll->whereNull('con_is_expired')->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved Approval level 1 completed data',
                'data' => [
                    'rows' => $dataGet,
                    'total_data' => $totalShowData,
                    'total_record' => $totalRecord,
                    'total_expired' => $countExpiredData,
                    'total_not_expired' => $countNotExpiredData
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve Approval level 1 completed data' . $e->getMessage(),
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }

    public function addApprove(Request $request)
    {
        $data = [
            'con_req_id' => ['required'],
            'con_comment_coo_approve' => ['required', 'string'],
            'grand_total' => ['required', 'numeric'],
            'avg_expense' => ['required', 'numeric'],
            'avg_balance' => ['required', 'numeric'],
        ];

        $validated = $this->handleValidationException($request, $data);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            DB::beginTransaction();
            $ipAddress = $request->server('REMOTE_ADDR');
            $currentDate = Carbon::now();
            $userName = Auth::user()->usr_display_name;

            $contractUpdate = $this->contract
                ->where('con_req_id', $validated['con_req_id'])
                ->update([
                    'sts_id' => 4,
                    'con_comment_coo_approve' => $validated['con_comment_coo_approve'],
                    'grandTotal' => $validated['grand_total'],
                ]);
            $contract = $this->contract->firstWhere('con_req_id', $validated['con_req_id']);

            $budget = Budget::where([
                ['bgt_bu', $contract['con_bu']],
                ['bgt_year', Carbon::parse($contract['con_duration_start'])->format('Y')]
            ])
                ->update([
                    'bgt_expense' => $validated['avg_expense'],
                    'bgt_balance' => $validated['avg_balance'],
                ]);

            $budgetHistory = BudgetHistory::create([
                [
                    'bgth_year' => Carbon::parse($contract['con_duration_start'])->format('Y'),
                    'bgth_bu' => $contract->con_bu,
                    'bgth_amount' => $validated['avg_expense'],
                    'bgth_category' => 'expense',
                    'aud_user' => $userName,
                    'aud_date' => $currentDate,
                    'aud_machine' => $ipAddress,
                ],
                [
                    'bgth_year' => Carbon::parse($contract['con_duration_start'])->format('Y'),
                    'bgth_bu' => $contract->con_bu,
                    'bgth_amount' => $validated['avg_balance'],
                    'bgth_category' => 'balance',
                    'aud_user' => $userName,
                    'aud_date' => $currentDate,
                    'aud_machine' => $ipAddress,
                ]
            ]);

            $timeHistory = TimeHistory::create([
                'con_id' => $validated['con_req_id'],
                'sts_id' => 4,
                'aud_user' => $userName,
                'aud_date' => $currentDate,
                'aud_prog' => 'CMSY',
                'aud_machine' => $ipAddress,
                'ths_comment' => $validated['con_comment_coo_approve'],
            ]);

            if ($contractUpdate && $budget && $budgetHistory && $timeHistory) {
                DB::commit();
                return response()->json([
                    'status' => 200,
                    'message' => 'Approval 1 updated successfully'
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'error' => 'Server error',
                'message' => 'Approval 1 failed to update',
            ], 500);
        }
    }

    public function addReject(Request $request)
    {
        $data = [
            'con_req_id' => ['required'],
            'con_comment_coo_approve' => ['required', 'string'],
        ];

        $validated = $this->handleValidationException($request, $data);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            DB::beginTransaction();
            $ipAddress = $request->server('REMOTE_ADDR');
            $currentDate = Carbon::now();
            $userName = Auth::user()->usr_display_name;

            $contractUpdate = $this->contract
                ->where('con_req_id', $validated['con_req_id'])
                ->update([
                    'sts_id' => 5,
                    'con_comment_coo_approve' => $validated['con_comment_coo_approve'],
                ]);

            $timeHistory = TimeHistory::create([
                'con_id' => $validated['con_req_id'],
                'sts_id' => 5,
                'aud_user' => $userName,
                'aud_date' => $currentDate,
                'aud_prog' => 'CMSY',
                'aud_machine' => $ipAddress,
                'ths_comment' => $validated['con_comment_coo_approve'],
            ]);

            if ($contractUpdate && $timeHistory) {
                DB::commit();
                return response()->json([
                    'status' => 200,
                    'message' => 'Approval 1 updated successfully'
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'error' => 'Server error',
                'message' => 'Approval 1 failed to update',
            ], 500);
        }
    }

    private function customSearchData($payload, $tableColumn)
    {
        $data = Contract::where(function ($query) use ($payload, $tableColumn) {
            if (isset($payload['columns'])) {
                $listWhere = $payload['columns'];
                foreach ($listWhere as $where) {
                    $value = $where['value'];
                    if ($value && $value != "" && $value != " "  && $where['name'] != 'con_is_expired' &&  $where['name'] != 'con_priority_id') {
                        $column = $where['name'];
                        $operator = strtolower($where['logic_operator']);
                        if ($operator == "=") {
                            $query->where($tableColumn . "." . $column, $value);
                        } else if ($operator == "notin") {
                            $query->whereNotIn($tableColumn . "." . $column, $value);
                        } else if ($operator == "in") {
                            $query->whereIn($tableColumn . "." . $column, $value);
                        } else if ($operator == "isnull") {
                            $query->WhereNull($tableColumn . "." . $column);
                        }
                    }
                }
            }
        })->distinct()->get();

        return $data;
    }
}
