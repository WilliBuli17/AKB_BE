<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator; //import library untuk validasi
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Else_;

class LaporanController extends Controller
{
    //method untuk mengambil DATA TAHUN
    public function getDataTahun(){
        $laporan =  DB::select("SELECT YEAR(incoming_stocks.tanggal_stock) AS TAHUN
                                FROM incoming_stocks
                                WHERE incoming_stocks.is_Deleted = 0
                                GROUP BY TAHUN
                                UNION
                                SELECT YEAR(orders.tanggal_order) AS TAHUN
                                FROM orders
                                WHERE orders.status_order=2
                                GROUP BY TAHUN");

        if (count($laporan) > 0) {
            return response([
                'message' => 'Retrive Success',
                'data' => $laporan
            ], 200); //return message data DATA TAHUN
        }

        return response([
            'message' => 'Data Kosong',
            'data' => null
        ], 404); //return message data DATA TAHUN
    }
    
    //method untuk mengambil Laporan Pendapatan Bulanan
    public function laporanPendapatanBulanan($tahun){
        $laporan =  DB::select("SELECT m.month AS Bulan,
                                COALESCE(SUM(IF(tipe_menu='Makanan Utama', (jumlah_item_order * harga_item_order), 0)), 0) AS Makanan,
                                COALESCE(SUM(IF(tipe_menu='Side Dish', (jumlah_item_order * harga_item_order), 0)), 0) AS Side,
                                COALESCE(SUM(IF(tipe_menu='Minuman', (jumlah_item_order * harga_item_order), 0)), 0) AS Minuman,
                                SUM(jumlah_item_order * harga_item_order) AS Total
                                FROM detail_orders INNER JOIN orders ON (detail_orders.id_order  = orders.id)
                                INNER JOIN menus ON (detail_orders.id_menu = menus.id)
                                RIGHT JOIN (SELECT 'January'   AS month, 1  AS Bln UNION 
                                            SELECT 'February'  AS month, 2  AS Bln UNION 
                                            SELECT 'March'     AS month, 3  AS Bln UNION 
                                            SELECT 'April'     AS month, 4  AS Bln UNION 
                                            SELECT 'May'       AS month, 5  AS Bln UNION 
                                            SELECT 'June'      AS month, 6  AS Bln UNION 
                                            SELECT 'July'      AS month, 7  AS Bln UNION 
                                            SELECT 'August'    AS month, 8  AS Bln UNION 
                                            SELECT 'September' AS month, 9  AS Bln UNION 
                                            SELECT 'October'   AS month, 10 AS Bln UNION 
                                            SELECT 'November'  AS month, 11 AS Bln UNION 
                                            SELECT 'December'  AS month, 12 AS Bln) 
                                AS m ON monthname(tanggal_order) = m.month 
                                WHERE YEAR(tanggal_order) = '$tahun' 
                                AND status_order LIKE '2'
                                GROUP BY m.month ORDER BY m.Bln");

        if (count($laporan) > 0) {
            return response([
                'message' => 'Retrive Success',
                'data' => $laporan
            ], 200); //return message data Laporan Pendapatan Bulanan
        }

        return response([
            'message' => 'Data Kosong',
            'data' => null
        ], 404); //return message data Laporan Pendapatan Bulanan
    }

    //method untuk mengambil Laporan Pendapatan Bulanan
    public function laporanPengeluaranBulanan($tahun){
        $laporan =  DB::select("SELECT m.month AS Bulan,
                                COALESCE(SUM(IF(tipe_menu='Makanan Utama', (harga_stock), 0)), 0) AS Makanan,
                                COALESCE(SUM(IF(tipe_menu='Side Dish', (harga_stock), 0)), 0) AS Side,
                                COALESCE(SUM(IF(tipe_menu='Minuman', (harga_stock), 0)), 0) AS Minuman,
                                SUM(harga_stock) AS Total
                                FROM incoming_stocks INNER JOIN bahans ON (incoming_stocks.id_bahan  = bahans.id)
                                INNER JOIN menus ON (menus.id_bahan = bahans.id)
                                RIGHT JOIN (SELECT 'January'   AS month, 1  AS Bln UNION 
                                            SELECT 'February'  AS month, 2  AS Bln UNION 
                                            SELECT 'March'     AS month, 3  AS Bln UNION 
                                            SELECT 'April'     AS month, 4  AS Bln UNION 
                                            SELECT 'May'       AS month, 5  AS Bln UNION 
                                            SELECT 'June'      AS month, 6  AS Bln UNION 
                                            SELECT 'July'      AS month, 7  AS Bln UNION 
                                            SELECT 'August'    AS month, 8  AS Bln UNION 
                                            SELECT 'September' AS month, 9  AS Bln UNION 
                                            SELECT 'October'   AS month, 10 AS Bln UNION 
                                            SELECT 'November'  AS month, 11 AS Bln UNION 
                                            SELECT 'December'  AS month, 12 AS Bln) 
                                AS m ON monthname(tanggal_stock) = m.month 
                                WHERE YEAR(tanggal_stock) = '$tahun' 
                                AND incoming_stocks.is_Deleted = 0
                                GROUP BY m.month ORDER BY m.Bln");

        if (count($laporan) > 0) {
            return response([
                'message' => 'Retrive Success',
                'data' => $laporan
            ], 200); //return message data Laporan Pendapatan Bulanan
        }

        return response([
            'message' => 'Data Kosong',
            'data' => null
        ], 404); //return message data Laporan Pendapatan Bulanan
    }

    //method untuk mengambil Laporan Pendapatan Tahunan
    public function laporanPendapatanTahunan($tahunMulai, $tahunSelesai){
        $laporan =  DB::select("SELECT year(orders.tanggal_order) as Tahun, 
                                COALESCE(SUM(IF(Tipe_Menu = 'Makanan Utama' AND Status_Order = '2', (Jumlah_Item_Order * Harga_Item_Order), 0)), 0) AS Makanan, 
                                COALESCE(SUM(IF(Tipe_Menu='Side Dish' AND Status_Order = '2', (Jumlah_Item_Order * Harga_Item_Order), 0)), 0) AS Side, 
                                COALESCE(SUM(IF(Tipe_Menu='Minuman' AND Status_Order = '2', (Jumlah_Item_Order * Harga_Item_Order), 0)), 0) AS Minuman, 
                                COALESCE(SUM(IF(Status_Order = '2', (Jumlah_Item_Order * Harga_Item_Order), 0)), 0) AS Total 
                                FROM detail_orders INNER JOIN orders ON (detail_orders.id_order = orders.id) 
                                INNER JOIN menus ON (detail_orders.id_menu = menus.id) 
                                WHERE year(orders.tanggal_order) BETWEEN '$tahunMulai' AND '$tahunSelesai' 
                                GROUP BY year(orders.tanggal_order)");

        if (count($laporan) > 0) {
            return response([
                'message' => 'Retrive Success',
                'data' => $laporan
            ], 200); //return message data Laporan Pendapatan Bulanan
        }

        return response([
            'message' => 'Data Kosong',
            'data' => null
        ], 404); //return message data Laporan Pendapatan Bulanan
    }
    
    //method untuk mengambil Laporan Pendapatan Tahunan
    public function laporanPengeluaranTahunan($tahunMulai, $tahunSelesai){
        $laporan =  DB::select("SELECT year(incoming_stocks.tanggal_stock) as Tahun, 
                                COALESCE(SUM(IF(tipe_menu='Makanan Utama', (harga_stock), 0)), 0) AS Makanan,
                                COALESCE(SUM(IF(tipe_menu='Side Dish', (harga_stock), 0)), 0) AS Side,
                                COALESCE(SUM(IF(tipe_menu='Minuman', (harga_stock), 0)), 0) AS Minuman,
                                SUM(harga_stock) AS Total
                                FROM incoming_stocks INNER JOIN bahans ON (incoming_stocks.id_bahan  = bahans.id)
                                INNER JOIN menus ON (menus.id_bahan = bahans.id)
                                WHERE year(incoming_stocks.tanggal_stock) BETWEEN '$tahunMulai' AND '$tahunSelesai' 
                                AND incoming_stocks.is_Deleted = 0
                                GROUP BY year(incoming_stocks.tanggal_stock)");

        if (count($laporan) > 0) {
            return response([
                'message' => 'Retrive Success',
                'data' => $laporan
            ], 200); //return message data Laporan Pendapatan Bulanan
        }

        return response([
            'message' => 'Data Kosong',
            'data' => null
        ], 404); //return message data Laporan Pendapatan Bulanan
    }
    
    //method untuk mengambil Laporan Pendapatan Tahunan
    public function laporanPenjualanItemMenu($tahun, $bulan){
        if($bulan == 13){
            $laporan =  DB::select("SELECT menus.nama_menu, menus.tipe_menu, menus.satuan_menu,
                                    COALESCE(MAX(detail_orders.jumlah_item_order), 0) as penjualan_tertinggi,
                                    COALESCE(SUM(detail_orders.jumlah_item_order), 0) as total
                                    FROM menus INNER JOIN detail_orders ON (menus.id = detail_orders.id_menu)
                                    INNER JOIN orders ON (orders.id  = detail_orders.id_order)
                                    WHERE YEAR(orders.tanggal_order) = '$tahun'
                                    AND orders.status_order = 2
                                    GROUP BY menus.nama_menu, menus.tipe_menu, menus.satuan_menu ORDER BY detail_orders.id_menu");
        } 
        else {
            $laporan =  DB::select("SELECT menus.nama_menu, menus.tipe_menu, menus.satuan_menu,
                                    COALESCE(MAX(detail_orders.jumlah_item_order), 0) as penjualan_tertinggi,
                                    COALESCE(SUM(detail_orders.jumlah_item_order), 0) as total
                                    FROM menus INNER JOIN detail_orders ON (menus.id = detail_orders.id_menu)
                                    INNER JOIN orders ON (orders.id  = detail_orders.id_order)
                                    WHERE month(orders.tanggal_order) = '$bulan'
                                    AND YEAR(orders.tanggal_order) = '$tahun'
                                    AND orders.status_order = 2
                                    GROUP BY menus.nama_menu, menus.tipe_menu, menus.satuan_menu ORDER BY detail_orders.id_menu");
        }
            

        if (count($laporan) > 0) {
            return response([
                'message' => 'Retrive Success',
                'data' => $laporan
            ], 200); //return message data Laporan Pendapatan Bulanan
        }

        return response([
            'message' => 'Data Kosong',
            'data' => null
        ], 404); //return message data Laporan Pendapatan Bulanan
    }
    
    //method untuk mengambil Laporan Stock Custom
    public function laporanStockCustom($tanggalMulai, $tanggalSelesai){
        $laporan =  DB::select("SELECT bahans.nama_bahan, bahans.id, bahans.satuan_bahan,
                                        NULL AS Incoming_Stok, 
                                        NULL AS Remaining_Stok, 
                                        NULL AS Waste_Stok
                                FROM bahans
                                WHERE bahans.id IN (SELECT incoming_stocks.id_bahan FROM incoming_stocks
                                                    WHERE incoming_stocks.tanggal_stock BETWEEN '$tanggalMulai' AND '$tanggalSelesai
                                                    AND incoming_stocks.is_Deleted = 0')
                                OR bahans.id IN (SELECT remaining_stocks.id_bahan FROM remaining_stocks
                                                WHERE remaining_stocks.tanggal_stock BETWEEN '$tanggalMulai' AND '$tanggalSelesai
                                                AND remaining_stocks.is_Deleted = 0')
                                OR bahans.id IN (SELECT waste_stocks.id_bahan FROM waste_stocks
                                                WHERE waste_stocks.tanggal_stock BETWEEN '$tanggalMulai' AND '$tanggalSelesai
                                                AND waste_stocks.is_Deleted = 0')");

        if (count($laporan) > 0) {
            return response([
                'message' => 'Retrive Success',
                'data' => $laporan
            ], 200); //return message data Laporan Stock Custom
        }

        return response([
            'message' => 'Data Kosong',
            'data' => null
        ], 404); //return message data Laporan Stock Custom
    }
    
    //method untuk mengambil Laporan Stock Custom
    public function laporanStockCustomincoming($tanggalMulai, $tanggalSelesai){
        $laporan =  DB::select("SELECT incoming_stocks.id_bahan, SUM(incoming_stocks.jumlah_stock) as jumlah_stock
                                FROM incoming_stocks 
                                WHERE incoming_stocks.tanggal_stock BETWEEN '$tanggalMulai' and '$tanggalSelesai'
                                AND incoming_stocks.is_Deleted = 0
                                GROUP BY incoming_stocks.id_bahan");

        if (count($laporan) > 0) {
            return response([
                'message' => 'Retrive Success',
                'data' => $laporan
            ], 200); //return message data Laporan Stock Custom
        }

        return response([
            'message' => 'Data Kosong',
            'data' => null
        ], 404); //return message data Laporan Stock Custom
    }
    
    //method untuk mengambil Laporan Stock Custom
    public function laporanStockCustomRemaining($tanggalMulai, $tanggalSelesai){
        $laporan =  DB::select("SELECT remaining_stocks.id_bahan, SUM(remaining_stocks.jumlah_stock) as jumlah_stock 
                                FROM remaining_stocks 
                                WHERE remaining_stocks.tanggal_stock BETWEEN '$tanggalMulai' and '$tanggalSelesai'
                                AND remaining_stocks.is_Deleted = 0
                                GROUP BY remaining_stocks.id_bahan");

        if (count($laporan) > 0) {
            return response([
                'message' => 'Retrive Success',
                'data' => $laporan
            ], 200); //return message data Laporan Stock Custom
        }

        return response([
            'message' => 'Data Kosong',
            'data' => null
        ], 404); //return message data Laporan Stock Custom
    }
    
    //method untuk mengambil Laporan Stock Custom
    public function laporanStockCustomWaste($tanggalMulai, $tanggalSelesai){
        $laporan =  DB::select("SELECT waste_stocks.id_bahan, SUM(waste_stocks.jumlah_stock) as jumlah_stock 
                                FROM waste_stocks 
                                WHERE waste_stocks.tanggal_stock BETWEEN '$tanggalMulai' and '$tanggalSelesai'
                                AND waste_stocks.is_Deleted = 0
                                GROUP BY waste_stocks.id_bahan");

        if (count($laporan) > 0) {
            return response([
                'message' => 'Retrive Success',
                'data' => $laporan
            ], 200); //return message data Laporan Stock Custom
        }

        return response([
            'message' => 'Data Kosong',
            'data' => null
        ], 404); //return message data Laporan Stock Custom
    }
    
    //method untuk mengambil Laporan Stock Custom
    public function laporanStockBulanan($idBahan, $monthYear){
        $laporan =  DB::select("SELECT incoming_stocks.tanggal_stock as tanggal,
                                        NULL AS Incoming_Stok,
                                        NULL AS Remaining_Stok,
                                        NULL AS Waste_Stok
                                FROM incoming_stocks 
                                WHERE incoming_stocks.id_bahan = '$idBahan'
                                AND month(incoming_stocks.tanggal_stock) = month('$monthYear')  
                                AND YEAR(incoming_stocks.tanggal_stock) = YEAR('$monthYear') 
                                AND incoming_stocks.is_Deleted = 0
                                GROUP BY tanggal
                                UNION
                                SELECT remaining_stocks.tanggal_stock as tanggal,
                                        NULL AS Incoming_Stok,
                                        NULL AS Remaining_Stok,
                                        NULL AS Waste_Stok
                                FROM remaining_stocks 
                                WHERE remaining_stocks.id_bahan = '$idBahan'
                                AND month(remaining_stocks.tanggal_stock) = month('$monthYear')  
                                AND YEAR(remaining_stocks.tanggal_stock) = YEAR('$monthYear') 
                                AND remaining_stocks.is_Deleted = 0
                                GROUP BY tanggal
                                UNION
                                SELECT waste_stocks.tanggal_stock as tanggal,
                                        NULL AS Incoming_Stok,
                                        NULL AS Remaining_Stok,
                                        NULL AS Waste_Stok
                                FROM waste_stocks 
                                WHERE waste_stocks.id_bahan = '$idBahan'
                                AND month(waste_stocks.tanggal_stock) = month('$monthYear') 
                                AND YEAR(waste_stocks.tanggal_stock) = YEAR('$monthYear') 
                                AND waste_stocks.is_Deleted = 0
                                GROUP BY tanggal");

        if (count($laporan) > 0) {
            return response([
                'message' => 'Retrive Success',
                'data' => $laporan
            ], 200); //return message data Laporan Stock Custom
        }

        return response([
            'message' => 'Data Kosong',
            'data' => null
        ], 404); //return message data Laporan Stock Custom
    }

    //method untuk mengambil Laporan Stock Custom
    public function laporanStockBulananincoming($idBahan, $monthYear){
        $laporan =  DB::select("SELECT incoming_stocks.tanggal_stock as tanggal, SUM(incoming_stocks.jumlah_stock) AS Incoming_Stok
                                FROM incoming_stocks
                                WHERE incoming_stocks.id_bahan = '$idBahan'
                                AND month(incoming_stocks.tanggal_stock) = month('$monthYear')  
                                AND YEAR(incoming_stocks.tanggal_stock) = YEAR('$monthYear') 
                                AND incoming_stocks.is_Deleted = 0
                                GROUP BY tanggal");

        if (count($laporan) > 0) {
            return response([
                'message' => 'Retrive Success',
                'data' => $laporan
            ], 200); //return message data Laporan Stock Custom
        }

        return response([
            'message' => 'Data Kosong',
            'data' => null
        ], 404); //return message data Laporan Stock Custom
    }
    
    //method untuk mengambil Laporan Stock Custom
    public function laporanStockBulananRemaining($idBahan, $monthYear){
        $laporan =  DB::select("SELECT remaining_stocks.tanggal_stock as tanggal, SUM(remaining_stocks.jumlah_stock) AS Remaining_Stok
                                FROM remaining_stocks
                                WHERE remaining_stocks.id_bahan = '$idBahan'
                                AND month(remaining_stocks.tanggal_stock) = month('$monthYear')  
                                AND YEAR(remaining_stocks.tanggal_stock) = YEAR('$monthYear') 
                                AND remaining_stocks.is_Deleted = 0
                                GROUP BY tanggal");

        if (count($laporan) > 0) {
            return response([
                'message' => 'Retrive Success',
                'data' => $laporan
            ], 200); //return message data Laporan Stock Custom
        }

        return response([
            'message' => 'Data Kosong',
            'data' => null
        ], 404); //return message data Laporan Stock Custom
    }
    
    //method untuk mengambil Laporan Stock Custom
    public function laporanStockBulananWaste($idBahan, $monthYear){
        $laporan =  DB::select("SELECT waste_stocks.tanggal_stock as tanggal, SUM(waste_stocks.jumlah_stock) AS Waste_Stok
                                FROM waste_stocks
                                WHERE waste_stocks.id_bahan = '$idBahan'
                                AND month(waste_stocks.tanggal_stock) = month('$monthYear') 
                                AND YEAR(waste_stocks.tanggal_stock) = YEAR('$monthYear') 
                                AND waste_stocks.is_Deleted = 0
                                GROUP BY tanggal");

        if (count($laporan) > 0) {
            return response([
                'message' => 'Retrive Success',
                'data' => $laporan
            ], 200); //return message data Laporan Stock Custom
        }

        return response([
            'message' => 'Data Kosong',
            'data' => null
        ], 404); //return message data Laporan Stock Custom
    }
}
