<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ManDaysRate;
use Illuminate\Http\{Request, JsonResponse};
use Carbon\Carbon;

class ManDaysRateController extends Controller
{
    protected $mandays;

    public function __construct(ManDaysRate $mandays)
    {
        $this->mandays = $mandays;
    }

    public function search(Request $request)
    {
        $tableColumn = $this->mandays->getTable();
        try {
            $payload = $request->all();
            $countBuilderAll = $this->customSearchData($payload, $tableColumn);
            $dataBuilder = $this->setUpPayload($payload, $tableColumn);
            $builder = $dataBuilder['builder'];
            $countBuilderDistinct = $dataBuilder['distinct'];
            $dataGet = $builder->distinct()->get();
            $totalRecord = $countBuilderDistinct->get()->count();
            $totalShowData = $dataGet->count();
            $countActiveData = $countBuilderAll->where('rtk_active_status', 'Active')->count();
            $countNotActiveData = $countBuilderAll->where('rtk_active_status', 'Non Active')->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved Man Days Rate data',
                'data' => [
                    'rows' => $dataGet,
                    'total_data' => $totalShowData,
                    'total_record' => $totalRecord,
                    'total_active' => $countActiveData,
                    'total_not_active' => $countNotActiveData
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve man days rate data',
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
            'laborType' => ['required', 'exists:mer_labor_type,labor_type'],
            'effectiveDate' => ['required'],
            'rate' => ['required', 'numeric'],
        ];
        $validated = $this->handleValidationException($request, $data);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            $validated['effectiveDate'] = Carbon::parse(trim($validated['effectiveDate'], '"'))->format('Y-m-d');
            $manDaysData = [
                'rtk_effective_date' => $validated['effectiveDate'],
                'rtk_id_jenis_tk' => $validated['laborType'],
                'rtk_rate' => $validated['rate'],
                'rtk_active_status' => 'Active',
            ];

            $this->mandays->create($manDaysData);

            return response()->json([
                'status' => 200,
                'message' => 'Man days rate created successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Server error',
                'message' => 'Man days rate failed to create',
            ], 500);
        }
    }

    public function edit(int $id)
    {
        try {
            $manDays = $this->mandays->firstWhere('rtk_id', $id);
            if (empty($manDays)) {
                return response()->json([
                    "status" => 404,
                    "message" => "Man days rate not found",
                ], 404);
            }
            return response()->json([
                "status" => 200,
                "message" => "Successfully retrieved man days rate data",
                "data" => $manDays
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to retrieve man days rate data",
                "error" => "Server error",
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        $data = [
            'laborType' => ['required', 'exists:mer_labor_type,labor_type'],
            'effectiveDate' => ['required'],
            'rate' => ['required', 'numeric'],
        ];
        $validated = $this->handleValidationException($request, $data);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            $manDays = $this->mandays->firstWhere('rtk_id', $id);
            if (empty($manDays)) {
                return response()->json([
                    "status" => 404,
                    "message" => "Man days rate not found",
                ], 404);
            }

            $validated['effectiveDate'] = Carbon::parse(trim($validated['effectiveDate'], '"'))->format('Y-m-d');
            $manDaysData = [
                'rtk_effective_date' => $validated['effectiveDate'],
                'rtk_id_jenis_tk' => $validated['laborType'],
                'rtk_rate' => $validated['rate'],
                'rtk_active_status' => 'Active',
            ];

            if ($manDays->update($manDaysData)) {
                return response()->json([
                    "status" => 200,
                    "message" => "Successfully updated man days rate data",
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to updated man days rate data",
                "error" => "Server error",
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $manDays = $this->mandays->firstWhere('rtk_id', $id);

            if (empty($manDays)) {
                return response()->json([
                    "status" => 404,
                    "message" => "Man days rate not found",
                ], 404);
            }

            if ($manDays->delete()) {
                return response()->json([
                    "status" => 200,
                    "message" => "Successfully deleted man days rate data"
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to delete man days rate data",
                "error" => "Server error",
            ], 500);
        }
    }

    private function customSearchData($payload, $tableColumn)
    {
        $data = $this->mandays->where(function ($query) use ($payload, $tableColumn) {
            if (isset($payload['columns'])) {
                $listWhere = $payload['columns'];
                foreach ($listWhere as $where) {
                    $value = $where['value'];
                    if ($value && $value != "" && $value != " "  && $where['name'] != 'rtk_active_status') {
                        $column = $where['name'];
                        $operator = strtolower($where['logic_operator']);
                        if ($operator == "=") {
                            $query->where($tableColumn . "." . $column, $value);
                        }
                    }
                }
            }
        })
            ->distinct()
            ->get();

        return $data;
    }
}
