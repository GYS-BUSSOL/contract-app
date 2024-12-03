<?php

namespace App\Http\Controllers\Api;

use App\Models\{Reviewer, SPKContract};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class ReviewerController extends Controller
{
    protected $vendor;

    public function __construct(Reviewer $vendor)
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
            $countExpiredData = $countBuilderAll->where('con_is_expired', '1')->count();
            $countNotExpiredData = $countBuilderAll->whereNull('con_is_expired')->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved reviewer data',
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
                'message' => 'Failed to retrieve reviewer data',
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }

    private function customSearchData($payload, $tableColumn)
    {
        $data = Reviewer::where(function ($query) use ($payload, $tableColumn) {
            if (isset($payload['columns'])) {
                $listWhere = $payload['columns'];
                foreach ($listWhere as $where) {
                    $value = $where['value'];
                    if ($value && $value != "" && $value != " "  && $where['name'] != 'con_is_expired' && $where['name'] != 'sts_description') {
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
