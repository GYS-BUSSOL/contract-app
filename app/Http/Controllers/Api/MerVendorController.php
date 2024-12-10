<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\MerVendor;

class MerVendorController extends Controller
{
  protected $vendor;

  public function __construct(MerVendor $vendor)
  {
    $this->vendor = $vendor;
  }

  public function edit(int $id)
  {
    try {
      $vendor = $this->vendor->firstWhere('vnd_id', $id);
      if (empty($vendor)) {
        return response()->json([
          "status" => 404,
          "message" => "Vendor not found",
        ], 404);
      }
      return response()->json([
        "status" => 200,
        "message" => "Successfully retrieved vendor data",
        "data" => $vendor
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        "status" => 500,
        "message" => "Failed to retrieve vendor data",
        "error" => "Server error",
      ], 500);
    }
  }

  public function list()
  {
    try {
      $dataGet = $this->vendor->get();
      $totalRecord = $dataGet->count();

      return response()->json([
        'status' => 200,
        'message' => 'Successfully retrieved mer vendor unit data',
        'data' => [
          'rows' => $dataGet,
          'total_record' => $totalRecord
        ],
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 500,
        'message' => 'Failed to retrieve mer vendor unit data',
        'data' => [
          'rows' => [],
          'total_record' => 0
        ],
      ], 500);
    }
  }
}
