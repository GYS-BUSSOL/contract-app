<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Support\Facades\{DB, Log};
use App\Http\Controllers\Controller;
use App\Models\{SPK, Contract, ContractJob, MerVendor, SPKContract};
use Illuminate\Http\{Request, JsonResponse};

class SPKController extends Controller
{
  protected $contract;
  protected $spk;

  public function __construct(Contract $contract, SPK $spk)
  {
    $this->contract = $contract;
    $this->spk = $spk;
  }

  public function search(Request $request)
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

  public function add(Request $request)
  {
    $data = [
      'signature_type' => ['required', 'exists:mer_sign_type,st_id'],
      'spk_jobdesc_summary' => ['required', 'string'],
      'duration' => ['required', 'string'],
      'suggest_vendor' => ['required', 'string', 'exists:mer_vendor,vnd_id'],
      'con_req_id' => ['required', "array"],
      'cjb_desc' => ['required', "array"],
      'cjb_pay_template' => ['required', "array"],
      'cjb_pay_type' => ['required', "array"],
      'cjb_rate' => ['required', "array"],
      'cjb_id' => ['required', "array"],
      'unt_id' => ['required', "array"],
    ];

    $validated = $this->handleValidationException($request, $data);
    if ($validated instanceof JsonResponse) {
      return $validated;
    }

    try {
      DB::beginTransaction();
      $spkStartDate = null;
      $spkEndDate = null;
      $currentDate = Carbon::now();
      $userName = 'Wahyu';
      $ip = $request->server('REMOTE_ADDR');

      $generateSPKId = $this->generateSPKId();

      $this->contract->whereIn('con_req_id', $validated['con_req_id'])
        ->update([
          'ven_id' => $validated['suggest_vendor'],
          'con_ven_pic' => MerVendor::where('vnd_id', $validated['suggest_vendor'])->value('vnd_name'),
        ]);
      $duration = $validated['duration'];
      if ($duration) {
        $duration = trim($duration, '"');
        list($spkStartDate, $spkEndDate) = explode(' to ', $duration);
        $spkStartDate = Carbon::createFromFormat('Y-m-d', $spkStartDate)->format('Y-m-d');
        $spkEndDate = Carbon::createFromFormat('Y-m-d', $spkEndDate)->format('Y-m-d');
        Log::info(["spkStartDate" => $spkStartDate, 'spkEndDate' => $spkEndDate]);
      }

      $createSPK = $this->spk->create([
        'ven_id' => $validated['suggest_vendor'],
        'spk_ven_pic' => MerVendor::where('vnd_id', $validated['suggest_vendor'])->value('vnd_name'),
        'spk_start_date' => $spkStartDate,
        'spk_end_date' => $spkEndDate,
        'spk_jobdesc_summary' => $validated['spk_jobdesc_summary'],
        'spk_transaction_status' => '0',
        'aud_user' => $userName,
        'aud_date' => $currentDate,
        'aud_prog' => 'CMSY',
        'aud_machine' => $ip,
        'st_id' => $validated['signature_type'],
      ]);

      $data = [
        'validated' => $validated,
        'aud_machine' => $ip,
        'aud_date' => $currentDate,
        'aud_user' => $userName,
        'generated_id' => $generateSPKId
      ];

      $createSPKContract = $this->addSPKContract($data);

      if ($createSPK && $createSPKContract) {
        DB::commit();
        return response()->json([
          'status' => 200,
          'message' => 'SPK created successfully'
        ], 200);
      }
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'status' => 500,
        'error' => 'Server error',
        'message' => 'SPK failed to create' . $e->getMessage(),
      ], 500);
    }
  }

  private function addSPKContract(array $arrData)
  {
    $validated = $arrData['validated'];
    for ($i = 0; $i < count($validated['cjb_id']); $i++) {
      $spkContract = [
        'spk_id' => $arrData['generated_id'],
        'con_req_id' => $validated['con_req_id'][$i],
        'cjb_id' => $validated['cjb_id'][$i],
        'cjb_desc' => $validated['cjb_desc'][$i],
        'cjb_pay_type' => $validated['unt_id'][$i],
        'cjb_pay_template' => $validated['cjb_pay_template'][$i],
        'spk_transaction_status' => '0',
        'aud_user' => $arrData['aud_user'],
        'aud_date' => $arrData['aud_date'],
        'aud_prog' => 'CMSY',
        'aud_machine' => $arrData['aud_machine'],
      ];
      $createSPKContract = SPKContract::create($spkContract);

      $updateContractJob = ContractJob::where('cjb_id', $validated['cjb_id'][$i])
        ->update([
          'cjb_rate' => $validated['cjb_rate'][$i]
        ]);
      $isSuccess = false;
      if ($createSPKContract && $updateContractJob) {
        $isSuccess = true;
      }

      return $isSuccess;
    }
  }

  private function generateSPKId()
  {
    $lastSPK = $this->contract->orderBy('aud_date', 'desc')->first();
    $new_angka = 1;
    if ($lastSPK) {
      $last_angka = $lastSPK['spk_id'];
      if ($last_angka < 9999) {
        $new_angka = $last_angka + 1;
      }
    }

    Log::info(['generateSPKId' => $new_angka]);
    return $new_angka;
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
