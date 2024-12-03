<?php

namespace App\Http\Controllers\Api;

use App\Models\Contract;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

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
}
