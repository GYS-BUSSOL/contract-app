<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\SignatureType;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\{Request, JsonResponse};

class SignatureTypeController extends Controller
{
    protected $st;

    public function __construct(SignatureType $st)
    {
        $this->st = $st;
    }

    public function search(Request $request)
    {
        $tableColumn = $this->st->getTable();
        try {
            $payload = $request->all();
            $dataBuilder = $this->setUpPayload($payload, $tableColumn);
            $builder = $dataBuilder['builder'];
            $countBuilderDistinct = $dataBuilder['distinct'];
            $dataGet = $builder->distinct()->get();
            $totalRecord = $countBuilderDistinct->get()->count();
            $totalShowData = $dataGet->count();

            $resultData = [];

            foreach ($dataGet as $arrDataParent) {
                $mulData = $this->customData(explode(",", $arrDataParent->st_user));
                $arrMULData = ['arr_user' => []];

                foreach ($mulData as $mermbcw) {
                    if (in_array($mermbcw['usr_id'], explode(",", $arrDataParent->st_user))) {
                        $arrMULData['arr_user'][] = [
                            'usr_id' => $mermbcw['usr_id'],
                            'usr_display_name' => $mermbcw['usr_display_name']
                        ];
                    }
                }
                $mergedData = array_merge(
                    json_decode(json_encode($arrDataParent), true),
                    $arrMULData
                );
                $resultData[] = $mergedData;
            }

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved signature type data',
                'data' => [
                    'rows' => $resultData,
                    'total_data' => $totalShowData,
                    'total_record' => $totalRecord,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve signature type data',
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
            'st_desc' => ['required', 'string'],
            'st_user' => ['required', 'array'],
        ];

        $validated = $this->handleValidationException($request, $data);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            if (is_string($validated['st_user'])) {
                $validated['st_user'] = json_decode($validated['st_user'], true);
            }

            if (is_array($validated['st_user'])) {
                $validated['st_user'] = join(", ", $validated['st_user']);
            }
            $this->st->create($validated);

            return response()->json([
                'status' => 200,
                'message' => 'Signature type created successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Server error',
                'message' => 'Signature type failed to create',
            ], 500);
        }
    }

    public function edit(int $id)
    {
        try {
            $signType = $this->st->firstWhere('st_id', $id);
            if (empty($signType)) {
                return response()->json([
                    "status" => 404,
                    "message" => "Signature type not found",
                ], 404);
            }
            return response()->json([
                "status" => 200,
                "message" => "Successfully retrieved signature type data",
                'data' => $signType
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to retrieve signature type data",
                "error" => "Server error",
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        $data = [
            'st_desc' => ['required', 'string'],
            'st_user' => ['required', 'array'],
        ];

        $validated = $this->handleValidationException($request, $data);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            $st = $this->st->firstWhere('st_id', $id);
            if (empty($st)) {
                return response()->json([
                    "status" => 404,
                    "message" => "Signature type not found",
                ], 404);
            }
            if (is_string($validated['st_user'])) {
                $validated['st_user'] = json_decode($validated['st_user'], true);
            }

            if (is_array($validated['st_user'])) {
                $validated['st_user'] = join(", ", $validated['st_user']);
            }

            if ($st->update($validated)) {
                return response()->json([
                    "status" => 200,
                    "message" => "Successfully updated signature type data",
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to updated signature type data",
                "error" => "Server error",
            ], 500);
        }
    }

    public function list()
    {
        try {
            $dataGet = $this->st->get();
            $totalRecord = $dataGet->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved mer signature type data',
                'data' => [
                    'rows' => $dataGet,
                    'total_record' => $totalRecord
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve mer signature type data',
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $st = $this->st->firstWhere('st_id', $id);
            if (empty($st)) {
                return response()->json([
                    "status" => 404,
                    "message" => "Signature type not found",
                ], 404);
            }

            if ($st->delete()) {
                return response()->json([
                    "status" => 200,
                    "message" => "Successfully deleted signature type data"
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to delete signature type data",
                "error" => "Server error",
            ], 500);
        }
    }

    private function customData($usrID)
    {
        $mul = User::query()
            ->from('mer_user_login AS mul')
            ->select('mul.usr_id', 'mul.usr_display_name')
            ->where('mul.usr_access', 'approval')
            ->whereIn('mul.usr_id', $usrID)
            ->get();

        return $mul;
    }
}
