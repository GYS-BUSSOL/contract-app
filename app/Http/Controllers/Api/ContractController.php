<?php

namespace App\Http\Controllers\Api;

use App\Models\{Contract, TrnBUCCWC, TrnShift};
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContractController extends Controller
{
    protected $contract;

    public function __construct(Contract $contract)
    {
        $this->contract = $contract;
    }

    public function edit(string $conReq)
    {
        try {
            if (strpos($conReq, ',') !== false) {
                $conReq = explode(',', $conReq);
            }

            $currentContract = $this->contract
                ->when(is_array($conReq), function ($query) use ($conReq) {
                    return $query->orWhereIn('con_req_id', $conReq);
                }, function ($query) use ($conReq) {
                    if (is_numeric($conReq)) {
                        return $query->orWhere('con_req_id', $conReq);
                    } else {
                        return $query->orWhere('con_req_no', $conReq);
                    }
                })
                ->where(function ($query) {
                    $query->whereNull('aud_delete')
                        ->orWhere('aud_delete', '0');
                })
                ->leftJoin('mer_bu_cc_wc', 'trn_contract.con_bu', '=', 'mer_bu_cc_wc.number')
                ->leftJoin('mer_company', 'trn_contract.con_company', '=', 'mer_company.company_code')
                ->leftJoin('mer_vendor', 'trn_contract.ven_id', '=', 'mer_vendor.vnd_id')
                ->select(
                    'trn_contract.*',
                    'mer_bu_cc_wc.*',
                    'mer_company.*',
                    'mer_vendor.*'
                )
                ->first();

            $currentContract = $currentContract ? $currentContract->toArray() : [];

            $cc = TrnBUCCWC::leftJoin('mer_bu_cc_wc', 'trn_bu_cc_wc.tbc_code', 'mer_bu_cc_wc.number')
                ->where([
                    ['con_req_id', $currentContract['con_req_id']],
                    ['tbc_kategori', 'cc'],
                ])
                ->get()
                ->toArray();
            $wc = TrnBUCCWC::leftJoin('mer_bu_cc_wc', 'trn_bu_cc_wc.tbc_code', 'mer_bu_cc_wc.number')
                ->where([
                    ['con_req_id', $currentContract['con_req_id']],
                    ['tbc_kategori', 'wc'],
                ])
                ->get()
                ->toArray();
            $shift = TrnShift::where('sh_con_req_id', $currentContract['con_req_id'])->get()->toArray();
            $arrCc['cc'] = $cc;
            $arrWc['wc'] = $wc;
            $arrShift['shift'] = $shift;
            $merged = array_merge($currentContract, $arrCc, $arrWc, $arrShift);
            return response()->json([
                "status" => 200,
                "message" => "Successfully retrieved contract data",
                "data" => $merged
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to retrieve contract data" . $e->getMessage(),
                "error" => "Server error",
            ], 500);
        }
    }

    public function list()
    {
        try {
            $dataGet = $this->contract->where(function ($query) {
                $query->whereNull('aud_delete')
                    ->orWhere('aud_delete', '0');
            })
                ->whereNotIn('con_req_id', function ($query) {
                    $query->select('con_req_id')
                        ->from('trn_spk_contract');
                })
                ->where('sts_id', '6')
                ->orderBy('con_req_id', 'desc')
                ->get();
            $totalRecord = $dataGet->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved contract list',
                'data' => [
                    'rows' => $dataGet,
                    'total_record' => $totalRecord
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve contract list',
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }
}
