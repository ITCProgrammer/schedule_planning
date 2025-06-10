<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonPeriod;

class ScheduleController extends Controller
{

    public function index(){
        $mesin = DB::connection('sqlsrv')->select('EXEC sp_get_mesin_detail');
        $groupedMesin = collect($mesin)->groupBy('jenis');
        return view('schedule',[
            'groupedMesin' => $groupedMesin,
            'carbon' => Carbon::now(),
        ]);
    }

    // public function index(Request $request){
    //     $view = $request->query('view', 'minggu');
    //     $today = Carbon::today();
    //     $endDate = $today->copy()->addDays(364);

    //     // Hari libur tambahan
    //     $libur = [
    //     ];

    //     $tanggalKerja = [];
    //     $tanggal = $today->copy();

    //     while ($tanggal <= $endDate) {
    //         if ($tanggal->isSunday() || in_array($tanggal->toDateString(), $libur)) {
    //             $tanggal->addDay();
    //             continue;
    //         }
    //         $tanggalKerja[] = $tanggal->copy();
    //         $tanggal->addDay();
    //     }

    //     $produksi = DB::connection('sqlsrv')->table('dbo.schedule_mesin')
    //         ->whereDate('end_date', '>=', $today)
    //         ->whereDate('start_date', '<=', $endDate)
    //         ->get();

    //     $dataPerMesin = [];
    //     $colorMap = [];

    //     foreach ($produksi as $item) {
    //         $mesin = $item->mesin_code;
    //         $itemCode = $item->datecreated;
    //         $reqQty = $item->qty;

    //         if (!isset($colorMap[$itemCode])) {
    //             $colorMap[$itemCode] = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    //         }

    //         $startDate = Carbon::parse($item->start_date);
    //         $endDateItem = Carbon::parse($item->end_date);
    //         $rangeDates = $startDate->toPeriod($endDateItem, '1 day');
    //         $totalQty = 0;
    //         $hsilSum = 0;

    //         // foreach ($rangeDates as $date) {
    //         //     if ($date->isSunday() || in_array($date->toDateString(), $libur)) {
    //         //         continue;
    //         //     }

    //         //     $totalQty += $item->qty_day;

    //         //     $dataPerMesin[$mesin][$date->toDateString()] = [
    //         //         'text' => "{$item->item_code} - Qty: " . number_format($totalQty, 0),
    //         //         'item_code' => $itemCode,
    //         //         'color' => $colorMap[$itemCode],
    //         //     ];
    //         // }
    //         $workingDates = [];
    //         foreach ($rangeDates as $date) {
    //             if ($date->isSunday() || in_array($date->toDateString(), $libur)) {
    //                 continue;
    //             }
    //             $workingDates[] = $date;
    //         }

    //         $totalQty = 0;
    //         $jumlahTanggal = count($workingDates);

    //         foreach ($workingDates as $index => $date) {
    //             $tanggalStr = $date->toDateString();
    //             $totalQty += $item->qty_day;

    //             // Tanggal terakhir
    //             if ($index === $jumlahTanggal - 1) {
    //                 $displayQty = $reqQty;
    //                 $overQty = $totalQty - $reqQty;

    //                 $text = "{$item->item_code} - Qty: " . number_format($displayQty, 0);
    //                 if ($overQty > 0) {
    //                     $text .= "\nAvailable Qty: " . number_format($overQty, 0);
    //                 }
    //             } else {
    //                 $text = "{$item->item_code} - Qty: " . number_format($totalQty, 0);
    //             }

    //             $dataPerMesin[$mesin][$tanggalStr] = [
    //                 'text' => $text,
    //                 'item_code' => $itemCode,
    //                 'color' => $colorMap[$itemCode],
    //             ];
    //         }
    //     }

    //     // Membuat data untuk kalender
    //     $events = [];

    //     foreach ($dataPerMesin as $mesin => $produksiPerTanggal) {
    //         foreach ($produksiPerTanggal as $tanggal => $data) {
    //             $events[] = [
    //                 'title' => "{$mesin} - {$data['text']}",
    //                 'start' => $tanggal,
    //                 'color' => $data['color'],
    //             ];
    //         }
    //     }

    //     return view('schedule', compact(
    //         'view',
    //         'tanggalKerja',
    //         'dataPerMesin',
    //         'today',
    //         'colorMap',
    //         'events'
    //     ));
    // }

    public function dataFilter(Request $request)
    {
        $search = $request->input('q');

        $results = DB::connection('DB2')
            ->table('PRODUCT as p')
            ->distinct()
            ->select(
                DB::raw('a.VALUEDECIMAL'),
                DB::raw('ROUND(a.VALUEDECIMAL * 24) AS CALCULATION'),
                DB::raw("TRIM(p.SUBCODE02) || '-' || TRIM(p.SUBCODE03) || '-' || TRIM(p.SUBCODE04) AS hanger")
            )
            ->leftJoin('ADSTORAGE as a', function($join) {
                $join->on('a.UNIQUEID', '=', 'p.ABSUNIQUEID')
                    ->where('a.FIELDNAME', '=', 'ProductionRate');
            })
            ->where('p.ITEMTYPECODE', 'KGF')
            ->when($search, function ($query, $search) {
                $query->whereRaw("TRIM(p.SUBCODE02) || '-' || TRIM(p.SUBCODE03) || '-' || TRIM(p.SUBCODE04) LIKE ?", ["%$search%"])
                      ->orWhereRaw("TRIM(p.LONGDESCRIPTION) LIKE ?", ["%$search%"]);
            })
            ->orderBy('hanger', 'ASC')
            ->limit(10)
            ->get();

        $formatted = $results->map(function ($item) {
            return [
                'id' => $item->hanger,
                'text' => $item->hanger
            ];
        });

        $formatted->prepend([
            'id' => '',
            'text' => 'Pilih nomor hanger'
        ]);

        return response()->json($formatted);
    }
}
