<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Support\Facades\{Auth, DB};
use App\Http\Controllers\Controller;
use App\Models\{Company, SPK, Contract, ContractJob, ManDaysRate, MerVendor, Range, SPKContract, TimeHistory, TrnBUCCWC, TrnShift};
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
    return response()->json([
      'status' => 200,
      'message' => 'SPK created successfully',
    ], 200);

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
      $userName = Auth::user()->usr_display_name;
      $ipAddress = $request->server('REMOTE_ADDR');

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
        'aud_machine' => $ipAddress,
        'st_id' => $validated['signature_type'],
      ]);

      $data = [
        'validated' => $validated,
        'aud_machine' => $ipAddress,
        'aud_date' => $currentDate,
        'aud_user' => $userName,
        'spk_id' => $createSPK['spk_id']
      ];

      $createSPKContract = $this->addSPKContract($data);

      if ($createSPK && $createSPKContract) {
        DB::commit();
        return response()->json([
          'status' => 200,
          'message' => 'SPK created successfully',
          'data' => $createSPK
        ], 200);
      }
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'status' => 500,
        'error' => 'Server error',
        'message' => 'SPK failed to create',
      ], 500);
    }
  }

  public function save(Request $request)
  {
    $data = [
      'contract' => ['required', "array"],
      'spk_id' => ['required', 'exists:trn_spk,spk_id'],
      'spk_date' => ['required'],
      'spk_no' => ['required', 'string'],
      'spk_box_bpjs' => ['required', 'string'],
      'spk_lain_lain' => ['required', 'string'],
      'spk_cara_bayar' => ['required', 'string'],
      'spk_tahap_bayar' => ['required', 'string'],
      'spk_renewal_box' => ['required', 'string'],
      'shift' => ['required']
    ];

    $validated = $this->handleValidationException($request, $data);
    if ($validated instanceof JsonResponse) {
      return $validated;
    }

    try {
      DB::beginTransaction();

      $currentDate = Carbon::now();
      $userName = Auth::user()->usr_display_name;
      $ipAddress = $request->server('REMOTE_ADDR');
      $arrData = [
        'validated' => $validated,
        'userName' => $userName,
        'ipAddress' => $ipAddress,
        'currentDate' => $currentDate
      ];

      $updatedSPK = $this->spk->where('spk_id', $validated['spk_id'])
        ->update(['spk_transaction_status' => '0']);

      $globalSave = $this->globalSaveAndPrint($arrData);

      if ($updatedSPK && $globalSave) {
        DB::commit();
        return response()->json([
          'status' => 200,
          'message' => 'SPK created successfully',
        ], 200);
      }
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'status' => 500,
        'error' => 'Server error',
        'message' => 'SPK failed to create',
      ], 500);
    }
  }

  public function savePrint(Request $request)
  {
    $data = [
      'contract' => ['required', "array"],
      'spk_id' => ['required', 'exists:trn_spk,spk_id'],
      'spk_date' => ['required'],
      'spk_no' => ['required', 'string'],
      'spk_box_bpjs' => ['required', 'string'],
      'spk_lain_lain' => ['required', 'string'],
      'spk_cara_bayar' => ['required', 'string'],
      'spk_tahap_bayar' => ['required', 'string'],
      'spk_renewal_box' => ['required', 'string'],
      'shift' => ['required']
    ];

    $validated = $this->handleValidationException($request, $data);
    if ($validated instanceof JsonResponse) {
      return $validated;
    }

    try {
      DB::beginTransaction();

      $currentDate = Carbon::now();
      $userName = Auth::user()->usr_display_name;
      $ipAddress = $request->server('REMOTE_ADDR');
      $arrData = [
        'validated' => $validated,
        'userName' => $userName,
        'ipAddress' => $ipAddress,
        'currentDate' => $currentDate
      ];
      $SPKId = $validated['spk_id'];

      $updatedContracts = $this->contract->whereIn('con_req_id', function ($query) use ($SPKId) {
        $query->select('con_req_id')
          ->from('trn_spk_contract')
          ->where('spk_id', $SPKId);
      })
        ->update(['sts_id' => '8']);

      if ($updatedContracts) {
        $updatedSPK = $this->spk->where('spk_id', $SPKId)
          ->update(['spk_transaction_status' => '1']);
      }

      $globalSave = $this->globalSaveAndPrint($arrData);

      if ($updatedContracts && $updatedSPK && $globalSave) {
        DB::commit();
        return response()->json([
          'status' => 200,
          'message' => 'SPK created successfully',
        ], 200);
      }
    } catch (\Exception $e) {
      DB::rollBack();
      return response()->json([
        'status' => 500,
        'error' => 'Server error',
        'message' => 'SPK failed to create',
      ], 500);
    }
  }

  private function globalSaveAndPrint(array $arrData)
  {
    $allDataContract = $arrData['validated']['contract'];
    $SPKId = $arrData['validated']['spk_id'];
    $currentDate = $arrData['currentDate'];
    $userName = $arrData['userName'];
    $ipAddress = $arrData['ipAddress'];
    $shift = $arrData['validated']['shift'];
    $spkNo = $arrData['validated']['spk_no'];
    $spkDate = $arrData['validated']['spk_date'];
    $spkBoxBpjs = $arrData['validated']['spk_box_bpjs'];
    $spkLainLain = $arrData['validated']['spk_lain_lain'];
    $spkCaraBayar = $arrData['validated']['spk_cara_bayar'];
    $spkTahapBayar = $arrData['validated']['spk_tahap_bayar'];
    $spkRenewalBox = $arrData['validated']['spk_renewal_box'];

    try {
      DB::beginTransaction();

      foreach ($allDataContract as $c) {
        foreach ($c['contractJob'] as $cj) {
          if ($cj['data']['cjb_pay_template'] == 'range') {
            foreach ($cj['range'] as $r) {
              $rangeId = $r['id'];
              $rangeCjbid = $r['cjb_id'];
              $minProduksi = $r['min_produksi'];
              $uom = $r['uom'];
              $maxProduksi = $r['max_produksi'];
              $maxBatas = $r['max_batas'];
              $harga = $r['harga'];

              $existingRange = Range::find($rangeId);

              if ($existingRange) {
                $existingRange->update([
                  'uom' => $uom,
                  'harga' => $harga,
                  'max_batas' => $maxBatas,
                  'min_produksi' => $minProduksi,
                  'max_produksi' => $maxProduksi,
                ]);
              } else {
                Range::create([
                  'spk_id' => $SPKId,
                  'cjb_id' => $rangeCjbid,
                  'min_produksi' => $minProduksi,
                  'uom' => $uom,
                  'max_produksi' => $maxProduksi,
                  'max_batas' => $maxBatas,
                  'harga' => $harga,
                ]);
              }
            }
          }
        }
      }

      $SPKContracts = SPKContract::where('spk_id', $SPKId)
        ->get();

      $arrShift = [];
      if (is_string($shift) && !empty($shift)) {
        $arrShift = array_map('trim', explode(',', $shift));
      }

      foreach ($SPKContracts as $spkCon) {
        $contractId = $spkCon['con_req_id'];

        $createdTimeHistory = TimeHistory::create([
          'con_id'      => $contractId,
          'sts_id'      => '8',
          'aud_user'    => $userName,
          'aud_date'    => $currentDate,
          'aud_prog'    => 'CMSY',
          'aud_machine' => $ipAddress
        ]);

        if ($createdTimeHistory) {

          $existingShifts = TrnShift::where('sh_con_req_id', $contractId)
            ->pluck('sh_jam')
            ->toArray();

          foreach ($arrShift as $shift) {
            if (in_array($shift, $existingShifts)) {
              TrnShift::where('sh_con_req_id', $contractId)
                ->where('sh_jam', $shift)
                ->update([
                  'sh_jam' => $shift,
                  'aud_user' => $userName,
                  'aud_date' => $currentDate,
                  'aud_prog' => 'CMSY',
                  'aud_machine' => $ipAddress
                ]);
            } else {
              TrnShift::create([
                'sh_con_req_id' => $contractId,
                'sh_jam' => $shift,
                'aud_user' => $userName,
                'aud_date' => $currentDate,
                'aud_prog' => 'CMSY',
                'aud_machine' => $ipAddress
              ]);
            }
          }

          TrnShift::where('sh_con_req_id', $contractId)
            ->whereNotIn('sh_jam', $arrShift)
            ->delete();

          $spkUpdated = $this->spk->where('spk_id', $SPKId)
            ->update([
              'spk_web_id' => '',
              'spk_no' => $spkNo,
              'spk_box_bpjs' => $spkBoxBpjs,
              'spk_lain_lain' => $spkLainLain,
              'spk_cara_bayar' => $spkCaraBayar,
              'spk_tahap_bayar' => $spkTahapBayar,
              'spk_renewal_box' => $spkRenewalBox,
              'spk_date' => $spkDate,
              'aud_user' => $userName,
              'aud_date' => $currentDate,
              'aud_prog' => 'CMSY',
              'aud_machine' => $ipAddress
            ]);
        }
      }

      if ($spkUpdated && $createdTimeHistory) {
        DB::commit();
        return true;
      }
    } catch (\Exception $e) {
      DB::rollBack();
      return false;
    }
  }

  public function edit(int $id)
  {
    try {
      $SPK = $this->spk
        ->from('trn_spk AS ts')
        ->leftJoin('mer_sign_type AS mst', 'ts.st_id', 'mst.st_id')
        ->join('mer_vendor AS mv', 'ts.ven_id', 'mv.vnd_id')
        ->where('ts.spk_id', $id)
        ->first()
        ->toArray();

      $spkContract = SPKContract::from('trn_spk_contract AS tsc')
        ->join('trn_time_history AS tth', 'tsc.con_req_id', '=', 'tth.con_id')
        ->join('mer_user_login AS mul', 'tth.aud_user', '=', 'mul.usr_display_name')
        ->where([
          ['tth.sts_id', 6],
          ['tsc.spk_id', $id],
          ['is_active', 0]
        ])
        ->select('mul.usr_display_name', 'mul.usr_jabatan')
        ->first();

      if ($spkContract) {
        $spkContractArray = $spkContract->toArray();
      } else {
        $spkContractArray = [];
      }

      $company = Company::firstWhere('company_is_company', 1)->select('company_name', 'company_alamat')
        ->first()
        ->toArray();

      $contract = Contract::from('trn_contract AS tc')
        ->rightJoin('trn_spk_contract AS tsc', 'tc.con_req_id', 'tsc.con_req_id')
        ->leftJoin('mer_bu_cc_wc AS mbcw', 'tc.con_bu', 'mbcw.number')
        ->where('tsc.spk_id', $id)
        ->select('mbcw.description', 'tc.*')
        ->distinct()
        ->get();

      $arrContract = [];
      foreach ($contract as $c) {
        $shift = TrnShift::selectRaw("
          STUFF(
            (
              SELECT DISTINCT ', ' + sh_jam 
              FROM trn_shift 
              WHERE sh_id = trn_shift.sh_id 
                AND sh_con_req_id = ?
              FOR XML PATH('')
            ), 1, 1, ''
          ) AS sh_jam
        ", [$c['con_req_id']])->first();

        $rowCountShift = TrnShift::from('trn_shift AS ts')
          ->selectRaw('count(*) AS jml_shift, ts.sh_con_req_id')
          ->where('ts.sh_con_req_id', $c['con_req_id'])
          ->whereRaw('LEN(sh_jam) > 1')
          ->groupBy('sh_con_req_id')
          ->first();

        $cc = TrnBUCCWC::from('trn_bu_cc_wc AS tbcw')
          ->join('mer_bu_cc_wc AS mbcw', 'tbcw.tbc_code', 'mbcw.number')
          ->where([
            ['con_req_id', $c['con_req_id']],
            ['tbc_kategori', 'cc']
          ])->get();

        $wc = TrnBUCCWC::from('trn_bu_cc_wc AS tbcw')
          ->join('mer_bu_cc_wc AS mbcw', 'tbcw.tbc_code', 'mbcw.number')
          ->where([
            ['con_req_id', $c['con_req_id']],
            ['tbc_kategori', 'wc']
          ])->get();

        $qryContractJob = ContractJob::from('trn_contract_job AS tcj')
          ->select([
            'tcj.cjb_id',
            'tpc.cjb_desc',
            'tcj.cjb_rate',
            'tpc.cjb_pay_type',
            'tpc.cjb_pay_template'
          ])
          ->leftJoin('trn_contract AS tc', 'tcj.con_id', 'tc.con_req_id')
          ->leftJoin('trn_spk_contract AS tpc', 'tcj.cjb_id', 'tpc.cjb_id')
          ->where('tcj.con_id', $c['con_req_id'])
          ->where(function ($query) {
            $query->whereNull('tcj.aud_delete')
              ->orWhere('tcj.aud_delete', '0');
          })
          ->distinct()
          ->get();

        $userSignature = SPKContract::from('trn_spk_contract AS tsc')
          ->join('trn_time_history as tth', 'tsc.con_req_id', 'tth.con_id')
          ->join('mer_user_login as mul', function ($join) {
            $join->on('tth.aud_user', 'mul.usr_display_name')
              ->where('mul.usr_access', 'approval');
          })
          ->select('mul.usr_display_name', 'mul.usr_jabatan')
          ->where('tsc.spk_id', $id)
          ->whereIn('tth.sts_id', [6, 4])
          ->groupBy('mul.usr_display_name', 'mul.usr_jabatan', 'tth.sts_id')
          ->orderBy('tth.sts_id', 'asc')
          ->get();

        $arrContractJob = [];
        foreach ($qryContractJob as $qcj) {
          $qryRate = ManDaysRate::from('mer_rate_tk AS mrt')
            ->join('trn_contract_job_labor AS tcjl', 'mrt.rtk_id_jenis_tk', 'tcjl.cjl_type')
            ->select('mrt.*', 'tcjl.*')
            ->where([
              ['tcjl.cjb_id', $qcj['cjb_id']],
              ['mrt.rtk_active_status', 'Active']
            ])
            ->get();

          $range = Range::where([
            ['spk_id', $id],
            ['cjb_id', $qcj['cjb_id']]
          ])->get();

          $arrContractJob[] = [
            'data' => $qcj,
            'rate' => $qryRate,
            'range' => $range
          ];
        }

        $arrContract['contract'][] = [
          'data' => $c,
          'cc' => $cc,
          'wc' => $wc,
          'contractJob' => $arrContractJob,
        ];
        $arrContract['shift'] = $shift['sh_jam'];
        $arrContract['countShift'] = $rowCountShift;
        $arrContract['userSignature'] = $userSignature;
      }

      $merged = array_merge($SPK, $spkContractArray, $company, $arrContract);

      return response()->json([
        "status" => 200,
        "message" => "Successfully retrieved SPK data",
        "data" => $merged
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        "status" => 500,
        "message" => "Failed to retrieve SPK data",
        "error" => "Server error",
      ], 500);
    }
  }

  private function addSPKContract(array $arrData)
  {
    $validated = $arrData['validated'];
    for ($i = 0; $i < count($validated['cjb_id']); $i++) {
      $spkContract = [
        'spk_id' => $arrData['spk_id'],
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

  public function latestSPKNumber()
  {
    $dateSPKNo = date("m/Y");
    $rowSPKNo = $this->spk->selectRaw('MAX(LEFT(spk_no, 3)) as jmlspk')
      ->value('jmlspk');
    $lastAngka = $rowSPKNo ? (int) $rowSPKNo : 0;
    $newAngka = $lastAngka < 9999 ? $lastAngka + 1 : 1;
    $newAngkaFormatted = sprintf("%03s", $newAngka) . '/PBL/SPK/NBP/' . $dateSPKNo;

    return response()->json([
      'status' => 200,
      'message' => 'Successfully retrieved SPK number',
      'data' => $newAngkaFormatted
    ], 200);
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
        'message' => 'Failed to retrieve SPK report data',
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
        'message' => 'Failed to retrieve SPK active data',
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
