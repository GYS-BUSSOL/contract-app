<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Http\Requests\PPS\CreateRequest;
use Illuminate\Http\{Request, JsonResponse};
use App\Http\Controllers\Api\TrnJobTypeController;
use Illuminate\Support\Facades\{DB, Log, Storage};
use App\Models\{Contract, TrnBUCCWC, MerShift, TimeHistory, TrnShift};

class PPSController extends Controller
{
    protected $contract;
    protected $jobType;

    public function __construct(Contract $contract, TrnJobTypeController $jobType)
    {
        $this->contract = $contract;
        $this->jobType = $jobType;
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
        Log::info(['requestAll' => $request->all()]);

        $data = [
            'company' => ['required', 'string', 'exists:mer_company,company_code'],
            'bu' => ['required', 'string', 'exists:mer_bu_cc_wc,number'],
            'cc' => ['required', 'string'],
            'wc' => ['nullable', 'string'],
            'work_location' => ['nullable', 'string'],
            'ck_wl' => ['nullable'],
            'id_project' => ['nullable', 'string'],
            'pps_no' => ['required', 'string', 'unique:trn_contract', 'con_pps_no'],
            'old_pps_no' => ['nullable', 'string'],
            'priority' => ['required', 'in:segera,tidak segera'],
            'shift_checklist' => ['required', Rule::in(['Non Shift', 'Shift 1', 'Shift 2', 'Shift 3'])],
            'cp_name' => ['required', 'string'],
            'cp_dept' => ['required', 'string'],
            'cp_ext' => ['required', 'numeric'],
            'cp_email' => ['required', 'email'],
            'comment' => ['nullable', 'string'],
            'duration' => ['nullable', 'string'],
            'suggest_vendor' => ['nullable', 'string', 'exists:mer_vendor,vnd_id'],
            'file_attachment' => ['nullable', 'file', 'extensions:doc,pdf,gif,jpeg,png', 'max:2048'],
            'con_comment_jobtarget' => ['nullable', 'string'],
        ];

        $validated = $this->handleValidationException($request, $data);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            DB::beginTransaction();
            $ip = $request->server('REMOTE_ADDR');
            $tanggal = Carbon::now()->format('ymd');
            $duration = $request->input('duration');
            $duration_start = null;
            $duration_end = null;
            $currentDate = Carbon::now();
            $userName = 'Wahyu';

            $lastContract = Contract::query()
                ->where('con_req_id', 'like', $tanggal . '%')
                ->orderByDesc('con_req_id')
                ->first();

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

            $uploadedAttachment = null;
            if ($request->hasFile('file_attachment')) {
                $uploadedAttachment = $this->fileAttachment($request->file('file_attachment'), $newRequestId);
            }

            if ($duration) {
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
            $shiftChecklistArray = json_decode($validated['shift_checklist'], true);

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

    public function addJobType(Request $request)
    {
        $data = [
            'job_type' => ['required', 'exists:mer_job_type,job_type_id'],
            'job_desc' => ['required', 'string'],
            'pic' => ['required', 'string'],
            'payment_type' => ['required', 'exists:mer_payment_type,paytype_code'],
            'total_job_target_qty' => ['required', 'numeric'],
            'uom' => ['required', 'exists:mer_measurement_unit,unt_code'],
            'cjt_year' => ['nullable'],
            'cjt_month' => ['nullable'],
            'cjt_type' => ['nullabled', 'in:1,2'],
            'cjt_qty' => ['nullable', 'numeric'],
            'total' => ['nullable', 'numeric'],
            'labor_type' => ['nullable', 'string'],
            'labor_type' => ['nullable', 'string'],
        ];
    }

    private function fileAttachment($request, $newRequestId)
    {
        $filename = now();
        $file = $request->file('file_attachment');
        $filename = $newRequestId . '.' . $file->getClientOriginalExtension();
        $filePath = public_path('storage/attachment_pps/' . $filename);
        $file->move(public_path('storage/attachment_pps'), $filename);

        return $filePath;
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
            'con_id_project' => $validated['id_project'],
            'con_pps_no' => $validated['pps_no'],
            'con_old_pps_no' => $validated['old_pps_no'],
            'con_priority_id' => $validated['priority'] == 'segera' ? 1 : 2,
            'con_cp_name' => $validated['cp_name'],
            'con_cp_dept' => $validated['cp_dept'],
            'con_cp_exthp' => $validated['cp_ext'],
            'con_cp_email' => $validated['cp_email'],
            'con_comment_bu' => $validated['comment'],
            'con_duration_start' => $duration_start,
            'con_duration_end' => $duration_end,
            'ven_id' => $validated['ven_id'],
            'con_file_attachment' => $uploadedAttachment,
            'con_bu' => $validated['bu'],
            'sts_id' => $validated['sts_id'],
            'aud_user' => $userName,
            'aud_date' => $currentDate,
            'aud_prog' => 'CMSY',
            'aud_machine' => $ip,
            'con_req_date' => $date,
            'con_comment_jobtarget' => $validated['con_comment_jobtarget'],
        ];
        Log::info(['arrContractData' => $arrContractData]);
        $result = $this->contract->create($arrContractData);
        return $result;
    }

    private function updateContract(array $arrWLData, string $newRequestId)
    {
        $data = $this->contract->firstWhere('con_req_id', $newRequestId);
        $result = $data->update([
            'con_wc_checklist' => $arrWLData['wlData'],
            'con_work_location' => $arrWLData['isChecklist']
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

        TimeHistory::create($arrDataTH);
    }

    private function addShift(array $shiftChecklistArray, array $arrContract)
    {
        Log::info(['shiftChecklistArray' => $shiftChecklistArray]);
        foreach ($shiftChecklistArray as $shift) {
            if (count($shiftChecklistArray) == 1) {
                $merShift =  MerShift::firstWhere('shift_code', $shift);
                Log::info(['merShift' => $merShift]);
                $arrShiftData = [
                    'sh_con_req_id' => $arrContract['request_id'],
                    'sh_shift' => $shift,
                    'sh_jam' => $merShift['shift_jam'],
                    'aud_user' => $arrContract['user_name'],
                    'aud_date' => $arrContract['current_date'],
                    'aud_prog' => 'CMSY',
                    'aud_machine' => $arrContract['ip']
                ];
            } else {
                $arrShiftData = [
                    'sh_con_req_id' => $arrContract['request_id'],
                    'sh_shift' => $shift,
                    'aud_user' => $arrContract['user_name'],
                    'aud_date' => $arrContract['current_date'],
                    'aud_prog' => 'CMSY',
                    'aud_machine' => $arrContract['ip']
                ];
            }
            Log::info(['arrShiftData' => $arrShiftData]);
            TrnShift::create($arrShiftData);
        }
    }

    private function addCC(string $arrCC, array $arrContract)
    {
        $arrCC = explode(',', $arrCC);
        Log::info(['arrCC' => $arrCC]);
        foreach ($arrCC as $cc) {
            $arrCCData = [
                'con_req_id' => $arrContract['request_id'],
                'tbc_code' => $cc,
                'tbc_kategori' => 'cc',
                'aud_user' => $arrContract['user_name'],
                'aud_date' => $arrContract['current_date'],
                'aud_machine' => $arrContract['ip']
            ];
            Log::info(['arrCCData' => $arrCCData]);
            TrnBUCCWC::create($arrCCData);
        }
    }

    private function addWC(string $arrWC, array $arrContract)
    {
        $arrWC = explode(',', $arrWC);
        Log::info(['arrWC' => $arrWC]);
        foreach ($arrWC as $cc) {
            $arrWCData = [
                'con_req_id' => $arrContract['request_id'],
                'tbc_code' => $cc,
                'tbc_kategori' => 'wc',
                'aud_user' => $arrContract['user_name'],
                'aud_date' => $arrContract['current_date'],
                'aud_machine' => $arrContract['ip']
            ];
            Log::info(['arrWCData' => $arrWCData]);
            TrnBUCCWC::create($arrWCData);
        }
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
