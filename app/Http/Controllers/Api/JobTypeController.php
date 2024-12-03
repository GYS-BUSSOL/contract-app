<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\JobType;
use App\Http\Controllers\Controller;
use Illuminate\Http\{Request, JsonResponse};

class JobTypeController extends Controller
{
    protected $jt;

    public function __construct(JobType $jt)
    {
        $this->jt = $jt;
    }

    public function search(Request $request)
    {
        $tableColumn = $this->jt->getTable();
        try {
            $payload = $request->all();
            $countBuilderAll = $this->customSearchData($payload, $tableColumn);
            $dataBuilder = $this->setUpPayload($payload, $tableColumn);
            $builder = $dataBuilder['builder'];
            $countBuilderDistinct = $dataBuilder['distinct'];
            $dataGet = $builder->distinct()->get();
            $totalRecord = $countBuilderDistinct->get()->count();
            $totalShowData = $dataGet->count();
            $countActiveData = $countBuilderAll->whereNull('job_is_active')->count();
            $countNotActiveData = $countBuilderAll->where('job_is_active', 1)->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved job type data',
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
                'message' => 'Failed to retrieve job type data',
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }

    public function list()
    {
        try {
            $dataGet = $this->jt->get();
            $totalRecord = $dataGet->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved job type data',
                'data' => [
                    'rows' => $dataGet,
                    'total_record' => $totalRecord
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve job type data',
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
            'job_type' => ['required', 'string'],
            'aud_user' => ['nullable'],
            'aud_date' => ['nullable'],
            'aud_prog' => ['nullable'],
            'aud_machine' => ['nullable']
        ];

        $validated = $this->handleValidationException($request, $data);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            $validated['aud_user'] = 'Wahyu'; // Nama lengkap User login
            $validated['aud_date'] = Carbon::now();
            $validated['aud_prog'] = 'CMSY';
            $validated['aud_machine'] = $request->server('REMOTE_ADDR');
            $this->jt->create($validated);

            return response()->json([
                'status' => 200,
                'message' => 'Job type created successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Server error',
                'message' => 'Job type failed to create',
            ], 500);
        }
    }

    public function edit(int $id)
    {
        try {
            $jobType = $this->jt->firstWhere('job_type_id', $id);
            if (empty($jobType)) {
                return response()->json([
                    "status" => 404,
                    "message" => "Job type not found",
                ], 404);
            }
            return response()->json([
                "status" => 200,
                "message" => "Successfully retrieved job type data",
                'data' => $jobType
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to retrieve job type data",
                "error" => "Server error",
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        $data = [
            'job_type' => ['required', 'string'],
            'aud_user' => ['nullable'],
            'aud_date' => ['nullable'],
            'aud_prog' => ['nullable'],
            'aud_machine' => ['nullable']
        ];

        $validated = $this->handleValidationException($request, $data);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            $jt = $this->jt->firstWhere('job_type_id', $id);
            if (empty($jt)) {
                return response()->json([
                    "status" => 404,
                    "message" => "Job type not found",
                ], 404);
            }
            $validated['aud_user'] = 'Wahyu'; // Nama lengkap User login
            $validated['aud_date'] = Carbon::now();
            $validated['aud_prog'] = 'CMSY';
            $validated['aud_machine'] = $request->server('REMOTE_ADDR');

            if ($jt->update($validated)) {
                return response()->json([
                    "status" => 200,
                    "message" => "Successfully updated job type data",
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to updated job type data",
                "error" => "Server error",
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $jt = $this->jt->firstWhere('job_type_id', $id);
            if (empty($jt)) {
                return response()->json([
                    "status" => 404,
                    "message" => "Job type not found",
                ], 404);
            }

            if ($jt->update([
                'job_is_active' => 1
            ])) {
                return response()->json([
                    "status" => 200,
                    "message" => "Successfully deleted job type data"
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to delete job type data",
                "error" => "Server error",
            ], 500);
        }
    }

    private function customSearchData($payload, $tableColumn)
    {
        $data = $this->jt->where(function ($query) use ($payload, $tableColumn) {
            if (isset($payload['columns'])) {
                $listWhere = $payload['columns'];
                foreach ($listWhere as $where) {
                    $value = $where['value'];
                    if ($value && $value != "" && $value != " "  && $where['name'] != 'job_is_active' && $where['logic_operator'] != '=') {
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
