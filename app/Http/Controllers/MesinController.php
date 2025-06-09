<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;

class MesinController extends Controller
{
    public function index()
    {
       $dataMesin = DB::connection('sqlsrv')->select('EXEC sp_get_machine');
       $dataSchedule = DB::connection('sqlsrv')->select('EXEC sp_get_shedule');

       return response()->json([
            'dataMesin' => $dataMesin,
            'dataSchedule' => $dataSchedule,
        ]);
    }
}
