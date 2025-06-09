<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ListViewController extends Controller
{
    public function index(){
        $mesin = DB::connection('sqlsrv')->select('EXEC sp_get_mesin_detail');
        $groupedMesin = collect($mesin)->groupBy('jenis');
        return view('test',[
            'groupedMesin' => $groupedMesin,
            'carbon' => Carbon::now(),
        ]);
    }

    public function poList(){
        $mesin = DB::connection('sqlsrv')->select('EXEC sp_get_mesin_detail');
        $groupedMesin = collect($mesin)->groupBy('jenis');
        return view('poList',[
            'groupedMesin' => $groupedMesin,
            'carbon' => Carbon::now(),
        ]);
    }

    public function scheList(){
        $mesin = DB::connection('sqlsrv')->select('EXEC sp_get_mesin_detail');
        $groupedMesin = collect($mesin)->groupBy('jenis');
        return view('scheduleList',[
            'groupedMesin' => $groupedMesin,
            'carbon' => Carbon::now(),
        ]);
    }

    public function editSchedule(Request $request)
    {
        $scheduleData = $request->input('schedule');

        if (!is_array($scheduleData)) {
            return response()->json(['error' => 'Data tidak valid'], 400);
        }

        foreach ($scheduleData as $row) {
            $item_code = $row['item_code'] ?? null;
            $type = $row['type'] ?? null;
            $tanggal = $row['tanggal'] ?? null; // format YYYY-MM-DD
            $mesin_from = $row['mesin_from'] ?? null;
            $mesin_to = $row['mesin_to'] ?? null;

            if (!$item_code || !$type || !$tanggal || !$mesin_to) {
                continue; // skip data yang tidak lengkap
            }

            // Panggil stored procedure (ganti dengan nama SP kamu)
            DB::connection('sqlsrv')->statement('EXEC sp_edit_schedule ?, ?, ?, ?, ?', [
                $item_code,
                $type,
                $tanggal,
                $mesin_from,
                $mesin_to
            ]);
        }

        return response()->json(['message' => 'Schedule berhasil disimpan']);
    }

    public function forecastList(){
        $itemCode = DB::connection('sqlsrv')->select('EXEC sp_get_unique_item_codes');
        return view('forecastList',[
            'itemCode' => $itemCode,
            'carbon' => Carbon::now(),
        ]);
    }

    public function loadData(){
        $dataMesin = DB::connection('sqlsrv')->select('EXEC sp_get_shedule');

        $dataDB2 = DB::connection('DB2')->select("
            SELECT
                TRIM(p.SUBCODE02) AS SUBCODE02,
                TRIM(p.SUBCODE03) AS SUBCODE03,
                TRIM(p.SUBCODE04) AS SUBCODE04,
                a2.VALUEDATE AS RMP_REQ_TO,
                SUM(p.USERPRIMARYQUANTITY) AS QTY_TOTAL
            FROM
                PRODUCTIONDEMAND p
            LEFT JOIN ADSTORAGE a ON a.UNIQUEID = p.ABSUNIQUEID AND a.FIELDNAME = 'RMPReqDate'
            LEFT JOIN ADSTORAGE a2 ON a2.UNIQUEID = p.ABSUNIQUEID AND a2.FIELDNAME = 'RMPGreigeReqDateTo'
            LEFT JOIN ADSTORAGE a3 ON a3.UNIQUEID = p.ABSUNIQUEID AND a3.FIELDNAME = 'OriginalPDCode'
            WHERE
                p.ITEMTYPEAFICODE = 'KGF'
                AND a2.VALUEDATE > '2025-05-26'
                AND a3.VALUESTRING IS NULL
            GROUP BY
                p.SUBCODE02,
                p.SUBCODE03,
                p.SUBCODE04,
                a2.VALUEDATE
        ");

        $dataStock = DB::connection('DB2')->select("
            SELECT
                DECOSUBCODE02,
                DECOSUBCODE03,
                DECOSUBCODE04,
                SUM(BASEPRIMARYQUANTITYUNIT) as Stock
            FROM
                BALANCE b
            WHERE
                b.LOGICALWAREHOUSECODE IN ('M021', 'M502')
            GROUP BY 
                DECOSUBCODE02,
                DECOSUBCODE03,
                DECOSUBCODE04
        ");

        $forecast = DB::connection('mysql')->select("
            SELECT
                t.item_subcode2,
                t.item_subcode3,
                t.item_subcode4,
                t.buy_month,
                SUM(t.qty_kg) AS total_qty_kg
            FROM tbl_upload_order t
            GROUP BY
                t.item_subcode2,
                t.item_subcode3,
                t.item_subcode4,
                t.buy_month
        ");

        $itemCodesMesin = [];
        foreach ($dataMesin as $mesin) {
            $parts = explode('-', $mesin->item_code);
            if (count($parts) == 2) {
                $itemCodesMesin[] = strtoupper(trim($parts[0])) . '-' . strtoupper(trim($parts[1]));
            }
        }

        $dataDB2Filtered = array_filter($dataDB2, function($db2) use ($itemCodesMesin) {
            $key = strtoupper(trim($db2->subcode02)) . '-' . strtoupper(trim($db2->subcode03)) . '-' . strtoupper(trim($db2->subcode04));
            return !in_array($key, $itemCodesMesin);
        });

        $dataDB2Filtered = array_values($dataDB2Filtered);

        return response()->json([
            'dataMesin' => $dataMesin,
            'dataDB2' => $dataDB2Filtered,
            'dataStock' => $dataStock,
            'forecast' => $forecast,
        ]);
    }

    public function index2(){

        $itemCode = DB::connection('sqlsrv')->select('EXEC sp_get_unique_item_codes');

        return view('newView', [
        'itemCode' => $itemCode,
        ]);
    }

    public function getScheduleByItemCode($item_code){
        list($Code1, $Code2, $Code3) = explode('-', $item_code);

        $schedules = DB::connection('sqlsrv')->select('EXEC sp_get_schedule_by_item_code ?', [$item_code]);

        $dataDB2 = DB::connection('DB2')->select("
            SELECT
            	a2.VALUEDATE AS RMP_REQ_TO,
            	SUM(p.USERPRIMARYQUANTITY) AS QTY_TOTAL
            FROM
            	PRODUCTIONDEMAND p
            LEFT JOIN ADSTORAGE a ON a.UNIQUEID = p.ABSUNIQUEID AND a.FIELDNAME = 'RMPReqDate'
            LEFT JOIN ADSTORAGE a2 ON a2.UNIQUEID = p.ABSUNIQUEID AND a2.FIELDNAME = 'RMPGreigeReqDateTo'
            LEFT JOIN ADSTORAGE a3 ON a3.UNIQUEID = p.ABSUNIQUEID AND a3.FIELDNAME = 'OriginalPDCode'
            WHERE
                p.SUBCODE02 = ?
                AND p.SUBCODE03 = ?
                AND p.SUBCODE04 = ?
                AND p.ITEMTYPEAFICODE = 'KGF'
                AND a2.VALUEDATE > CAST(CURRENT DATE AS DATE)
            	AND a3.VALUESTRING IS NULL
            GROUP BY
            	a2.VALUEDATE
        ", [$Code1, $Code2, $Code3]);

        $dataStock = DB::connection('DB2')->select("
            SELECT
            	SUM(BASEPRIMARYQUANTITYUNIT) as Stock
            FROM
            	BALANCE b
            WHERE
            	DECOSUBCODE02 = ?
            	AND b.DECOSUBCODE03 = ?
            	AND b.DECOSUBCODE04 = ?
            	AND b.LOGICALWAREHOUSECODE IN ('M021', 'M502')",
        [$Code1, $Code2, $Code3]);

        $forecast = DB::connection('mysql')->select("
            SELECT
              t.item_subcode2,
              t.item_subcode3,
              t.item_subcode4,
              t.buy_month,
              SUM(t.qty_kg) AS total_qty_kg
            FROM tbl_upload_order t
            WHERE 
              t.item_subcode2 = ? AND
              t.item_subcode3 = ? AND
              t.item_subcode4 = ?
            GROUP BY
              t.item_subcode2,
              t.item_subcode3,
              t.item_subcode4,
              t.buy_month
        ",[$Code1, $Code2, $Code3]);

        return response()->json([
            'schedules' => $schedules,
            'db2_data' => $dataDB2,
            'stock_data' => $dataStock,
            'forecast' => $forecast,
        ]);
    }

    
    public function searchForecast($item_code){
        list($Code1, $Code2, $Code3) = explode('-', $item_code);

        $dataDB2 = DB::connection('DB2')->select("
            SELECT
                a2.VALUEDATE AS RMP_REQ_TO,
                SUM(p.USERPRIMARYQUANTITY) AS QTY_TOTAL
            FROM
                PRODUCTIONDEMAND p
            LEFT JOIN ADSTORAGE a ON a.UNIQUEID = p.ABSUNIQUEID AND a.FIELDNAME = 'RMPReqDate'
            LEFT JOIN ADSTORAGE a2 ON a2.UNIQUEID = p.ABSUNIQUEID AND a2.FIELDNAME = 'RMPGreigeReqDateTo'
            LEFT JOIN ADSTORAGE a3 ON a3.UNIQUEID = p.ABSUNIQUEID AND a3.FIELDNAME = 'OriginalPDCode'
            WHERE
                p.SUBCODE02 = ? AND
                p.SUBCODE03 = ? AND
                p.SUBCODE04 = ? AND
                p.ITEMTYPEAFICODE = 'KGF' AND
                a2.VALUEDATE > CAST(CURRENT DATE AS DATE) AND
                a3.VALUESTRING IS NULL
            GROUP BY a2.VALUEDATE
        ", [$Code1, $Code2, $Code3]);

        $dataStock = DB::connection('DB2')->select("
            SELECT
                SUM(BASEPRIMARYQUANTITYUNIT) as Stock
            FROM BALANCE b
            WHERE
                DECOSUBCODE02 = ? AND
                b.DECOSUBCODE03 = ? AND
                b.DECOSUBCODE04 = ? AND
                b.LOGICALWAREHOUSECODE IN ('M021', 'M502')",
        [$Code1, $Code2, $Code3]);

        $forecast = DB::connection('mysql')->select("
            SELECT
                t.item_subcode2,
                t.item_subcode3,
                t.item_subcode4,
                t.buy_month,
                SUM(t.qty_kg) AS total_qty_kg
            FROM tbl_upload_order t
            WHERE 
                t.item_subcode2 = ? AND
                t.item_subcode3 = ? AND
                t.item_subcode4 = ?
            GROUP BY
                t.item_subcode2,
                t.item_subcode3,
                t.item_subcode4,
                t.buy_month
        ", [$Code1, $Code2, $Code3]);

        return response()->json([
            'db2_data' => $dataDB2,
            'stock_data' => $dataStock,
            'forecast' => $forecast,
        ]);
    }

    public function getStockDetail(Request $request){
        $itemCode = $request->input('item_code');
        $stock = $request->input('stock');

        list($Code1, $Code2, $Code3) = explode('-', $itemCode);

        $dataHeading = DB::connection('DB2')->select("
            SELECT
                COALESCE(p.ORIGDLVSALORDLINESALORDERCODE, '-') AS ORIGDLVSALORDLINESALORDERCODE,
                COALESCE(s.STATISTICALGROUPCODE, '-') AS STATISTICALGROUPCODE,
                COALESCE(b.LOGICALWAREHOUSECODE, '-') AS LOGICALWAREHOUSECODE,
                COALESCE(SUM(BASEPRIMARYQUANTITYUNIT), 0) AS TotalStock
            FROM
              BALANCE b
            LEFT JOIN PRODUCTIONDEMAND p ON p.CODE = b.LOTCODE
            LEFT JOIN SALESORDER s ON s.CODE = p.ORIGDLVSALORDLINESALORDERCODE
            WHERE
                b.DECOSUBCODE02 = ?
            	AND b.DECOSUBCODE03 = ?
                AND b.DECOSUBCODE04 = ?
              AND b.LOGICALWAREHOUSECODE IN ('M021', 'M502')
            GROUP BY
              p.ORIGDLVSALORDLINESALORDERCODE,
              b.LOGICALWAREHOUSECODE,
              s.STATISTICALGROUPCODE",
        [$Code1, $Code2, $Code3]);

        $dataDetail = DB::connection('DB2')->select("
            SELECT
                COALESCE(p.ORIGDLVSALORDLINESALORDERCODE, '-') AS ORIGDLVSALORDLINESALORDERCODE,
                COALESCE(b.LOTCODE, '-') AS LOTCODE,
                COALESCE(s.STATISTICALGROUPCODE, '-') AS STATISTICALGROUPCODE,
                COALESCE(p.EXTERNALREFERENCE, '-') AS EXTERNALREFERENCE,
                COALESCE(b.LOGICALWAREHOUSECODE, '-') AS LOGICALWAREHOUSECODE,
                ROUND(COALESCE(SUM(BASEPRIMARYQUANTITYUNIT), 0), 2) AS Stock
            FROM
              BALANCE b
            LEFT JOIN PRODUCTIONDEMAND p ON p.CODE = b.LOTCODE
            LEFT JOIN SALESORDER s ON s.CODE = p.ORIGDLVSALORDLINESALORDERCODE
            WHERE
                b.DECOSUBCODE02 = ?
            	AND b.DECOSUBCODE03 = ?
                AND b.DECOSUBCODE04 = ?
              AND b.LOGICALWAREHOUSECODE IN ('M021', 'M502')
            GROUP BY
              p.ORIGDLVSALORDLINESALORDERCODE,
              b.LOTCODE,
              s.STATISTICALGROUPCODE,
              p.EXTERNALREFERENCE,
              b.LOGICALWAREHOUSECODE",
        [$Code1, $Code2, $Code3]);

        $dataStock = DB::connection('DB2')->select("
            SELECT
                COALESCE(b.LOTCODE, '-') AS LOTCODE,
                COALESCE(p.ORIGDLVSALORDLINESALORDERCODE, '-') AS ORIGDLVSALORDLINESALORDERCODE,
                COALESCE(s.STATISTICALGROUPCODE, '-') AS STATISTICALGROUPCODE,
                COALESCE(p.EXTERNALREFERENCE, '-') AS EXTERNALREFERENCE,
                COALESCE(b.LOGICALWAREHOUSECODE, '-') AS LOGICALWAREHOUSECODE,
                COALESCE(SUM(BASEPRIMARYQUANTITYUNIT), 0) AS Stock
            FROM
            	BALANCE b
            LEFT JOIN PRODUCTIONDEMAND p ON p.CODE = b.LOTCODE 
            LEFT JOIN SALESORDER s ON s.CODE = p.ORIGDLVSALORDLINESALORDERCODE 
            WHERE
            	b.DECOSUBCODE02 = ?
            	AND b.DECOSUBCODE03 = ?
                AND b.DECOSUBCODE04 = ?
            	AND b.LOGICALWAREHOUSECODE IN ('M021', 'M502')
            GROUP BY
            	b.LOTCODE,
            	p.ORIGDLVSALORDLINESALORDERCODE,
            	s.STATISTICALGROUPCODE,
            	p.EXTERNALREFERENCE,
            	b.LOGICALWAREHOUSECODE",
        [$Code1, $Code2, $Code3]);

        return response()->json([
            'dataHeading' => $dataHeading,
            'dataDetail' => $dataDetail,
            'dataStock' => $dataStock
        ]);
    }

    // Untuk list item
    public function getItems()
    {
        $items = DB::connection('sqlsrv')->select('EXEC sp_get_unique_item_codes');
        return response()->json($items);
    }

    public function getItemDetail($itemCode)
    {
        $details = [
            ['mesin' => 'Mesin A1', 'tgl_mulai' => '2025-05-20', 'tgl_selesai' => '2025-05-25', 'status' => 'On Progress'],
            ['mesin' => 'Mesin B2', 'tgl_mulai' => '2025-05-26', 'tgl_selesai' => '2025-06-01', 'status' => 'Scheduled'],
            ['mesin' => 'Mesin C3', 'tgl_mulai' => '2025-06-02', 'tgl_selesai' => '2025-06-07', 'status' => 'Completed'],
        ];

        return response()->json($details);
    }

}
