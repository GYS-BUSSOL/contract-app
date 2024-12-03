<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\{Request, JsonResponse};
use App\Http\Controllers\Controller;
use App\Models\MerBUCCWC;
use Illuminate\Support\Facades\Log;

class HumanResourcesController extends Controller
{
    protected $users;

    public function __construct(User $users)
    {
        $this->users = $users;
    }

    public function search(Request $request)
    {
        $tableColumn = $this->users->getTable();
        try {
            $payload = $request->all();
            $countBuilderAll = $this->customSearchData($payload, $tableColumn);
            $dataBuilder = $this->setUpPayload($payload, $tableColumn);
            $builder = $dataBuilder['builder'];
            $countBuilderDistinct = $dataBuilder['distinct'];
            $dataGet = $builder->distinct()->get();
            $totalRecord = $countBuilderDistinct->get()->count();
            $totalShowData = $dataGet->count();
            $countActiveData = $countBuilderAll->where('is_active', 0)->count();
            $countNotActiveData = $countBuilderAll->where('is_active', 1)->count();

            $resultData = [];

            foreach ($dataGet as $arrDataParent) {
                $mermbcwData = $this->customData(explode(",", $arrDataParent->bu_id));
                $arrMerMBCWData = ['arr_business_unit' => []];

                foreach ($mermbcwData as $mermbcw) {
                    if (in_array($mermbcw['id'], explode(",", $arrDataParent->bu_id))) {
                        $arrMerMBCWData['arr_business_unit'][] = [
                            'number' => $mermbcw['number'],
                            'description' => $mermbcw['description']
                        ];
                    }
                }
                $mergedData = array_merge(
                    json_decode(json_encode($arrDataParent), true),
                    $arrMerMBCWData
                );
                $resultData[] = $mergedData;
            }

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved user data',
                'data' => [
                    'rows' => $resultData,
                    'total_data' => $totalShowData,
                    'total_record' => $totalRecord,
                    'total_active' => $countActiveData,
                    'total_not_active' => $countNotActiveData
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve user data',
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
            $dataGet = $this->users->get();
            $totalRecord = $dataGet->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved user data',
                'data' => [
                    'rows' => $dataGet,
                    'total_record' => $totalRecord
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve user data',
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }

    public function listWithRole(string $accessRole)
    {
        try {
            $dataGet = $this->users->where('usr_access', $accessRole)->get();
            $totalRecord = $dataGet->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved user ' . $accessRole . ' data',
                'data' => [
                    'rows' => $dataGet,
                    'total_record' => $totalRecord
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve user ' . $accessRole . ' data',
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }

    public function add(Request $request)
    {
        Log::info($request->all());
        $data = [
            'Username' => ['required', 'string'],
            'UserDisplay' => ['required', 'string'],
            'NoTlp' => ['required'],
            'Access' => ['required', 'string', 'in:admin,approval,requester'],
            'BU' => ['required', 'array']
        ];

        $validated = $this->handleValidationException($request, $data);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            if (is_string($validated['BU'])) {
                $validated['BU'] = json_decode($validated['BU'], true);
            }

            if (is_array($validated['BU'])) {
                $validated['BU'] = join(", ", $validated['BU']);
            }
            $userData = [
                'usr_name' => $validated['Username'],
                'usr_display_name' => $validated['UserDisplay'],
                'usr_no_tlp' => $validated['NoTlp'],
                'usr_access' => $validated['Access'],
                'bu_id' => $validated['BU'],
            ];

            $this->users->create($userData);

            return response()->json([
                'status' => 200,
                'message' => 'User created successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Server error',
                'message' => 'User failed to create',
            ], 500);
        }
    }

    public function edit(int $id)
    {
        try {
            $userHR = $this->users->firstWhere('usr_id', $id);
            if (empty($userHR)) {
                return response()->json([
                    "status" => 404,
                    "message" => "User not found",
                ], 404);
            }
            return response()->json([
                "status" => 200,
                "message" => "Successfully retrieved user data",
                "data" => $userHR
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to retrieve user data",
                "error" => "Server error",
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        $data = [
            'Username' => ['required', 'string'],
            'UserDisplay' => ['required', 'string'],
            'NoTlp' => ['required'],
            'Access' => ['required', 'string', 'in:admin,approval,requester'],
            'BU' => ['required', 'array']
        ];

        $validated = $this->handleValidationException($request, $data);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            $userHR = $this->users->firstWhere('usr_id', $id);
            if (empty($userHR)) {
                return response()->json([
                    "status" => 404,
                    "message" => "User not found",
                ], 404);
            }

            if (is_string($validated['BU'])) {
                $validated['BU'] = json_decode($validated['BU'], true);
            }

            if (is_array($validated['BU'])) {
                $validated['BU'] = join(", ", $validated['BU']);
            }
            $userData = [
                'usr_name' => $validated['Username'],
                'usr_display_name' => $validated['UserDisplay'],
                'usr_no_tlp' => $validated['NoTlp'],
                'usr_access' => $validated['Access'],
                'bu_id' => $validated['BU'],
            ];

            if ($userHR->update($userData)) {
                return response()->json([
                    "status" => 200,
                    "message" => "Successfully updated user data",
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to updated user data",
                "error" => "Server error",
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $userHR = $this->users->firstWhere('usr_id', $id);
            if (empty($userHR)) {
                return response()->json([
                    "status" => 404,
                    "message" => "User not found",
                ], 404);
            }

            if ($userHR->delete()) {
                return response()->json([
                    "status" => 200,
                    "message" => "Successfully deleted user data"
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to delete user data",
                "error" => "Server error",
            ], 500);
        }
    }

    private function customSearchData($payload, $tableColumn)
    {
        $data = $this->users->where(function ($query) use ($payload, $tableColumn) {
            if (isset($payload['columns'])) {
                $listWhere = $payload['columns'];
                foreach ($listWhere as $where) {
                    $value = $where['value'];
                    if ($value && $value != "" && $value != " "  && $where['name'] != 'usr_access') {
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

    private function customData($BUId)
    {
        $mbcw = MerBUCCWC::query()
            ->from('mer_bu_cc_wc AS mbcw')
            ->where('mbcw.group_dimension', 'Business Unit')
            ->whereIn('id', $BUId)
            ->get();

        return $mbcw;
    }
}
