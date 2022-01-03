<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('getMenuAndroid/menu/{is_Deleted}/{tipe}', 'Api\MenuController@getCustomTipe');

Route::get('getOne/reservasi/{id}', 'Api\ReservasiController@getOne');

Route::post('store/order/{id_reservasi}', 'Api\OrderController@storeParent');
Route::put('updateStatusOrder/order/{id}/{statusOrder}', 'Api\OrderController@updateStatusOrder');

Route::post('update/detailOrder/{id_order}/{id_menu}/{status}', 'Api\DetailOrderController@update');
Route::get('getChartOrder/detailOrder/{id_order}', 'Api\DetailOrderController@getChartOrder');
Route::get('getOrderFix/detailOrder/{id_order}', 'Api\DetailOrderController@getOrderFix');
Route::put('updateStatusMore/detailOrder/{id_order}', 'Api\DetailOrderController@updateStatusMore');
Route::put('updateOrderFromDetail/detailOrder/{id_order}', 'Api\DetailOrderController@updateOrderFromDetail');
Route::delete('delete/detailOrder/{id}', 'Api\DetailOrderController@destroy');






// Route::get('getKartuCustom/kartu/{tipe_kartu}', 'Api\KartuController@getKartuCustom');
// Route::get('getDetailOrder/transaksi/{id}', 'Api\TransaksiController@getDetailOrder');
Route::get('laporanPendapatanBulanan/laporan/{tahun}', 'Api\LaporanController@laporanPendapatanBulanan');
Route::get('laporanPengeluaranBulanan/laporan/{tahun}', 'Api\LaporanController@laporanPengeluaranBulanan');
Route::get('laporanPendapatanTahunan/laporan/{tahunMulai}/{tahunSelesai}', 'Api\LaporanController@laporanPendapatanTahunan');
Route::get('laporanPengeluaranTahunan/laporan/{tahunMulai}/{tahunSelesai}', 'Api\LaporanController@laporanPengeluaranTahunan');
Route::get('laporanPenjualanItemMenu/laporan/{tahun}/{bulan}', 'Api\LaporanController@laporanPenjualanItemMenu');
Route::get('laporanStockCustom/laporan/{tanggalMulai}/{tanggalSelesai}', 'Api\LaporanController@laporanStockCustom');
Route::get('laporanStockCustomincoming/laporan/{tanggalMulai}/{tanggalSelesai}', 'Api\LaporanController@laporanStockCustomincoming');
Route::get('laporanStockCustomRemaining/laporan/{tanggalMulai}/{tanggalSelesai}', 'Api\LaporanController@laporanStockCustomRemaining');
Route::get('laporanStockCustomWaste/laporan/{tanggalMulai}/{tanggalSelesai}', 'Api\LaporanController@laporanStockCustomWaste');
Route::get('laporanStockBulanan/laporan/{idBahan}/{monthYear}', 'Api\LaporanController@laporanStockBulanan');
Route::get('laporanStockBulananincoming/laporan/{idBahan}/{monthYear}', 'Api\LaporanController@laporanStockBulananincoming');
Route::get('laporanStockBulananRemaining/laporan/{idBahan}/{monthYear}', 'Api\LaporanController@laporanStockBulananRemaining');
Route::get('laporanStockBulananWaste/laporan/{idBahan}/{monthYear}', 'Api\LaporanController@laporanStockBulananWaste');
Route::get('getDataTahun/laporan', 'Api\LaporanController@getDataTahun');



Route::post('login', 'Api\AuthController@login');

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', 'Api\AuthController@logout');
    Route::get('userAktif/{id}', 'Api\AuthController@show');

    Route::post('store/pegawai', 'Api\PegawaiController@store');
    Route::post('update/pegawai/{id}', 'Api\PegawaiController@update');
    Route::get('getAll/pegawai', 'Api\PegawaiController@getAll');
    Route::get('getCustom/pegawai/{status_pegawai}', 'Api\PegawaiController@getCustom');
    //Route::get('getOne/pegawai/{id}', 'Api\PegawaiController@getOne');
    Route::put('updateAccount/pegawai/{id}', 'Api\PegawaiController@updateAccount');
    Route::put('updateStatus/pegawai/{id}', 'Api\PegawaiController@updateStatus');

    Route::post('store/customer', 'Api\CustomerController@store');
    //Route::get('getAll/customer', 'Api\CustomerController@getAll');
    Route::get('getCustom/customer/{is_Deleted}', 'Api\CustomerController@getCustom');
    //Route::get('getOne/customer/{id}', 'Api\CustomerController@getOne');
    Route::put('update/customer/{id}', 'Api\CustomerController@update');
    Route::put('delete/customer/{id}', 'Api\CustomerController@delete');
    Route::put('restore/customer/{id}', 'Api\CustomerController@restore');
    //Route::delete('destroy/customer/{id}', 'Api\CustomerController@destroy');

    Route::post('store/table', 'Api\TableController@store');
    //Route::get('getAll/table', 'Api\TableController@getAll');
    Route::get('getCustomDelete/table/{is_Deleted}', 'Api\TableController@getCustomDelete');
    //Route::get('getCustomStatus/table/{status_table}', 'Api\TableController@getCustomStatus');
    Route::get('getMejaByReservasi/table/{tanggal_reservasi}/{sesi_reservasi}/{status_reservasi}/{is_Deleted}', 'Api\TableController@getMejaByReservasi');
    //Route::get('getOne/table/{id}', 'Api\TableController@getOne');
    Route::put('update/table/{id}', 'Api\TableController@update');
    Route::put('delete/table/{id}', 'Api\TableController@delete');
    Route::put('restore/table/{id}', 'Api\TableController@restore');
    //Route::delete('destroy/table/{id}', 'Api\TableController@destroy');
    
    Route::post('store/reservasi', 'Api\ReservasiController@store');    
    //Route::get('getAll/reservasi', 'Api\ReservasiController@getAll');
    Route::get('getCustomDelete/reservasi/{is_Deleted}', 'Api\ReservasiController@getCustomDelete');
    Route::get('getCustomStatus/reservasi/{status_reservasi}/{is_Deleted}', 'Api\ReservasiController@getCustomStatus');
    Route::get('getCustomByTanggalDanSesi/reservasi/{id}/{tanggal_reservasi}/{sesi_reservasi}/{is_Deleted}', 'Api\ReservasiController@getCustomByTanggalDanSesi');
    Route::put('update/reservasi/{id}', 'Api\ReservasiController@update');
    Route::put('updateStatus/reservasi/{id}/{status}', 'Api\ReservasiController@updateStatus');
    Route::put('delete/reservasi/{id}', 'Api\ReservasiController@delete');
    Route::put('restore/reservasi/{id}', 'Api\ReservasiController@restore');
    //Route::delete('destroy/reservasi/{id}', 'Api\ReservasiController@destroy');

    Route::post('store/bahan', 'Api\BahanController@store');
    // Route::get('getAll/bahan', 'Api\BahanController@getAll');
    Route::get('getCustomStatus/bahan/{is_Deleted}', 'Api\BahanController@getCustomStatus');
    Route::get('getCustomByJumlah/bahan/{is_Deleted}/{check}', 'Api\BahanController@getCustomByJumlah');
    Route::get('getOne/bahan/{id}', 'Api\BahanController@getOne');
    Route::put('update/bahan/{id}', 'Api\BahanController@update');
    Route::put('delete/bahan/{id}', 'Api\BahanController@delete');
    Route::put('restore/bahan/{id}', 'Api\BahanController@restore');
    //Route::delete('destroy/bahan/{id}', 'Api\BahanController@destroy');

    Route::post('store/menu', 'Api\MenuController@store');
    Route::post('update/menu/{id}', 'Api\MenuController@update');
    //Route::get('getAll/menu', 'Api\MenuController@getAll');
    Route::get('getCustomDelete/menu/{is_Deleted}', 'Api\MenuController@getCustomDelete');
    Route::get('getCustomStock/menu/{is_Deleted}/{check}', 'Api\MenuController@getCustomStock');
    //Route::get('getOne/menu/{id}', 'Api\MenuController@getOne');
    Route::put('delete/menu/{id}', 'Api\MenuController@delete');
    Route::put('restore/menu/{id}', 'Api\MenuController@restore');
    //Route::delete('destroy/menu/{id}', 'Api\MenuController@destroy');

    Route::get('getOrderFixForEmployee/detailOrder/{status_item_order}', 'Api\DetailOrderController@getOrderFixForEmployee');    
    Route::put('updateStatus/detailOrder/{id}/{status}', 'Api\DetailOrderController@updateStatus');

    Route::get('getOrderAndReservation/order/{status}', 'Api\OrderController@getOrderAndReservation');

    Route::post('store/incomingStock', 'Api\IncomingStockController@store');
    // Route::get('getAll/incomingStock', 'Api\IncomingStockController@getAll');
    Route::get('getCustomStatus/incomingStock/{is_Deleted}', 'Api\IncomingStockController@getCustomStatus');
    Route::get('getCustomByBahan/incomingStock/{is_Deleted}/{tanggal_stock}', 'Api\IncomingStockController@getCustomByBahan');
    Route::get('getHistorycal/incomingStock/{is_Deleted}/{tanggal_stock_satu}/{tanggal_stock_dua}', 'Api\IncomingStockController@getHistorycal');
    Route::get('getOne/incomingStock/{id}', 'Api\IncomingStockController@getOne');
    Route::put('update/incomingStock/{id}', 'Api\IncomingStockController@update');
    Route::put('delete/incomingStock/{id}', 'Api\IncomingStockController@delete');
    Route::put('restore/incomingStock/{id}', 'Api\IncomingStockController@restore');
    //Route::delete('destroy/incomingStock/{id}', 'Api\IncomingStockController@destroy');

    Route::post('store/remainingStock', 'Api\RemainingStockController@store');
    // Route::get('getAll/remainingStock', 'Api\RemainingStockController@getAll');
    Route::get('getCustomStatus/remainingStock/{is_Deleted}', 'Api\RemainingStockController@getCustomStatus');
    Route::get('getCustomByBahan/remainingStock/{is_Deleted}/{tanggal_stock}', 'Api\RemainingStockController@getCustomByBahan');
    Route::get('getOne/remainingStock/{id}', 'Api\RemainingStockController@getOne');
    Route::put('update/remainingStock/{id}', 'Api\RemainingStockController@update');
    Route::put('delete/remainingStock/{id}', 'Api\RemainingStockController@delete');
    Route::put('restore/remainingStock/{id}', 'Api\RemainingStockController@restore');
    //Route::delete('destroy/remainingStock/{id}', 'Api\RemainingStockController@destroy');

    Route::post('store/wasteStock', 'Api\WasteStockController@store');
    // Route::get('getAll/wasteStock', 'Api\WasteStockController@getAll');
    Route::get('getCustomStatus/wasteStock/{is_Deleted}', 'Api\WasteStockController@getCustomStatus');
    Route::get('getCustomByBahan/wasteStock/{is_Deleted}/{tanggal_stock}', 'Api\WasteStockController@getCustomByBahan');
    Route::get('getOne/wasteStock/{id}', 'Api\WasteStockController@getOne');
    Route::put('update/wasteStock/{id}', 'Api\WasteStockController@update');
    Route::put('delete/wasteStock/{id}', 'Api\WasteStockController@delete');
    Route::put('restore/wasteStock/{id}', 'Api\WasteStockController@restore');
    //Route::delete('destroy/wasteStock/{id}', 'Api\WasteStockController@destroy');

    
    Route::post('store/kartu', 'Api\KartuController@store');
    Route::get('getKartuCustom/kartu/{tipe_kartu}', 'Api\KartuController@getKartuCustom');

    Route::post('store/transaksi', 'Api\TransaksiController@store');
    Route::get('getOneReservasi/transaksi/{id}', 'Api\TransaksiController@getOneReservasi');
    Route::get('getOneOrder/transaksi/{id}', 'Api\TransaksiController@getOneOrder');
    Route::get('getDetailOrder/transaksi/{id}', 'Api\TransaksiController@getDetailOrder');
    
    //Route::get('laporanPendapatanBulanan/laporan/{tahun}', 'Api\LaporanController@laporanPendapatanBulanan');
});