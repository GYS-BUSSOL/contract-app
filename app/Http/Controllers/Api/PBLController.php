<?php

namespace App\Http\Controllers\Api;

use App\Models\{PBL, TimeHistory};
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PBLController extends Controller
{
    protected $pbl;

    public function __construct(PBL $pbl)
    {
        $this->pbl = $pbl;
    }

    public function search(Request $request)
    {
        $tableColumn = $this->pbl->getTable();
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

            $resultData = [];

            foreach ($dataGet as $arrDataParent) {
                $csData = $this->customData($arrDataParent->con_req_id);
                $spkData = [
                    'aud_date' => null
                ];

                if (isset($csData['con_id']) && $arrDataParent->con_req_id === $csData['con_id']) {
                    $spkData = [
                        'aud_date' => !empty($csData['aud_date']) ? $csData['aud_date'] : null
                    ];
                }

                $mergedData = array_merge(
                    json_decode(json_encode($arrDataParent), true),
                    $spkData
                );

                $resultData[] = $mergedData;
            }

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved PBL data',
                'data' => [
                    'rows' => $resultData,
                    'total_data' => $totalShowData,
                    'total_record' => $totalRecord,
                    'total_expired' => $countExpiredData,
                    'total_not_expired' => $countNotExpiredData
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve PBL data: ' . $e->getMessage(),
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }

    private function customSearchData($payload, $tableColumn)
    {
        $data = PBL::where(function ($query) use ($payload, $tableColumn) {
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

    private function customData($con_req_id)
    {
        $audDate = TimeHistory::from('trn_time_history AS b')
            ->where([
                ['sts_id', 6],
                ['b.con_id', $con_req_id]
            ])
            ->orderBy('aud_date', 'desc')
            ->first();
        $data = [
            'aud_date' => $audDate['aud_date'],
            'con_id' => $audDate['con_id']
        ];
        return $data;
    }
}
