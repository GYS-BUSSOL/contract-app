<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\{Budget, ContractJob, TrnShift, ContractJobLabor};

class EstimatedController extends Controller
{
    protected $budget;

    public function __construct(Budget $budget)
    {
        $this->budget = $budget;
    }

    public function cost(Request $request)
    {
        try {
            $data = [
                'con_req_id' => ['required', 'string'],
                'con_bu' => ['required', 'string'],
                'con_duration_start' => ['required', 'string'],
                'con_duration_end' => ['required', 'string'],
                'days' => ['required', 'numeric'],
            ];

            $validated = $this->handleValidationException($request, $data);
            if ($validated instanceof JsonResponse) {
                return $validated;
            }
            $conReq = $validated['con_req_id'];
            $conBU = $validated['con_bu'];
            $conDurationStart = $validated['con_duration_start'];
            $days = $validated['days'];

            $qryTotal = $this->budget
                ->select(DB::raw('SUM(bgt_balance) as tot_budget'))
                ->where([
                    ['bgt_year', date('Y', strtotime($conDurationStart))],
                    ['bgt_bu', $conBU]
                ])
                ->first();

            $totBudget = $qryTotal->tot_budget ?? 0;

            // Get job cost
            $qryJobCost = ContractJob::select(DB::raw('SUM(
                    CASE 
                        WHEN cjb_pay_template = \'p1\' OR cjb_pay_template = \'rit\' 
                        THEN cjb_rate * cjb_qty * 1.08 
                        ELSE cjb_rate * cjb_qty 
                    END
                ) as tot_qty'))
                ->where('con_id', $conReq)
                ->where(function ($query) {
                    $query->whereNull('aud_delete')
                        ->orWhere('aud_delete', '=', '0');
                })
                ->first();


            $totalExpense = $qryJobCost->tot_qty ?? 0;

            // Get total labor cost
            $qryTotalLabor = ContractJobLabor::from('trn_contract_job_labor AS tcjl')
                ->select(DB::raw('SUM((tcjl.cjl_rate * 1.08) * tcjl.cjl_qty * ?) as jml_rate_labor'))
                ->join('trn_contract_job as b', 'tcjl.cjb_id', '=', 'b.cjb_id')
                ->where(function ($query) {
                    $query->whereNull('b.aud_delete')
                        ->orWhere('b.aud_delete', '=', '0');
                })
                ->where('b.cjb_pay_template', '=', 'mandays')
                ->where('tcjl.con_id', $conReq)
                ->addBinding($days, 'select')
                ->first();


            if (!empty($qryTotalLabor->jml_rate_labor)) {
                $totalExpense += $qryTotalLabor->jml_rate_labor;
            }

            // Calculate balance budget
            $balanceBudget = $totBudget - $totalExpense;

            return response()->json([
                "status" => 200,
                "message" => "Successfully retrieved cost data",
                "data" => [
                    'totBudget' => $totBudget,
                    'totalExpense' => $totalExpense,
                    'balanceBudget' => $balanceBudget,
                    'durationStart' => $validated['con_duration_start'],
                    'durationEnd' => $validated['con_duration_end'],
                    'durationDay' => $validated['days']
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to retrieve cost data" . $e->getMessage(),
                "error" => "Server error",
            ], 500);
        }
    }

    public function costList(string $conReq, int $days)
    {
        try {
            $contractJobs = ContractJob::where(function ($query) use ($conReq) {
                if (is_array($conReq)) {
                    $query->orWhereIn('con_id', $conReq);
                } else {
                    $query->orWhere('con_id', $conReq);
                }
            })
                ->where(function ($query) {
                    $query->whereNull('aud_delete')
                        ->orWhere('aud_delete', '0');
                })
                ->get();

            $grandTotal = 0;
            $arrData = [
                'not_mandays' => [],
                'mandays' => [],
            ];

            foreach ($contractJobs as $rowJob) {
                $shiftCount = TrnShift::where('sh_con_req_id', $conReq)
                    ->selectRaw('COUNT(sh_con_req_id) as jum')
                    ->groupBy('sh_con_req_id')
                    ->value('jum') ?? 0;

                $laborJobs = ContractJobLabor::from('trn_contract_job_labor AS a')
                    ->join('trn_contract_job as b', 'a.cjb_id', '=', 'b.cjb_id')
                    ->where('a.cjb_id', $rowJob->cjb_id)
                    ->where(function ($query) {
                        $query->whereNull('b.aud_delete')
                            ->orWhere('b.aud_delete', '=', '0');
                    })
                    ->get();

                if ($rowJob->cjb_pay_template != 'mandays') {
                    $total = $rowJob->cjb_qty * $rowJob->cjb_rate;
                    if ($rowJob->cjb_pay_template == 'p1' || $rowJob->cjb_pay_template == 'rit') {
                        $total *= 1.08;
                    }

                    $grandTotal += $total;

                    $arrData['not_mandays'][] = [
                        'jum' => $shiftCount,
                        'cjb_desc' => $rowJob->cjb_desc,
                        'cjb_pay_template' => $rowJob->cjb_pay_template,
                        'cjb_rate' => $rowJob->cjb_rate,
                        'cjb_qty' => $rowJob->cjb_qty,
                        'cjb_pay_type' => $rowJob->cjb_pay_type,
                        'unt_id' => $rowJob->unt_id,
                        'job_labor' => $laborJobs,
                        'total' => $total
                    ];
                } else {
                    foreach ($laborJobs as $rowLabor) {
                        $rate = $rowLabor->cjl_rate * 1.08;
                        $laborTotal = $rate * $rowLabor->cjl_qty * $days;
                        $grandTotal += $laborTotal;
                    }
                    $arrData['mandays'][] = [
                        'cjb_desc' => $rowJob->cjb_desc,
                        'jum' => $shiftCount,
                        'cjb_qty' => $rowJob->cjb_qty,
                        'unt_id' => $rowJob->unt_id,
                        'cjb_pic' => $rowJob->cjb_pic,
                        'job_labor' => $laborJobs,
                    ];
                }
            }

            $arrData['grand_total'] = $grandTotal;

            return response()->json([
                "status" => 200,
                "message" => "Successfully retrieved cost detail data",
                "data" => $arrData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to retrieve cost detail data" . $e->getMessage(),
                "error" => "Server error",
            ], 500);
        }
    }
}
