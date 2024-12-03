<?php

namespace App\Http\Controllers\Api;

use App\Models\SPK;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class SPKController extends Controller
{
    protected $spk;

    public function __construct(Contract $spk)
    {
        $this->spk = $spk;
    }

    public function search(Request $request)
    {
        $tableColumn = $this->spk->getTable();
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
                $conReqData = $this->customData();
                $arrConReqData = ['arr_con_req_no' => []];
                $arrConPPSNo = ['arr_con_pps_no' => []];

                foreach ($conReqData as $req) {
                    if (isset($req['spk_id']) && isset($arrDataParent->join_second_spk_id) && $arrDataParent->join_second_spk_id == $req['spk_id']) {
                        $arrConReqData['arr_con_req_no'][] = [
                            'con_req_no' => !empty($req['con_req_no']) ? $req['con_req_no'] : null,
                            'con_id' => !empty($req['con_id']) ? $req['con_id'] : null
                        ];
                        $arrConPPSNo['arr_con_pps_no'][] = [
                            'con_pps_no' => !empty($req['con_pps_no']) ? $req['con_pps_no'] : null,
                        ];
                    }
                }
                $mergedData = array_merge(
                    json_decode(json_encode($arrDataParent), true),
                    $arrConReqData,
                    $arrConPPSNo
                );
                $resultData[] = $mergedData;
            }

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved SPK List data',
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
                'message' => 'Failed to retrieve SPK List data',
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }

    public function searchReport(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable'],
            'end_date' => ['nullable'],
            'vendor_name' => ['nullable'],
            'cost_center' => ['nullable'],
            'spk_status' => ['nullable', 'in:Active,Not Active,All']
        ]);
        try {
            $dataGet = DB::select('EXEC [dbo].[Usp_GetSPKReport] @StartDate = ?, @EndDate = ?, @VendorName = ?, @CostCenter = ?, @SPKStatus = ?', [$validated['start_date'], $validated['end_date'], $validated['vendor_name'], $validated['cost_center'], $validated['spk_status']]);
            $totalShowData = count($dataGet);
            $dataGetAll = DB::select('EXEC [dbo].[Usp_GetSPKReport] @StartDate = ?, @EndDate = ?, @VendorName = ?, @CostCenter = ?, @SPKStatus = ?', [date('Y-m'), date('Y-m'), 'All', 'All', 'All']);
            $totalRecord = count($dataGet);
            $activeCount = 0;
            $notActiveCount = 0;

            foreach ($dataGetAll as $row) {
                if ($row->spk_status_active === 'Active') {
                    $activeCount++;
                } elseif ($row->spk_status_active === 'Not Active') {
                    $notActiveCount++;
                }
            }
            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved SPK report data',
                'data' => [
                    'rows' => $dataGet,
                    'total_data' => $totalShowData,
                    'total_record' => $totalRecord,
                    'total_active_spk' => $activeCount,
                    'total_not_active_spk' => $notActiveCount
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve SPK report data' . $e->getMessage(),
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }

    public function searchActive(Request $request)
    {
        $validated = $request->validate([
            'vendor_name' => ['nullable'],
            'cost_center' => ['nullable'],
        ]);
        try {
            $dataGet = DB::select('EXEC [dbo].[Usp_GetSPKActive_list] @VendorName = ?, @CostCenter = ?', [$validated['vendor_name'], $validated['cost_center']]);
            $totalShowData = count($dataGet);
            $totalRecord = count($dataGet);

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved SPK active data',
                'data' => [
                    'rows' => $dataGet,
                    'total_data' => $totalShowData,
                    'total_record' => $totalRecord,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve SPK active data' . $e->getMessage(),
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
                    if ($value && $value != "" && $value != " "  && $where['name'] != 'con_is_expired') {
                        $column = $where['name'];
                        $operator = strtolower($where['logic_operator']);
                        if ($operator == "in") {
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

    private function customData()
    {
        $conreq = SPK::select('tc.con_req_no', 'tc.con_id', 'tc.con_pps_no', 'tsc.con_req_id', 'ts.*')
            ->from('trn_spk AS ts')
            ->join('trn_spk_contract AS tsc', 'ts.spk_id', '=', 'tsc.spk_id')
            ->join('trn_contract AS tc', 'tsc.con_req_id', '=', 'tc.con_req_id')
            ->get();

        return $conreq;
    }
}
