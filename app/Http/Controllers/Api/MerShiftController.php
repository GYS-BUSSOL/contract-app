<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MerShift;

class MerShiftController extends Controller
{
    protected $shift;

    public function __construct(MerShift $shift)
    {
        $this->shift = $shift;
    }

    public function list()
    {
        try {
            $dataGet = $this->shift->get();
            $totalRecord = $dataGet->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved shift data',
                'data' => [
                    'rows' => $dataGet,
                    'total_record' => $totalRecord
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve shift data',
                "error" => "Server error",
            ], 500);
        }
    }
}
