<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TimeHistory;
use Illuminate\Http\Request;

class TimeHistoryController extends Controller
{
    protected $history;

    public function __construct(TimeHistory $history)
    {
        $this->history = $history;
    }

    public function list(string $conReq)
    {
        try {
            $data = $this->history
                ->when(is_array($conReq), function ($query) use ($conReq) {
                    return $query->orWhereIn('tc.con_req_id', $conReq);
                }, function ($query) use ($conReq) {
                    if (is_numeric($conReq)) {
                        return $query->orWhere('tc.con_req_id', $conReq);
                    }
                })
                ->leftJoin('mer_contract_status AS mcs', 'trn_time_history.sts_id', 'mcs.sts_id')
                ->leftJoin('trn_contract AS tc', 'trn_time_history.con_id', 'tc.con_req_id')
                ->select('trn_time_history.*', 'mcs.sts_description')
                ->get();
            return response()->json([
                "status" => 200,
                "message" => "Successfully retrieved history data",
                "data" => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to retrieve history data",
                "error" => "Server error",
            ], 500);
        }
    }

    public function listReviewer(string $conReq)
    {
        try {
            $data = $this->history
                ->when(is_array($conReq), function ($query) use ($conReq) {
                    return $query->orWhereIn('tc.con_req_id', $conReq);
                }, function ($query) use ($conReq) {
                    if (is_numeric($conReq)) {
                        return $query->orWhere('tc.con_req_id', $conReq);
                    }
                })
                ->whereIn('trn_time_history.sts_id', ['4', '5', '6', '7', '15', '16'])
                ->leftJoin('mer_contract_status AS mcs', 'trn_time_history.sts_id', 'mcs.sts_id')
                ->leftJoin('trn_contract AS tc', 'trn_time_history.con_id', 'tc.con_req_id')
                ->select('trn_time_history.*', 'mcs.sts_description')
                ->get();
            return response()->json([
                "status" => 200,
                "message" => "Successfully retrieved history data",
                "data" => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to retrieve history data",
                "error" => "Server error",
            ], 500);
        }
    }

    public function listCost(string $conReq)
    {
        try {
            $data = $this->history
                ->when(is_array($conReq), function ($query) use ($conReq) {
                    return $query->orWhereIn('tc.con_req_id', $conReq);
                }, function ($query) use ($conReq) {
                    if (is_numeric($conReq)) {
                        return $query->orWhere('tc.con_req_id', $conReq);
                    }
                })
                ->whereIn('trn_time_history.sts_id', ['4', '5', '6', '7', '15', '16', '3'])
                ->leftJoin('mer_contract_status AS mcs', 'trn_time_history.sts_id', 'mcs.sts_id')
                ->leftJoin('trn_contract AS tc', 'trn_time_history.con_id', 'tc.con_req_id')
                ->select('trn_time_history.*', 'mcs.sts_description')
                ->get();
            return response()->json([
                "status" => 200,
                "message" => "Successfully retrieved history data",
                "data" => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to retrieve history data",
                "error" => "Server error",
            ], 500);
        }
    }
}
