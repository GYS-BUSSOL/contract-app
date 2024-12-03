<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MerBUCCWC;
use Illuminate\Http\Request;

class BudgetBUController extends Controller
{
    protected $vendor;

    public function __construct(MerBUCCWC $vendor)
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
