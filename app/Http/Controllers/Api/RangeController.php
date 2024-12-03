<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class RangeController extends Controller
{
    public function listYear(Request $request)
    {
        try {
            $validated = $request->validate([
                'tbl' => ['required', 'string'],
                'clmn' => ['required', 'string'],
            ]);

            $tableName = $validated['tbl'];
            $columnName = $validated['clmn'];

            if (!Schema::hasTable($tableName)) {
                return response()->json(['error' => 'Table does not exist'], 400);
            }

            if (!Schema::hasColumn($tableName, $columnName)) {
                return response()->json(['error' => 'Column does not exist in the table'], 400);
            }

            $minYear = DB::table($tableName)->min($columnName);
            $maxYear = DB::table($tableName)->max($columnName);

            $result = [
                'min_year' => $minYear,
                'max_year' => $maxYear,
            ];
            return response()->json([
                'status' => 200,
                'message' => 'Successfully get range year data',
                'data' => [
                    'rows' => $result,
                    'total_record' => count($result)
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve get range year data',
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }
}
