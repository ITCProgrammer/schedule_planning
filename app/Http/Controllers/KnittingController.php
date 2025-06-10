<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KnittingController extends Controller
{
     public function index()
    {
        // return view('welcome');
    }

    public function dataGet() {
        $dataMesin = DB::connection('sqlsrv')->select('EXEC sp_get_shedule');   

        $dataDB2 = DB::connection('DB2')->select("
            SELECT
                TRIM(p.SUBCODE02) AS SUBCODE02,
                TRIM(p.SUBCODE03) AS SUBCODE03,
                TRIM(p.SUBCODE04) AS SUBCODE04,
                TRIM(p.SUBCODE02) || '-' || TRIM(p.SUBCODE03) || '-' || TRIM(p.SUBCODE04) AS item_code,
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
                AND p.PROGRESSSTATUS != '6'
            GROUP BY
                p.SUBCODE02,
                p.SUBCODE03,
                p.SUBCODE04,
                a2.VALUEDATE
        "); 

        $forecast = DB::connection('mysql')->select("
            SELECT
                t.item_subcode2,
                t.item_subcode3,
                t.item_subcode4,
                CONCAT(t.item_subcode2, '-', t.item_subcode3, '-', t.item_subcode4) AS item_code,
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

        $forecastFiltered = array_filter($forecast, function($item) use ($itemCodesMesin) {
            $key = strtoupper(trim($item->item_subcode2)) . '-' . strtoupper(trim($item->item_subcode3)) . '-' . strtoupper(trim($item->item_subcode4));
            return !in_array($key, $itemCodesMesin);
        });
        $forecastFiltered = array_values($forecastFiltered);    

        return response()->json([
            'dataMesin' => $dataMesin,
            'dataDB2' => $dataDB2Filtered,
            'forecast' => $forecastFiltered,
        ]);
    }

}
