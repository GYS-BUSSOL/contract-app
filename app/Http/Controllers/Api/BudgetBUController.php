<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\MerBUCCWC;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\BudgetHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetBUController extends Controller
{
    protected $merbuccwc;

    public function __construct(MerBUCCWC $merbuccwc)
    {
        $this->merbuccwc = $merbuccwc;
    }

    public function search(Request $request)
    {
        $tableColumn = $this->merbuccwc->getTable();
        try {
            $payload = $request->all();
            $countBuilderAll = $this->customSearchData($payload, $tableColumn);
            $dataBuilder = $this->setUpPayload($payload, $tableColumn);
            $builder = $dataBuilder['builder'];
            $countBuilderDistinct = $dataBuilder['distinct'];
            $dataGet = $builder->distinct()->get();
            $totalRecord = $countBuilderDistinct->get()->count();
            $totalShowData = $dataGet->count();
            $sumBudget = $countBuilderAll->where('bgt_year', $payload['active_year'])->sum('bgt_amount');
            $sumExpense = $countBuilderAll->where('bgt_year', $payload['active_year'])->sum('bgt_expense');
            $sumBalance = $countBuilderAll->where('bgt_year', $payload['active_year'])->sum('bgt_balance');

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved Budget BU data',
                'data' => [
                    'rows' => $dataGet,
                    'total_data' => $totalShowData,
                    'total_record' => $totalRecord,
                    'sum_budget' => $sumBudget,
                    'sum_expense' => $sumExpense,
                    'sum_balance' => $sumBalance
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve Budget BU data' . $e->getMessage(),
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }

    public function add(Request $request)
    {
        $data = [
            'year' => ['required', 'string'],
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

            $dataMerBUCCWC = $this->merbuccwc->where('group_dimension', 'Business Unit')->get();

            foreach ($dataMerBUCCWC as $data) {
                $budgetQuery = Budget::where([
                    ['bgt_year', $validated['year']],
                    ['bgt_bu', $data['number']]
                ])->first();

                if ($budgetQuery) {
                    $budgetQuery->update([
                        'aud_user' => $userName,
                        'aud_date' => $currentDate,
                        'aud_machine' => $ipAddress
                    ]);
                } else {
                    $budgetQuery = Budget::create(
                        [
                            'bgt_year' => $validated['year'],
                            'bgt_bu' => $data['number'],
                            'aud_user' => $userName,
                            'aud_date' => $currentDate,
                            'aud_machine' => $ipAddress
                        ]
                    );
                }
            }

            if ($budgetQuery) {
                DB::commit();
                return response()->json([
                    'status' => 200,
                    'message' => 'Budget BU created successfully'
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'error' => 'Server error',
                'message' => 'Budget BU failed to create',
            ], 500);
        }
    }

    public function edit(string $id, string $year)
    {
        try {
            $merbcw = $this->merbuccwc
                ->from('mer_bu_cc_wc AS mbcw')
                ->leftJoin('trn_budget AS tb', 'mbcw.number', 'tb.bgt_bu')
                ->where([
                    ['group_dimension', 'Business Unit'],
                    ['number', $id],
                    ['tb.bgt_year', $year]
                ])->first();

            if (empty($merbcw)) {
                return response()->json([
                    "status" => 404,
                    "message" => "Data not found",
                ], 404);
            }
            return response()->json([
                "status" => 200,
                "message" => "Successfully retrieved data",
                'data' => $merbcw
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to retrieve data",
                "error" => "Server error",
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        $data = [
            'year' => ['required', 'string'],
            'bgt_bu_head' => ['required'],
            'bgt_amount' => ['required']
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

            $budgetHistory = BudgetHistory::create([
                'bgth_year' => $validated['year'],
                'bgth_bu' => $id,
                'bgth_bu_head' => $validated['bgt_bu_head'],
                'bgth_amount' => $validated['bgt_amount'],
                'bgth_category' => 'budget',
                'aud_user' => $userName,
                'aud_date' => $currentDate,
                'aud_machine' => $ipAddress
            ]);

            $budget = Budget::where([
                ['bgt_bu', $id],
                ['bgt_year', $validated['year']]
            ])
                ->first();
            $totalBalance = $validated['bgt_amount'] - $budget['bgt_amount'] + $budget['bgt_balance'];

            $budget->update([
                'bgt_bu_head' => $validated['bgt_bu_head'],
                'bgt_amount' => $validated['bgt_amount'],
                'bgt_balance' => $totalBalance,
                'aud_user' => $userName,
                'aud_date' => $currentDate,
                'aud_machine' => $ipAddress
            ]);

            if ($budget && $budgetHistory) {
                DB::commit();
                return response()->json([
                    "status" => 200,
                    "message" => "Successfully updated budget data",
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                "status" => 500,
                "message" => "Failed to updated budget data",
                "error" => "Server error",
            ], 500);
        }
    }

    private function customSearchData($payload, $tableColumn)
    {
        $data = MerBUCCWC::where(function ($query) use ($payload, $tableColumn) {
            if (isset($payload['columns'])) {
                $listWhere = $payload['columns'];
                foreach ($listWhere as $where) {
                    $value = $where['value'];
                    if ($value && $value != "" && $value != " "  && $where['name'] != 'bgt_year') {
                        $column = $where['name'];
                        $operator = strtolower($where['logic_operator']);
                        if ($operator == "=") {
                            $query->where($tableColumn . "." . $column, $value);
                        }
                    }
                }
            }
        })
            ->leftJoin('trn_budget AS tb', $tableColumn . '.number', 'tb.bgt_bu')
            ->distinct()
            ->get();

        return $data;
    }
}
