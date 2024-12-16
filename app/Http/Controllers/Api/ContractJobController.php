<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{Auth, DB};
use App\Models\{Contract, ContractJob, ContractJobLabor, JobType, TimeHistory, MerVendor, ContractJobTarget};

class ContractJobController extends Controller
{
  protected $contractJob;
  protected $contract;

  public function __construct(ContractJob $contractJob, Contract $contract)
  {
    $this->contractJob = $contractJob;
    $this->contract = $contract;
  }

  public function add(Request $request)
  {
    $data = [
      'suggest_vendor' => ['required', 'string', 'exists:mer_vendor,vnd_id'],
      'con_req_id' => ['required', 'string'],
      'cjb_pay_template' => ['required', "array"],
      'cjb_pay_type' => ['required', "array"],
      'cjb_rate' => ['required', "array"],
      'cjb_id' => ['required', "array"],
    ];

    $validated = $this->handleValidationException($request, $data);
    if ($validated instanceof JsonResponse) {
      return $validated;
    }

    try {
      DB::beginTransaction();
      $currentDate = Carbon::now();
      $userName = Auth::user()->usr_display_name;
      $ip = $request->server('REMOTE_ADDR');

      $currentContract = $this->contract->firstWhere('con_req_id', $validated['con_req_id']);
      $currentContract->update([
        'sts_id' => '3',
        'ven_id' => $validated['suggest_vendor'],
        'con_ven_pic' => MerVendor::where('vnd_id', $validated['suggest_vendor'])->value('vnd_name'),
      ]);

      $data = [
        'validated' => $validated,
        'aud_machine' => $ip,
        'aud_date' => $currentDate,
        'aud_user' => $userName,
      ];

      $updateContractJob = $this->updateContractJob($data);

      if ($currentContract && $updateContractJob) {
        DB::commit();
        return response()->json([
          'status' => 200,
          'message' => 'Contract job created successfully'
        ], 200);
      }
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'status' => 500,
        'error' => 'Server error',
        'message' => 'Contract job failed to create' . $e->getMessage(),
      ], 500);
    }
  }

  public function rejectStatus(Request $request)
  {
    $data = [
      'con_comment_pbl_reject' => ['nullable', 'string'],
      'con_req_id' => ['required', 'string'],
    ];

    $validated = $this->handleValidationException($request, $data);
    if ($validated instanceof JsonResponse) {
      return $validated;
    }

    try {
      DB::beginTransaction();
      $currentDate = Carbon::now();
      $userName = Auth::user()->usr_display_name;
      $ip = $request->server('REMOTE_ADDR');

      $currentContract = $this->contract->firstWhere('con_req_id', $validated['con_req_id']);
      $currentContract->update([
        'sts_id' => '20',
        'con_comment_pbl_reject' => $validated['con_comment_pbl_reject'],
      ]);

      if ($currentContract) {
        $data = [
          'con_id' => $validated['con_req_id'],
          'sts_id' => '20',
          'ths_comment' => $validated['con_comment_pbl_reject'],
          'aud_user' => $userName,
          'aud_date' => $currentDate,
          'aud_prog' => 'CMSY',
          'aud_machine' => $ip,
        ];
        $this->addTimeHistory($data);
      }

      if ($currentContract) {
        DB::commit();
        return response()->json([
          'status' => 200,
          'message' => 'Contract job rejected successfully'
        ], 200);
      }
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'status' => 500,
        'error' => 'Server error',
        'message' => 'Contract job failed to reject' . $e->getMessage(),
      ], 500);
    }
  }

  public function list(string $conReq)
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

      $currentContractJob = $currentContractJob ? $currentContractJob : [];

      $merged = [];
      $jobType = [];
      $jobLabor = [];
      $jobTarget = [];
      $countJobLaborDistinc = [];
      $countJobLabor = 0;

      if ($currentContractJob) {
        foreach ($currentContractJob as $ccj) {
          $countJobLabor = ContractJobLabor::where('cjb_id', $ccj['cjb_id'])->count();
          $jobType[] = JobType::from('mer_job_type AS mjy')
            ->join('trn_contract_job_type AS tcjy', 'mjy.job_type_id', 'tcjy.cjtype_job_id')
            ->where('tcjy.cjb_id', $ccj['cjb_id'])
            ->get();
          $countJobLaborDistinc = ContractJobLabor::from('trn_contract_job_labor AS tcjl')
            ->join('mer_rate_tk AS mrt', 'tcjl.cjl_type', 'mrt.rtk_id_jenis_tk')
            ->where([
              ['tcjl.cjb_id', $ccj['cjb_id']],
              ['mrt.rtk_active_status', 'Active']
            ])
            ->distinct('mrt.rtk_rate')
            ->pluck('mrt.rtk_rate')
            ->toArray();
          $jobLabor[] = ContractJobLabor::where('cjb_id', $ccj['cjb_id'])
            ->get();
          $jobTarget[] = ContractJobTarget::where('cjb_id', $ccj['cjb_id'])
            ->get();
        }
        $arrCountJobLabor['count_job_labor'] = $countJobLabor;
        $arrCountJobLaborDistinc['count_job_labor_distinc'] = 0;
        if (is_array($countJobLaborDistinc)) {
          $arrCountJobLaborDistinc['count_job_labor_distinc'] = array_sum($countJobLaborDistinc);
        }
        $arrJobType['job_type'] = $jobType;
        $arrJobLabor['job_labor'] = $jobLabor;
        $arrJobTarget['job_target'] = $jobTarget;
        $arrCurrentContractJob['contract_job'] = $currentContractJob;
        $merged = array_merge($arrCurrentContractJob, $arrCountJobLabor, $arrCountJobLaborDistinc, $arrJobType, $arrJobLabor, $arrJobTarget);
      } else {
        return response()->json([
          "status" => 404,
          "message" => "Contract job data not found",
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

  public function edit(string $cjbId)
  {
    try {
      $currentContractJob = $this->contractJob->firstWhere('cjb_id', $cjbId);

      if ($currentContractJob) {
        $countJobLabor = ContractJobLabor::where('cjb_id', $currentContractJob['cjb_id'])->count();
        $jobType = JobType::from('mer_job_type AS mjy')
          ->join('trn_contract_job_type AS tcjy', 'mjy.job_type_id', 'tcjy.cjtype_job_id')
          ->where('tcjy.cjb_id', $currentContractJob['cjb_id'])
          ->get();
        $countJobLaborDistinc = ContractJobLabor::from('trn_contract_job_labor AS tcjl')
          ->join('mer_rate_tk AS mrt', 'tcjl.cjl_type', 'mrt.rtk_id_jenis_tk')
          ->where([
            ['tcjl.cjb_id', $currentContractJob['cjb_id']],
            ['mrt.rtk_active_status', 'Active']
          ])
          ->distinct('mrt.rtk_rate')
          ->pluck('mrt.rtk_rate')
          ->toArray();
        $jobLabor = ContractJobLabor::where('cjb_id', $currentContractJob['cjb_id'])
          ->get();
        $jobTarget = ContractJobTarget::where('cjb_id', $currentContractJob['cjb_id'])
          ->get();

        $arrCountJobLabor['count_job_labor'] = $countJobLabor;
        $arrCountJobLaborDistinc['count_job_labor_distinc'] = 0;
        if (is_array($countJobLaborDistinc)) {
          $arrCountJobLaborDistinc['count_job_labor_distinc'] = array_sum($countJobLaborDistinc);
        }
        $arrJobType['job_type'] = $jobType;
        $arrJobLabor['job_labor'] = $jobLabor;
        $arrJobTarget['job_target'] = $jobTarget;
        $arrCurrentContractJob['contract_job'] = $currentContractJob;
        $merged = array_merge($arrCurrentContractJob, $arrCountJobLabor, $arrCountJobLaborDistinc, $arrJobType, $arrJobLabor, $arrJobTarget);
      } else {
        return response()->json([
          "status" => 404,
          "message" => "Contract job data not found",
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
        "message" => "Failed to retrieve contract job data",
        "error" => "Server error",
      ], 500);
    }
  }

  private function updateContractJob(array $arrData)
  {
    $validated = $arrData['validated'];
    for ($i = 0; $i < count($validated['cjb_id']); $i++) {
      $updateContractJob = $this->contractJob
        ->where('cjb_id', $validated['cjb_id'][$i])
        ->update([
          'cjb_rate' => $validated['cjb_rate'][$i],
          'cjb_pay_type' => $validated['cjb_pay_type'][$i],
          'cjb_pay_template' => $validated['cjb_pay_template'][$i],
        ]);
      if ($updateContractJob) {
        $data = [
          'con_id' => $validated['con_req_id'],
          'sts_id' => '3',
          'ths_is_approval' => '0',
          'ths_comment' => 'Vendor Assignment',
          'ths_transaction_status' => '1',
          'aud_user' => $arrData['aud_user'],
          'aud_date' => $arrData['aud_date'],
          'aud_prog' => 'CMSY',
          'aud_machine' => $arrData['aud_machine'],
        ];
        $this->addTimeHistory($data);
      }

      $isSuccess = false;
      if ($updateContractJob) {
        $isSuccess = true;
      }

      return $isSuccess;
    }
  }

  private function addTimeHistory($data)
  {
    TimeHistory::create($data);
  }
}
