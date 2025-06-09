<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\CarbonPeriod;


class HomeController extends Controller
{
    public function index()
    {
        if (!Session::has('user')) {
            return redirect()->route('login');
        }

        $mesin = DB::connection('sqlsrv')->select('EXEC sp_get_mesin_detail');
        $groupedMesin = collect($mesin)->groupBy('jenis');

        $results = DB::connection('DB2')
            ->table('PRODUCT as p')
            ->distinct()
            ->select(
                DB::raw('a.VALUEDECIMAL'),
                DB::raw('ROUND(a.VALUEDECIMAL * 24) AS CALCULATION'),
                DB::raw('TRIM(p.SUBCODE02) || \'-\' ||TRIM(p.SUBCODE03) || \'-\' || TRIM(p.SUBCODE04) AS HANGER'),
                DB::raw('TRIM(p.LONGDESCRIPTION) AS LONGDESCRIPTION')
            )
            ->leftJoin('ADSTORAGE as a', function($join) {
                $join->on('a.UNIQUEID', '=', 'p.ABSUNIQUEID')
                    ->where('a.FIELDNAME', '=', 'ProductionRate');
            })
            ->where('p.ITEMTYPECODE', 'KGF')
            ->orderBy('HANGER', 'ASC')
            ->get();
        // return view('home', compact('mesin'));

        return view('home', [
            'groupedMesin' => $groupedMesin,
            'now' => Carbon::now(),
            'filter' => $results
        ]);
    }

    public function calculation(Request $request){
        $tgl_dlv = Carbon::parse($request->date2);
        $qty = (int) $request->txQty;
        $no_item = $request->no_item;
        $today = Carbon::parse($request->date1);
        [$subcode02, $subcode03, $subcode04] = explode('-', $request->no_item);

        $results = DB::connection('DB2')->select("
            SELECT DISTINCT 
                a.VALUEDECIMAL AS CALDAY,
                a.VALUEDECIMAL * 24 AS CALCULATION
            FROM PRODUCT p
            LEFT JOIN ADSTORAGE a ON a.UNIQUEID = p.ABSUNIQUEID
                AND a.FIELDNAME = 'ProductionRate'
            WHERE p.ITEMTYPECODE = 'KGF'
              AND p.SUBCODE02 = ?
              AND p.SUBCODE03 = ?
              AND p.SUBCODE04 = ?
        ", [$subcode02, $subcode03, $subcode04]);

        $holidays = [

        ];

        $period = CarbonPeriod::create($today, $tgl_dlv);
        $workdays = 0;

        foreach ($period as $date) {
            if (!in_array($date->toDateString(), $holidays) && $date->dayOfWeek !== Carbon::SUNDAY) {
                $workdays++;
            }
        }

        $count_machine = ceil($qty / $results[0]->calculation);
        $kebutuhan_mesin = max(1, round($count_machine / max($workdays, 1), 0));

        return response()->json([
            'start_date' => $today->toDateString(),
            'delivery_date' => $tgl_dlv->toDateString(),
            'today' => $today->toDateString(),
            'workdays_until_delivery' => $workdays,
            'qty' => $qty,
            'no_item' => $no_item,
            'calday' => $results[0]->calday,
            'calculation' => $results[0]->calculation,
            'jumlah_1mesin' => $count_machine,
            'kebutuhan_mesin' => $kebutuhan_mesin,
            'message' => "Tersisa {$workdays} hari kerja hingga tanggal delivery."
        ]);
    }

    public function machine(Request $request)
    {
        $mesin = DB::connection('sqlsrv')->select('EXEC sp_get_mesin_detail');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'no_item'        => 'required|string',
            'qty'            => 'required|numeric',
            'qty_day'        => 'required|numeric',
            'start_date'     => 'required|date',
            'delivery_date'  => 'required|date',
            'machines'       => 'required|array',
            'type'           => 'required|string',
            'machines.*'     => 'required|string'
        ]);

        $savedData = [];

        $itemCode = $validated['no_item'];
        $dateStr = now()->format('Ymd');

        $count = DB::connection('sqlsrv')
        ->table('schedule_mesin')
        ->where('item_code', $itemCode)
        ->whereDate('datecreated', now()->toDateString())
        ->count();

        $sequence = str_pad($count + 1, 5, '0', STR_PAD_LEFT);
        $linkedId = "SC-{$itemCode}-{$dateStr}-{$sequence}";

        $jumlahMesin = count($validated['machines']);
        $qtyDayPerMesin = $jumlahMesin > 0 ? ceil($validated['qty_day'] / $jumlahMesin) : 0;

        foreach ($validated['machines'] as $machineCode) {

            $dataToInsert = [
                'item_code'     => $validated['no_item'],
                'qty'           => $validated['qty'],
                'qty_day'       => $validated['qty_day'],
                'start_date'    => $validated['start_date'],
                'end_date'      => $validated['delivery_date'],
                'mesin_code'    => $machineCode,
                'type'          => $validated['type'],
                'dept'          => 'KNT',
                'datecreated'   => now(),
                'linked_id_machine' => $linkedId,
            ];

            DB::connection('sqlsrv')->table('schedule_mesin')->insert($dataToInsert);
            $savedData[] = $dataToInsert;
        }

        return response()->json([
            'status' => 'Success',
            'message' => 'Data berhasil disimpan.',
            'data' => $savedData
        ]);
    }


}
