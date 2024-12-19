<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Contract;
use App\Models\TimeHistory;
use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Support\Facades\{DB, Auth};
use Illuminate\Http\{Request, JsonResponse};

class Approval2Controller extends Controller
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
            $dataBuilder = $this->setUpPayload($payload, $tableColumn);
            $builder = $dataBuilder['builder'];
            $countBuilderDistinct = $dataBuilder['distinct'];
            $dataGet = $builder->distinct()->get();
            $totalRecord = $countBuilderDistinct->get()->count();
            $totalShowData = $dataGet->count();
            $countPriorityData = $countBuilderDistinct->get()->where('con_priority_id', '1')->count();
            $countNotPriorityData = $countBuilderDistinct->get()->where('con_priority_id', '2')->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved Approval level 2 on going data',
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
                'message' => 'Failed to retrieve Approval level 2 on going data',
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
            $dataBuilder = $this->setUpPayload($payload, $tableColumn);
            $builder = $dataBuilder['builder'];
            $countBuilderDistinct = $dataBuilder['distinct'];
            $dataGet = $builder->distinct()->get();
            $totalRecord = $countBuilderDistinct->get()->count();
            $totalShowData = $dataGet->count();
            $countExpiredData = $countBuilderDistinct->get()->where('con_is_expired', '1')->count();
            $countNotExpiredData = $countBuilderDistinct->get()->whereNull('con_is_expired')->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved Approval level 2 completed data',
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
                'message' => 'Failed to retrieve Approval level 2 completed data',
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
            'con_comment_coo_approve' => ['nullable', 'string'],
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
                    'sts_id' => 6,
                    'con_ceo_name' => $userName,
                    'con_comment_coo_approve' => $validated['con_comment_coo_approve'],
                ]);

            $timeHistory = TimeHistory::create([
                'con_id' => $validated['con_req_id'],
                'sts_id' => 6,
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
                    'message' => 'Approval 2 updated successfully'
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'error' => 'Server error',
                'message' => 'Approval 2 failed to update',
            ], 500);
        }
    }

    public function addRejectCancel(Request $request)
    {
        $data = [
            'con_req_id' => ['required'],
            'con_comment_ceo' => ['required', 'string'],
            'con_bu' => ['required', 'exists:trn_contract,con_bu'],
            'avg_expense' => ['required', 'numeric'],
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
                    'sts_id' => 15,
                    'con_ceo_name' => $userName,
                    'con_comment_ceo' => $validated['con_comment_ceo'],
                ]);

            $budget = Budget::firstWhere('bgt_bu', $validated['con_bu']);
            $balance = $budget['bgt_balance'] + $validated['avg_expense'];
            $updateBudget = $budget->update([
                'bgt_balance' => $balance,
            ]);

            $timeHistory = TimeHistory::create([
                'con_id' => $validated['con_req_id'],
                'sts_id' => 15,
                'aud_user' => $userName,
                'aud_date' => $currentDate,
                'aud_prog' => 'CMSY',
                'aud_machine' => $ipAddress,
                'ths_comment' => $validated['con_comment_ceo'],
            ]);

            if ($contractUpdate && $timeHistory && $updateBudget) {
                DB::commit();
                return response()->json([
                    'status' => 200,
                    'message' => 'Approval 2 updated successfully'
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'error' => 'Server error',
                'message' => 'Approval 2 failed to update',
            ], 500);
        }
    }

    public function sendBU(Request $request)
    {
        $data = [
            'con_req_id' => ['required'],
            'con_comment_ceo' => ['required', 'string'],
            'con_bu' => ['required', 'exists:trn_contract,con_bu'],
            'avg_expense' => ['required', 'numeric'],
            'con_katrj_ceo_id' => ['required', 'exists:mer_kategori_reject,katrj_id']
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
                    'sts_id' => 7,
                    'con_ceo_name' => $userName,
                    'con_comment_ceo' => $validated['con_comment_ceo'],
                    'con_katrj_ceo_id' => $validated['con_katrj_ceo_id']
                ]);

            $budget = Budget::firstWhere('bgt_bu', $validated['con_bu']);
            $balance = $budget['bgt_balance'] + $validated['avg_expense'];
            $updateBudget = $budget->update([
                'bgt_balance' => $balance,
            ]);

            $timeHistory = TimeHistory::create([
                'con_id' => $validated['con_req_id'],
                'sts_id' => 7,
                'aud_user' => $userName,
                'aud_date' => $currentDate,
                'aud_prog' => 'CMSY',
                'aud_machine' => $ipAddress,
                'ths_comment' => $validated['con_comment_ceo'],
            ]);

            if ($contractUpdate && $timeHistory && $updateBudget) {
                DB::commit();
                return response()->json([
                    'status' => 200,
                    'message' => 'Approval 2 updated successfully'
                ], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 500,
                'error' => 'Server error',
                'message' => 'Approval 2 failed to update',
            ], 500);
        }
    }
}
