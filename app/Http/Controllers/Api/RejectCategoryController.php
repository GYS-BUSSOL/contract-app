<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RejectCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RejectCategoryController extends Controller
{
    protected $rejectCategory;

    public function __construct(RejectCategory $rejectCategory)
    {
        $this->rejectCategory = $rejectCategory;
    }

    public function list()
    {
        try {
            $dataGet = $this->rejectCategory->get();
            $totalRecord = $dataGet->count();
            Log::info($dataGet);
            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved reject category data',
                'data' => [
                    'rows' => $dataGet,
                    'total_record' => $totalRecord
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve reject category data',
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }
}
