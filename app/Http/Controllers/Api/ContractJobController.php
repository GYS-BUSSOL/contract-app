<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{ContractJob, ContractJobLabor, ContractJobTarget, JobType, PaymentType};
use Illuminate\Http\Request;

class ContractJobController extends Controller
{
    protected $contractJob;

    public function __construct(ContractJob $contractJob)
    {
        $this->contractJob = $contractJob;
    }

    public function edit(string $conReq)
    {
        try {
            if (strpos($conReq, ',') !== false) {
                $conReq = explode(',', $conReq);
            }
            $currentContractJob = $this->contractJob
                ->when(is_array($conReq), function ($query) use ($conReq) {
                    return $query->orWhereIn('con_id', $conReq);
                }, function ($query) use ($conReq) {
                    return $query->orWhere('con_id', $conReq);
                })
                ->where(function ($query) {
                    $query->whereNull('aud_delete')
                        ->orWhere('aud_delete', '0');
                })
                ->get();

            $currentContractJob = $currentContractJob ? $currentContractJob->toArray() : [];

            $merged = [];
            if ($currentContractJob) {
                foreach ($currentContractJob as $ccj) {
                    $countJobLabor = ContractJobLabor::where('cjb_id', $ccj['cjb_id'])->count();
                    $jobType[] = JobType::from('mer_job_type AS mjy')
                        ->join('trn_contract_job_type AS tcjy', 'mjy.job_type_id', 'tcjy.cjtype_job_id')
                        ->where('tcjy.cjb_id', $ccj['cjb_id'])
                        ->first()
                        ->toArray();
                    $paymentType[] = PaymentType::where('paytype_code', $ccj['cjb_pay_type'])
                        ->first()
                        ->toArray();
                    $countJobLaborDistinc = ContractJobLabor::from('trn_contract_job_labor AS tcjl')
                        ->join('mer_rate_tk AS mrt', 'tcjl.cjl_type', '=', 'mrt.rtk_id_jenis_tk')
                        ->where([
                            ['tcjl.cjb_id', '=', $ccj['cjb_id']],
                            ['mrt.rtk_active_status', '=', 'Active']
                        ])
                        ->distinct('mrt.rtk_rate')
                        ->pluck('mrt.rtk_rate')
                        ->toArray();
                    $jobLabor[] = ContractJobLabor::where('cjb_id', $ccj['cjb_id'])
                        ->first()
                        ->toArray();
                    $jobTarget[] = ContractJobTarget::where('cjb_id', $ccj['cjb_id'])
                        ->first()
                        ->toArray();
                }
                $arrCountJobLabor['count_job_labor'] = $countJobLabor;
                $arrCountJobLaborDistinc['count_job_labor_distinc'] = array_sum($countJobLaborDistinc);
                $arrJobType['job_type'] = $jobType;
                $arrPaymentType['payment_type'] = $paymentType;
                $arrJobLabor['job_labor'] = $jobLabor;
                $arrJobTarget['job_target'] = $jobTarget;
                $arrCurrentContractJob['contract_job'] = $currentContractJob;
                $merged = array_merge($arrCurrentContractJob, $arrCountJobLabor, $arrCountJobLaborDistinc, $arrJobType, $arrPaymentType, $arrJobLabor, $arrJobTarget);
            } else {
                return response()->json([
                    "status" => 404,
                    "message" => "Contract data not found",
                ], 404);
            }
            return response()->json([
                "status" => 200,
                "message" => "Successfully retrieved contract job data",
                "data" => $merged
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to retrieve contract job data" . $e->getMessage(),
                "error" => "Server error",
            ], 500);
        }
    }
}
