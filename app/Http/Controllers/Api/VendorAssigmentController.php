<?php

namespace App\Http\Controllers\Api;

use App\Models\VendorAssigment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VendorAssigmentController extends Controller
{
    protected $vendor;

    public function __construct(VendorAssigment $vendor)
    {
        $this->vendor = $vendor;
    }

    public function search(Request $request)
    {
        $tableColumn = $this->vendor->getTable();
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
                'message' => 'Successfully retrieved vendor data',
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
                'message' => 'Failed to retrieve vendor data',
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }

    private function customSearchData($payload, $tableColumn)
    {
        $data = VendorAssigment::where(function ($query) use ($payload, $tableColumn) {
            if (isset($payload['columns'])) {
                $listWhere = $payload['columns'];
                foreach ($listWhere as $where) {
                    $value = $where['value'];
                    if ($value && $value != "" && $value != " "  && $where['name'] != 'con_is_expired' && $where['name'] != 'con_priority_id' && $where['name'] != 'sts_description') {
                        $column = $where['name'];
                        $operator = strtolower($where['logic_operator']);
                        if ($operator == "=") {
                            $query->where($tableColumn . "." . $column, $value);
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