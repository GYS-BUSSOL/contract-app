<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BUController extends Controller
{
  public function list()
  {
    try {
      $dataGet = DB::select('EXEC [dbo].[sp_GetListBU]');
      $totalRecord = count($dataGet);

      return response()->json([
        'status' => 200,
        'message' => 'Successfully retrieved business unit data',
        'data' => [
          'rows' => $dataGet,
          'total_record' => $totalRecord
        ],
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'status' => 500,
        'message' => 'Failed to retrieve business unit data',
        'data' => [
          'rows' => [],
          'total_record' => 0
        ],
      ], 500);
    }
  }
}
