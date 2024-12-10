<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Contract;
use App\Models\ContractJob;
use App\Models\ManDaysRate;
use App\Models\ContractJobType;
use App\Models\ContractJobLabor;
use App\Models\ContractJobTarget;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Http\{Request, JsonResponse};

class TrnJobTypeController extends Controller
{

  protected $contract;
  protected $contractJob;

  public function __construct(Contract $contract, ContractJob $contractJob)
  {
    $this->contract = $contract;
    $this->contractJob = $contractJob;
  }

  public function getRangeIncrement($requestId)
  {
    try {
      $row = $this->contract->firstWhere('con_req_id', $requestId);

      if (!$row) {
        return response()->json(['error' => 'Data not found'], 404);
      }

      $start = Carbon::parse($row['con_duration_start'])->startOfMonth();
      $end = Carbon::parse($row['con_duration_end'])->addMonth()->startOfMonth();

      $dates = [];
      for ($date = $start; $date < $end; $date->addMonth()) {
        $dates[] = $date->format('Y-m');
      }
      Log::info(['rangeIncrement' => $dates]);
      return response()->json([
        "status" => 200,
        "message" => "Successfully retrieved range increment data",
        "data" => [
          'rows' => $row
        ],
        'periode' => $dates
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        "status" => 500,
        "message" => "Failed to retrieve range increment data",
        "error" => "Server error",
      ], 500);
    }
  }

  public function add(Request $request)
  {
    $data = [
      'job_type' => ['required'],
      'job_desc' => ['required', 'string'],
      'pic' => ['required', 'string'],
      'payment_type' => ['required', 'exists:mer_payment_type,paytype_code'],
      'total_job_target_qty' => ['required', 'numeric'],
      'uom' => ['required', 'exists:mer_measurement_unit,unt_code'],
      'cjt_type' => ['nullable'],
      'cjt_qty' => ['nullable'],
      'total' => ['nullable'],
      'labor_type' => ['nullable', 'string'],
      'labor_qty' => ['nullable', 'string'],
      'con_id' => ['required', 'exists:trn_contract,con_id'],
      'periode' => ['required']
    ];

    $validated = $this->handleValidationException($request, $data);
    if ($validated instanceof JsonResponse) {
      return $validated;
    }
    Log::info(['validated' => $validated]);
    try {
      DB::beginTransaction();

      $currentDate = Carbon::now();
      $userName = 'Wahyu';
      $ip = $request->server('REMOTE_ADDR');

      $updateContract = $this->updateContract($validated['con_id']);
      $contractJobCount = $this->getContractJobCount($updateContract['con_req_id']);
      $arrContractJob = [
        'validated' => $validated,
        'contract_job_count' => $contractJobCount,
        'ip' => $ip,
        'current_date' => $currentDate,
        'user_name' => $userName,
        'con_req_id' => $updateContract['con_req_id']
      ];
      $contractJob = $this->addContractJob($arrContractJob);

      if (isset($validated['job_type']) && count(explode(',', $validated['job_type'])) > 0) {
        $this->addJobType($validated['job_type'], $arrContractJob);
      }

      if (isset($validated['labor_type']) && isset($validated['labor_qty']) && count(explode(',', $validated['labor_type'])) > 0 && count(explode(',', $validated['labor_qty'])) > 0) {
        $arrData = [
          'labor_qty' => $validated['labor_qty'],
          'labor_type' => $validated['labor_type'],
        ];
        $this->addJobLabor($arrData, $arrContractJob);
        $this->addJobTarget($validated['periode'], $arrContractJob);
      }


      if ($contractJob) {
        DB::commit();
        return response()->json([
          'status' => 200,
          'message' => 'Job type created successfully',
        ], 200);
      }
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'status' => 500,
        'error' => 'Server error',
        'message' => 'Job type failed to create' . $e->getMessage(),
      ], 500);
    }
  }

  public function edit(string $conReq)
  {
    try {
      $currentContract = $this->contract->firstWhere('con_req_id', $conReq);
      if (empty($currentContract)) {
        return response()->json([
          "status" => 404,
          "message" => "Contract not found",
        ], 404);
      }
      $jobType = [];
      $jobLabor = [];
      $jobTarget = [];
      Log::info(['currentContract' => $currentContract, 'conReq' => $conReq]);
      if ($currentContract) {
        $contractJobArray = $this->contractJob->where(
          'con_id',
          $currentContract['con_req_id']
        )
          ->get()->toArray();
        $jobType = ContractJobType::where(
          'con_id',
          $currentContract['con_req_id']
        )
          ->get()->toArray();
        $jobLabor = ContractJobLabor::where(
          'con_id',
          $currentContract['con_req_id']
        )
          ->get()->toArray();
        $jobTarget = ContractJobTarget::where('cjb_id', 'LIKE', $currentContract['con_req_id'] . '%')
          ->get()
          ->toArray();

        $arrContractJob['arr_contract_job'] = $contractJobArray;
        $arrJobType['arr_job_type'] = $jobType;
        $arrJobLabor['arr_job_labor'] = $jobLabor;
        $arrJobTarget['arr_job_target'] = $jobTarget;

        $mergedArray = array_merge($arrContractJob, $arrJobType, $arrJobLabor, $arrJobTarget);
      } else {
        $mergedArray = [];
      }

      return response()->json([
        "status" => 200,
        "message" => "Successfully retrieved job type data",
        "data" => $mergedArray
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        "status" => 500,
        "message" => "Failed to retrieve job type data",
        "error" => "Server error",
      ], 500);
    }
  }

  private function updateContract(int $conId)
  {
    $data = $this->contract->firstWhere('con_id', $conId);
    $result = $data->update([
      'sts_id' => 1
    ]);
    Log::info(['updateContract' => $result]);
    return $result;
  }

  private function getContractJobCount(string $conReqId)
  {
    $dataCount = $this->contractJob->where('con_id', $conReqId)->count();
    if ($dataCount > 0) {
      $newDataCount = $dataCount + 1;
    } else {
      $newDataCount = 1;
    }
    Log::info(['dataCount' => $dataCount, 'newDataCount' => $newDataCount]);
    $result = str_pad($newDataCount, 2, '0', STR_PAD_LEFT);
    Log::info(['getContractJobCount' => $result]);
    return $result;
  }

  private function addContractJob(array $arrContractJob)
  {
    $data = $this->contractJob->create([
      'con_id' => $arrContractJob['con_req_id'],
      'cjb_id' => $arrContractJob['con_req_id'] . $arrContractJob['contract_job_count'],
      'cjb_desc' => $arrContractJob['validated']['job_desc'],
      'cjb_pic' => $arrContractJob['validated']['pic'],
      'cjb_qty' => $arrContractJob['validated']['total_job_target_qty'],
      'unt_id' => $arrContractJob['validated']['uom'],
      'cjb_pay_type' => $arrContractJob['validated']['payment_type'],
      'cjb_transaction_status' => 0,
      'aud_user' => $arrContractJob['user_name'],
      'aud_date' => $arrContractJob['current_date'],
      'aud_prog' => 'CMSY',
      'aud_machine' => $arrContractJob['ip']
    ]);
    Log::info(['addContractJob' => $data, 'cjb_id' => $arrContractJob['con_req_id'] . $arrContractJob['contract_job_count']]);
    return $data;
  }

  private function addJobType(string $jobType, array $arrContractJob)
  {
    $arrJobType = explode(',', $jobType);
    foreach ($arrJobType as $type) {
      $data = [
        'con_id' => $arrContractJob['con_req_id'],
        'cjb_id' => $arrContractJob['con_req_id'] . $arrContractJob['contract_job_count'],
        'cjtype_job_id' => $type,
        'aud_user' => $arrContractJob['user_name'],
        'aud_date' => $arrContractJob['current_date'],
        'aud_prog' => 'CMSY',
        'aud_machine' => $arrContractJob['ip']
      ];
      ContractJobType::create($data);
    }

    return $data;
  }

  private function addJobLabor(array $arrData, array $arrContractJob)
  {
    $laborTypes = explode(',', $arrData['labor_type']);
    $laborQtys = explode(',', $arrData['labor_qty']);

    for ($i = 0; $i < count($laborQtys); $i++) {
      $rateLabor = ManDaysRate::where('rtk_id_jenis_tk', $laborTypes[$i])
        ->where('rtk_active_status', 'Active')
        ->orderBy('rtk_rate', 'desc')
        ->value('rtk_rate');

      ContractJobLabor::create([
        'con_id' => $arrContractJob['con_req_id'],
        'cjb_id' => $arrContractJob['con_req_id'] . $arrContractJob['contract_job_count'],
        'cjl_type' => $laborTypes[$i],
        'cjl_qty' => $laborQtys[$i],
        'cjl_transaction_status' => '0',
        'aud_user' => $arrContractJob['user_name'],
        'aud_date' => $arrContractJob['current_date'],
        'aud_prog' => 'CMSY',
        'aud_machine' => $arrContractJob['ip'],
        'cjl_rate' => $rateLabor,
      ]);
    }
  }

  private function addJobTarget(string $arrDate, array $arrContractJob)
  {
    $parseArray = explode(',', $arrDate);
    Log::info(["arrDate" => $arrDate, "parseArray" => $parseArray]);
    $cjt_year = array_map(fn($date) => explode('-', $date)[0], $parseArray);
    $cjt_month = array_map(fn($date) => explode('-', $date)[1], $parseArray);
    $cjtType = explode(',', $arrContractJob['validated']['cjt_type']);
    $cjtQty = explode(',', $arrContractJob['validated']['cjt_qty']);
    $total = explode(',', $arrContractJob['validated']['total']);

    for ($i = 0; $i < count($cjt_year); $i++) {
      ContractJobTarget::create([
        'cjb_id' =>  $arrContractJob['con_req_id'] . $arrContractJob['contract_job_count'],
        'cjt_year' => $cjt_year[$i],
        'cjt_month' => $cjt_month[$i],
        'cjt_type' => $cjtType[$i],
        'cjt_qty' => $cjtQty[$i],
        'cjt_transaction_status' => '0',
        'aud_user' => $arrContractJob['user_name'],
        'aud_date' => $arrContractJob['current_date'],
        'aud_prog' => 'CMSY',
        'aud_machine' => $arrContractJob['ip'],
        'cjt_total' => $total[$i],
      ]);
    }
  }
}
