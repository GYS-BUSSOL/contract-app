<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Penilaian;
use Illuminate\Http\Request;

class PenilaianController extends Controller
{
    protected $penilaian;

    public function __construct(Penilaian $penilaian)
    {
        $this->penilaian = $penilaian;
    }

    public function list(string $conReq)
    {
        try {
            $data = $this->penilaian
                ->when(is_array($conReq), function ($query) use ($conReq) {
                    return $query->orWhereIn('con_req_id', $conReq);
                }, function ($query) use ($conReq) {
                    if (is_numeric($conReq)) {
                        return $query->orWhere('con_req_id', $conReq);
                    }
                })
                ->get();
            return response()->json([
                "status" => 200,
                "message" => "Successfully retrieved data",
                "data" => $data
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to retrieve data" . $e->getMessage(),
                "error" => "Server error",
            ], 500);
        }
    }
}
