<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\Request;

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
