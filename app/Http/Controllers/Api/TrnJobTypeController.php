<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Contract;

class TrnJobTypeController extends Controller
{

    public function getRangeIncrement($requestId)
    {
        $row = Contract::firstWhere('con_req_id', $requestId);

        if (!$row) {
            return response()->json(['error' => 'Data not found'], 404);
        }

        $start = Carbon::parse($row['con_duration_start'])->startOfMonth();
        $end = Carbon::parse($row['con_duration_end'])->addMonth()->startOfMonth();

        $dates = [];
        for ($date = $start; $date < $end; $date->addMonth()) {
            $dates[] = $date->format('Y-m');
        }

        return response()->json([
            'periods' => $dates,
        ]);
    }

    public function addData()
    {
        return true;
    }
}
