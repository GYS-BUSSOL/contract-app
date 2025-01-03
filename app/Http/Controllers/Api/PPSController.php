<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\{DB, Log, Storage};
use App\Models\{Contract, TrnBUCCWC, MerShift, TimeHistory, TrnShift};

class PPSController extends Controller
{
  protected $contract;

  public function __construct(Contract $contract)
  {
    $this->contract = $contract;
  }

  public function searchOngoing(Request $request)
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
      $countPriorityData = $countBuilderAll->where('con_priority_id', '1')->count();
      $countNotPriorityData = $countBuilderAll->where('con_priority_id', '2')->count();

      return response()->json([
        'status' => 200,
        'message' => 'Successfully retrieved pps on going data',
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
        'message' => 'Failed to retrieve pps on going data',
        'data' => [
          'rows' => [],
          'total_record' => 0
        ],
      ], 500);
    }
  }

  public function add(Request $request)
  {
    $data = $this->param();
    $validated = $this->handleValidationException($request, $data);
    if ($validated instanceof JsonResponse) {
      return $validated;
    }

    try {
      DB::beginTransaction();
      $ip = $request->server('REMOTE_ADDR');
      $duration = $request->input('duration');
      $duration_start = null;
      $duration_end = null;
      $currentDate = Carbon::now();
      $userName = Auth::user()->usr_display_name;

      $generateRequestId = $this->generateRequestId();
      $newRequestId = $generateRequestId['newRequestId'];
      $newOrderNo = $generateRequestId['newOrderNo'];

      $uploadedAttachment = null;
      if ($request->hasFile('file_attachment')) {
        $request->validate([
          'file_attachment' => ['file', 'extensions:doc,pdf,gif,jpeg,png', 'max:2048']
        ]);
        $uploadedAttachment = $this->fileAttachment($request->file('file_attachment'), $newRequestId);
      }
      if ($duration) {
        $duration = trim($duration, '"');
        list($duration_start, $duration_end) = explode(' to ', $duration);
        $duration_start = Carbon::createFromFormat('Y-m-d', $duration_start)->format('Y-m-d');
        $duration_end = Carbon::createFromFormat('Y-m-d', $duration_end)->format('Y-m-d');
      }
      $arrContract = [
        'validated' => $validated,
        'duration_start' => $duration_start,
        'duration_end' => $duration_end,
        'request_id' => $newRequestId,
        'order_no' => $newOrderNo,
        'path_file_attachment' => $uploadedAttachment,
        'ip' => $ip,
        'current_date' => $currentDate,
        'user_name' => $userName
      ];

      $contractData = $this->addContract($arrContract);
      if (is_string($validated['shift_checklist']) && !is_array(json_decode(trim($validated['shift_checklist'], "'"), true))) {
        $shiftChecklistArray = explode(',', trim($validated['shift_checklist'], '"'));
      } else {
        $shiftChecklistArray = json_decode(trim($validated['shift_checklist'], "'"), true);
      }

      if (isset($shiftChecklistArray)) {
        $this->addShift($shiftChecklistArray, $arrContract);
      }

      if (isset($validated['cc']) && count(explode(',', $validated['cc'])) > 0) {
        $this->addCC($validated['cc'], $arrContract);
      }

      if (isset($validated['wc']) && count(explode(',', $validated['wc'])) > 0) {
        $this->addWC($validated['wc'], $arrContract);
      }

      $wlChecklist = 0;
      if ($validated['ck_wl']) {
        $wlData = $validated['wc'];
        $wlChecklist = 1;
      } else {
        $wlData = $validated['work_location'];
      }

      $arrWLData = [
        'isChecklist' => $wlChecklist,
        'wlData' => $wlData
      ];
      $this->updateContract($arrWLData, $newRequestId);
      $this->addTimeHistory($arrContract);

      if ($contractData) {
        DB::commit();
        return response()->json([
          'status' => 200,
          'message' => 'PPS created successfully',
          'data' => [
            'rows' => $contractData,
          ]
        ], 200);
      }
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'status' => 500,
        'error' => 'Server error',
        'message' => 'PPS failed to create' . $e->getMessage(),
      ], 500);
    }
  }

  public function edit(int $id)
  {
    try {
      $currentContract = $this->contract->firstWhere('con_id', $id);
      if (empty($currentContract)) {
        return response()->json([
          "status" => 404,
          "message" => "Contract not found",
        ], 404);
      }
      $shift = [];
      $cc = [];
      $wc = [];

      if ($currentContract) {
        $PPSOngoingArray = $currentContract->toArray();
        $shift = TrnShift::where('sh_con_req_id', $PPSOngoingArray['con_req_id'])->get()->toArray();
        $cc = TrnBUCCWC::where([
          ['con_req_id', $PPSOngoingArray['con_req_id']],
          ['tbc_kategori', 'cc']
        ])
          ->get()
          ->toArray();

        $wc = TrnBUCCWC::where([
          ['con_req_id', $PPSOngoingArray['con_req_id']],
          ['tbc_kategori', 'wc']
        ])
          ->get()
          ->toArray();

        $arrShift['arr_shift'] = $shift;
        $arrCC['arr_cc'] = $cc;
        $arrWC['arr_wc'] = $wc;

        $mergedArray = array_merge($PPSOngoingArray, $arrShift, $arrCC, $arrWC);
      } else {
        $mergedArray = [];
      }

      return response()->json([
        "status" => 200,
        "message" => "Successfully retrieved contract data",
        "data" => $mergedArray
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        "status" => 500,
        "message" => "Failed to retrieve contract data",
        "error" => "Server error",
      ], 500);
    }
  }

  public function update(Request $request, int $id)
  {
    $data = $this->param();
    $validated = $this->handleValidationException($request, $data);
    if ($validated instanceof JsonResponse) {
      return $validated;
    }

    try {
      DB::beginTransaction();
      $ip = $request->server('REMOTE_ADDR');
      $duration = $request->input('duration');
      $duration_start = null;
      $duration_end = null;
      $currentDate = Carbon::now();
      $userName = Auth::user()->usr_display_name;

      $dataCurrent = $this->contract->firstWhere('con_id', $id);
      $newRequestId = $dataCurrent['con_req_id'];
      $newOrderNo = $dataCurrent['con_req_no'];

      $uploadedAttachment = null;
      if ($request->hasFile('file_attachment')) {
        if ($dataCurrent['con_file_attachment'] != null && $dataCurrent['con_file_attachment'] != '') {
          if (Storage::exists($dataCurrent['con_file_attachment']))
            Storage::delete($dataCurrent['con_file_attachment']);
        }
        $request->validate([
          'file_attachment' => ['file', 'extensions:doc,pdf,gif,jpeg,png', 'max:2048']
        ]);
        $uploadedAttachment = $this->fileAttachment($request->file('file_attachment'), $newRequestId);
      } else {
        $uploadedAttachment = $dataCurrent['con_file_attachment'];
      }
      if ($duration) {
        $duration = trim($duration, '"');
        list($duration_start, $duration_end) = explode(' to ', $duration);
        $duration_start = Carbon::createFromFormat('Y-m-d', $duration_start)->format('Y-m-d');
        $duration_end = Carbon::createFromFormat('Y-m-d', $duration_end)->format('Y-m-d');
      }
      $arrContract = [
        'validated' => $validated,
        'duration_start' => $duration_start,
        'duration_end' => $duration_end,
        'request_id' => $newRequestId,
        'order_no' => $newOrderNo,
        'path_file_attachment' => $uploadedAttachment,
        'ip' => $ip,
        'current_date' => $currentDate,
        'user_name' => $userName
      ];

      $contractData = $this->updateContractRequest($arrContract);
      $shiftChecklistArray = json_decode($validated['shift_checklist'], true);

      if (isset($shiftChecklistArray)) {
        $this->updateShift($shiftChecklistArray, $arrContract);
      }

      if (isset($validated['cc']) && count(explode(',', $validated['cc'])) > 0) {
        $this->updateCC($validated['cc'], $arrContract);
      }

      if (isset($validated['wc']) && count(explode(',', $validated['wc'])) > 0) {
        $this->updateWC($validated['wc'], $arrContract);
      }

      $wlChecklist = 0;
      if ($validated['ck_wl']) {
        $wlData = $validated['wc'];
        $wlChecklist = 1;
      } else {
        $wlData = $validated['work_location'];
      }

      $arrWLData = [
        'isChecklist' => $wlChecklist,
        'wlData' => $wlData
      ];
      $this->updateContractRequestWL($arrWLData, $newRequestId);
      $this->addTimeHistory($arrContract);

      if ($contractData) {
        DB::commit();
        return response()->json([
          "status" => 200,
          "message" => "Successfully updated pps on going data",
        ], 200);
      }
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        "status" => 500,
        "message" => "Failed to updated pps on going data",
        "error" => "Server error",
      ], 500);
    }
  }

  private function fileAttachment($file, $newRequestId)
  {
    $filename = now();
    $filename = $newRequestId . '.' . $file->getClientOriginalExtension();
    $filePath = public_path('storage/attachment_pps/' . $filename);
    $file->move(public_path('storage/attachment_pps'), $filename);
    return 'attachment_pps/' . $filename;
  }

  private function addContract($data)
  {
    $newRequestId = $data['request_id'];
    $newOrderNo = $data['order_no'];
    $validated = $data['validated'];
    $duration_start = $data['duration_start'];
    $duration_end = $data['duration_end'];
    $uploadedAttachment = $data['path_file_attachment'];
    $date = Carbon::now()->format('Y-m-d');
    $currentDate = $data['current_date'];
    $ip = $data['ip'];
    $userName = $data['user_name'];

    $arrContractData = [
      'con_req_id' => $newRequestId,
      'con_req_no' => $newOrderNo,
      'con_company' => $validated['company'],
      'con_id_project' => $validated['id_project'] != '' &&  $validated['id_project'] != 'null' ? $validated['id_project'] : null,
      'con_pps_no' => $validated['pps_no'],
      'con_old_pps_no' => $validated['old_pps_no'] != '' &&  $validated['old_pps_no'] != 'null' ? $validated['old_pps_no'] : null,
      'con_priority_id' => $validated['priority'] == 'segera' ? 1 : 2,
      'con_cp_name' => $validated['cp_name'],
      'con_cp_dept' => $validated['cp_dept'],
      'con_cp_exthp' => $validated['cp_ext'],
      'con_cp_email' => $validated['cp_email'],
      'con_comment_bu' => $validated['comment'],
      'con_duration_start' => $duration_start,
      'con_duration_end' => $duration_end,
      'ven_id' => $validated['suggest_vendor'] != '' &&  $validated['suggest_vendor'] != 'null' ? $validated['suggest_vendor'] : null,
      'con_file_attachment' => $uploadedAttachment,
      'con_bu' => $validated['bu'],
      'sts_id' => 1,
      'aud_user' => $userName,
      'aud_date' => $currentDate,
      'aud_prog' => 'CMSY',
      'aud_machine' => $ip,
      'con_req_date' => $date,
      'con_comment_jobtarget' => $validated['con_comment_jobtarget'],
    ];
    $result = $this->contract->create($arrContractData);
    return $result;
  }

  private function updateContractRequest($data)
  {
    $newRequestId = $data['request_id'];
    $newOrderNo = $data['order_no'];
    $validated = $data['validated'];
    $duration_start = $data['duration_start'];
    $duration_end = $data['duration_end'];
    $uploadedAttachment = $data['path_file_attachment'];
    $date = Carbon::now()->format('Y-m-d');
    $currentDate = $data['current_date'];
    $ip = $data['ip'];
    $userName = $data['user_name'];

    $arrContractData = [
      'con_company' => $validated['company'],
      'con_id_project' => $validated['id_project'] != '' &&  $validated['id_project'] != 'null' ? $validated['id_project'] : null,
      'con_pps_no' => $validated['pps_no'],
      'con_old_pps_no' => $validated['old_pps_no'] != '' &&  $validated['old_pps_no'] != 'null' ? $validated['old_pps_no'] : null,
      'con_priority_id' => $validated['priority'] == 'segera' ? 1 : 2,
      'con_cp_name' => $validated['cp_name'],
      'con_cp_dept' => $validated['cp_dept'],
      'con_cp_exthp' => $validated['cp_ext'],
      'con_cp_email' => $validated['cp_email'],
      'con_comment_bu' => $validated['comment'],
      'con_duration_start' => $duration_start,
      'con_duration_end' => $duration_end,
      'ven_id' => $validated['suggest_vendor'] != '' &&  $validated['suggest_vendor'] != 'null' ? $validated['suggest_vendor'] : null,
      'con_file_attachment' => $uploadedAttachment,
      'con_bu' => $validated['bu'],
      'sts_id' => 1,
      'aud_user' => $userName,
      'aud_date' => $currentDate,
      'aud_prog' => 'CMSY',
      'aud_machine' => $ip,
      'con_req_date' => $date,
      'con_comment_jobtarget' => $validated['con_comment_jobtarget'],
    ];
    $result = $this->contract
      ->where([
        ['con_req_id', $newRequestId],
        ['con_req_no', $newOrderNo]
      ])
      ->first();
    $result->update($arrContractData);
    return $result;
  }

  private function updateContract(array $arrWLData, string $newRequestId)
  {
    $data = $this->contract->firstWhere('con_req_id', $newRequestId);
    $result = $data->update([
      'con_wc_checklist' => $arrWLData['isChecklist'],
      'con_work_location' => $arrWLData['wlData']
    ]);
    return $result;
  }

  private function updateContractRequestWL(array $arrWLData, string $newRequestId)
  {
    $data = $this->contract->firstWhere('con_req_id', $newRequestId);
    $result = $data->update([
      'con_wc_checklist' => $arrWLData['isChecklist'],
      'con_work_location' => $arrWLData['wlData']
    ]);
    return $result;
  }

  private function addTimeHistory($arrContract)
  {
    $arrDataTH = [
      'con_id' => $arrContract['request_id'],
      'sts_id' => 1,
      'ths_is_approval' => 0,
      'ths_comment' => 'New Request',
      'ths_transaction_status' => 1,
      'aud_user' => $arrContract['user_name'],
      'aud_date' => $arrContract['current_date'],
      'aud_prog' => 'CMSY',
      'aud_machine' => $arrContract['ip']
    ];

    $result = TimeHistory::create($arrDataTH);
  }

  private function addShift(array $shiftChecklistArray, array $arrContract)
  {
    foreach ($shiftChecklistArray as $shift) {
      $merShift =  MerShift::firstWhere('shift_code', $shift);
      $arrShiftData = [
        'sh_con_req_id' => $arrContract['request_id'],
        'sh_shift' => $shift,
        'sh_jam' => $merShift['shift_jam'],
        'aud_user' => $arrContract['user_name'],
        'aud_date' => $arrContract['current_date'],
        'aud_prog' => 'CMSY',
        'aud_machine' => $arrContract['ip']
      ];
      TrnShift::create($arrShiftData);
    }
  }

  private function updateShift(array $shiftChecklistArray, array $arrContract)
  {
    foreach ($shiftChecklistArray as $shift) {
      $merShift =  MerShift::firstWhere('shift_code', $shift);
      $arrShiftData = [
        'sh_shift' => $shift,
        'sh_jam' => $merShift['shift_jam'],
        'aud_user' => $arrContract['user_name'],
        'aud_date' => $arrContract['current_date'],
        'aud_prog' => 'CMSY',
        'aud_machine' => $arrContract['ip']
      ];
      $result = TrnShift::firstWhere('sh_con_req_id', $arrContract['request_id']);
      $result->update($arrShiftData);
    }
  }

  private function addCC(string $arrCC, array $arrContract)
  {
    $arrCC = explode(',', $arrCC);
    foreach ($arrCC as $cc) {
      $arrCCData = [
        'con_req_id' => $arrContract['request_id'],
        'tbc_code' => $cc,
        'tbc_kategori' => 'cc',
        'aud_user' => $arrContract['user_name'],
        'aud_date' => $arrContract['current_date'],
        'aud_machine' => $arrContract['ip']
      ];
      $result = TrnBUCCWC::create($arrCCData);
    }
  }

  private function updateCC(string $arrCC, array $arrContract)
  {
    $arrCC = explode(',', $arrCC);
    foreach ($arrCC as $cc) {
      $arrCCData = [
        'tbc_code' => $cc,
        'aud_user' => $arrContract['user_name'],
        'aud_date' => $arrContract['current_date'],
        'aud_machine' => $arrContract['ip']
      ];
      $result = TrnBUCCWC::firstWhere([
        ['con_req_id', $arrContract['request_id']],
        ['tbc_kategori', 'cc']
      ]);
      $result->update($arrCCData);
    }
  }

  private function addWC(string $arrWC, array $arrContract)
  {
    $arrWC = explode(',', $arrWC);
    foreach ($arrWC as $cc) {
      $arrWCData = [
        'con_req_id' => $arrContract['request_id'],
        'tbc_code' => $cc,
        'tbc_kategori' => 'wc',
        'aud_user' => $arrContract['user_name'],
        'aud_date' => $arrContract['current_date'],
        'aud_machine' => $arrContract['ip']
      ];
      $result = TrnBUCCWC::create($arrWCData);
    }
  }

  private function updateWC(string $arrWC, array $arrContract)
  {
    $arrWC = explode(',', $arrWC);
    foreach ($arrWC as $cc) {
      $arrWCData = [
        'tbc_code' => $cc,
        'aud_user' => $arrContract['user_name'],
        'aud_date' => $arrContract['current_date'],
        'aud_machine' => $arrContract['ip']
      ];
      $result = TrnBUCCWC::firstWhere([
        ['con_req_id', $arrContract['request_id']],
        ['tbc_kategori', 'wc']
      ]);
      $result->update($arrWCData);
    }
  }

  private function generateRequestId()
  {
    $tanggal = Carbon::now()->format('ymd');
    $lastContract = Contract::query()
      ->where('con_req_id', 'like', $tanggal . '%')
      ->orderBy('con_req_id', 'desc')->first();
    $new_angka = 1;
    if ($lastContract) {
      $last_angka = substr($lastContract->con_req_id, 6, 4);
      if ($last_angka < 9999) {
        $new_angka = $last_angka + 1;
      }
    }

    $new_angka = str_pad($new_angka, 4, '0', STR_PAD_LEFT);
    $newOrderNo = 'CM' . $tanggal . '-' . $new_angka;
    $newRequestId = $tanggal . $new_angka;
    $result = [
      'newOrderNo' => $newOrderNo,
      'newRequestId' => $newRequestId
    ];
    return $result;
  }

  private function param()
  {
    $data = [
      'company' => ['required', 'string', 'exists:mer_company,company_code'],
      'bu' => ['required', 'string', 'exists:mer_bu_cc_wc,number'],
      'cc' => ['required', 'string'],
      'wc' => ['nullable', 'string'],
      'work_location' => ['nullable', 'string'],
      'ck_wl' => ['nullable'],
      'id_project' => ['nullable', 'string'],
      'pps_no' => ['required', 'string'],
      'old_pps_no' => ['nullable', 'string'],
      'priority' => ['required', 'in:segera,tidak segera'],
      'shift_checklist' => ['required'],
      'cp_name' => ['required', 'string'],
      'cp_dept' => ['required', 'string'],
      'cp_ext' => ['required', 'numeric'],
      'cp_email' => ['required', 'email'],
      'comment' => ['nullable', 'string'],
      'duration' => ['nullable', 'string'],
      'suggest_vendor' => ['nullable', 'string'],
      'file_attachment' => ['nullable'],
      'con_comment_jobtarget' => ['nullable', 'string'],
    ];

    return $data;
  }

  public function searchCompleted(Request $request)
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
      $countPriorityData = $countBuilderAll->where('con_priority_id', '1')->count();
      $countNotPriorityData = $countBuilderAll->where('con_priority_id', '2')->count();

      return response()->json([
        'status' => 200,
        'message' => 'Successfully retrieved PPS completed data',
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
        'message' => 'Failed to retrieve PPS completed data' . $e->getMessage(),
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
          if ($value && $value != "" && $value != " "  && $where['name'] != 'con_is_expired' &&  $where['name'] != 'con_priority_id' && $where['name'] != 'sts_description') {
            $column = $where['name'];
            $operator = strtolower($where['logic_operator']);
            if ($operator == "notin") {
              $query->whereNotIn($tableColumn . "." . $column, $value);
            } else if ($operator == "in") {
              $query->whereIn($tableColumn . "." . $column, $value);
            } else if ($operator == "isnull") {
              $query = $query->WhereNull($tableColumn . "." . $column);
            }
          }
        }
      }
    })->distinct()->get();

    return $data;
  }
}
