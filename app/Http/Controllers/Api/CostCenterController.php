<?php

namespace App\Http\Controllers\Api;

use App\Models\MerBUCCWC;
use Illuminate\Http\{Request, JsonResponse};
use App\Http\Controllers\Controller;

class CostCenterController extends Controller
{
    protected $mbcw;

    public function __construct(MerBUCCWC $mbcw)
    {
        $this->mbcw = $mbcw;
    }

    public function search(Request $request)
    {
        $tableColumn = $this->mbcw->getTable();
        try {
            $payload = $request->all();
            $countBuilderAll = $this->customSearchData($payload, $tableColumn);
            $dataBuilder = $this->setUpPayload($payload, $tableColumn);
            $builder = $dataBuilder['builder'];
            $countBuilderDistinct = $dataBuilder['distinct'];
            $dataGet = $builder->distinct()->get();
            $totalRecord = $countBuilderDistinct->get()->count();
            $totalShowData = $dataGet->count();
            $countActiveData = $countBuilderAll->where('is_active', 0)->whereNotNull('is_active')->count();
            $countNotActiveData = $countBuilderAll->where('is_active', 1)->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved area cost center data',
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
                'message' => 'Failed to retrieve area cost center data',
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
            'description' => ['required', 'string'],
            'number' => ['required', 'string'],
            'group_dimension' => ['nullable'],
            'is_active' => ['nullable']
        ];

        $validated = $this->handleValidationException($request, $data);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            $validated['group_dimension'] = "Cost Center";
            $validated['is_active'] = 0;
            $this->mbcw->create($validated);

            return response()->json([
                'status' => 200,
                'message' => 'Area cost center created successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Server error',
                'message' => 'Area cost center failed to create',
            ], 500);
        }
    }

    public function edit(int $id)
    {
        try {
            $areaCC = $this->mbcw->firstWhere('id', $id);
            if (empty($areaCC)) {
                return response()->json([
                    "status" => 404,
                    "message" => "Area cost center not found",
                ], 404);
            }
            return response()->json([
                "status" => 200,
                "message" => "Successfully retrieved area cost center data",
                'data' => $areaCC
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to retrieve area cost center data",
                "error" => "Server error",
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        $data = [
            'description' => ['required', 'string'],
            'number' => ['required', 'string'],
            'group_dimension' => ['nullable'],
            'is_active' => ['nullable']
        ];

        $validated = $this->handleValidationException($request, $data);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            $mbcw = $this->mbcw->firstWhere('id', $id);
            if (empty($mbcw)) {
                return response()->json([
                    "status" => 404,
                    "message" => "Area cost center not found",
                ], 404);
            }
            $validated['group_dimension'] = "Cost Center";
            $validated['is_active'] = 0;

            if ($mbcw->update($validated)) {
                return response()->json([
                    "status" => 200,
                    "message" => "Successfully updated area cost center data",
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to updated area cost center data",
                "error" => "Server error",
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $mbcw = $this->mbcw->firstWhere('id', $id);
            if (empty($mbcw)) {
                return response()->json([
                    "status" => 404,
                    "message" => "Area cost center not found",
                ], 404);
            }

            if ($mbcw->delete()) {
                return response()->json([
                    "status" => 200,
                    "message" => "Successfully deleted area cost center data"
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to delete area cost center data",
                "error" => "Server error",
            ], 500);
        }
    }

    private function customSearchData($payload, $tableColumn)
    {
        $data = $this->mbcw->where(function ($query) use ($payload, $tableColumn) {
            if (isset($payload['columns'])) {
                $listWhere = $payload['columns'];
                foreach ($listWhere as $where) {
                    $value = $where['value'];
                    if ($value && $value != "" && $value != " "  && $where['name'] != 'is_active') {
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
