<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator; //import library untuk validasi
use App\Transaksi; //import modal Transaksi
use Illuminate\Support\Facades\DB;
use App\Order; //import modal Order
use App\Reservasi; //import modal Reservasi

class TransaksiController extends Controller
{
    //method untuk menampilkan data Transaksi (read)
    public function getOneReservasi($id)
    {
        $transaksi = Transaksi::join('orders', 'transaksis.id_order', '=', 'orders.id')
                            ->join('reservasis', 'orders.id_reservasi', '=', 'reservasis.id')
                            ->join('customers', 'reservasis.id_customer', '=', 'customers.id')
                            ->join('users', 'reservasis.id_pegawai', '=', 'users.id')
                            ->where('transaksis.id', $id)
                            ->get(['transaksis.*', 'customers.nama_customer','reservasis.id_table', 'users.nama_pegawai']);

        if (count($transaksi) > 0) {
            return response([
                'message' => 'Retrive Success',
                'data' => $transaksi
            ], 200);
        } //return data semua Transaksi dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Transaksi kosong
    }

    //method untuk menampilkan data Transaksi (read)
    public function getOneOrder($id)
    {
        $transaksi = Transaksi::join('orders', 'transaksis.id_order', '=', 'orders.id')
                            ->join('users', 'transaksis.id_pegawai', '=', 'users.id')
                            ->where('transaksis.id', $id)
                            ->get(['transaksis.*', 'orders.total_menu_order','orders.total_item_order', 'users.nama_pegawai']);

        if (count($transaksi) > 0) {
            return response([
                'message' => 'Retrive Success',
                'data' => $transaksi
            ], 200);
        } //return data semua Transaksi dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Transaksi kosong
    }

    //method untuk menampilkan data Transaksi (read)
    public function getDetailOrder($id)
    {
        $transaksi = Transaksi::join('orders', 'transaksis.id_order', '=', 'orders.id')
                                ->join('detail_orders', 'detail_orders.id_order', '=', 'orders.id')
                                ->join('menus', 'detail_orders.id_menu', '=', 'menus.id')
                                ->groupBy('menus.nama_menu')
                                ->where('transaksis.id', $id)
                                ->select('menus.nama_menu',
                                     DB::raw('SUM(detail_orders.jumlah_item_order) AS jumlah_item_order'),
                                     DB::raw('AVG(detail_orders.harga_item_order) AS harga_item_order'),
                                     DB::raw('(SUM(detail_orders.jumlah_item_order) * AVG(detail_orders.harga_item_order)) AS SubTotal'))->get();

        if (count($transaksi) > 0) {
            return response([
                'message' => 'Retrive Success',
                'data' => $transaksi
            ], 200);
        } //return data semua Transaksi dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Transaksi kosong
    }

    ///method untuk menambah 1 data Transaksi baru(create)
    public function store(Request $request)
    {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'subtotal_harga' => 'required',
            'biaya_pajak' => 'required',
            'biaya_service' => 'required',
            'total_harga' => 'required',
            'tipe_pembayaran' => 'required',
            'tanggal_transaksi' => 'required|date_format:Y-m-d',
            'kode_resi' => 'required',
            'id_kartu' => 'nullable',
            'kode_verivikasi_edc' => 'nullable',
            'id_order' => 'required',
            'id_pegawai' => 'required',
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        $getNomorTransaksi = Transaksi::where('tanggal_transaksi', $storeData['tanggal_transaksi'])
                                        ->max('nomor_resi');

        $transaksi = new Transaksi();
        $transaksi->subtotal_harga           = $storeData['subtotal_harga'];
        $transaksi->biaya_pajak              = $storeData['biaya_pajak'];
        $transaksi->biaya_service            = $storeData['biaya_service'];
        $transaksi->total_harga              = $storeData['total_harga'];
        $transaksi->tipe_pembayaran          = $storeData['tipe_pembayaran'];
        $transaksi->tanggal_transaksi        = $storeData['tanggal_transaksi'];
        if(!is_null($getNomorTransaksi)){
            $transaksi->nomor_resi           = $getNomorTransaksi + 1;
        } else {
            $transaksi->nomor_resi           = 1;
        }
        $transaksi->kode_resi                = $storeData['kode_resi'] . $transaksi->nomor_resi;
        $transaksi->id_order                 = $storeData['id_order'];
        $transaksi->id_pegawai               = $storeData['id_pegawai'];
        if (!is_null($request->id_kartu) && !is_null($request->kode_verivikasi_edc)) {
            $transaksi->id_kartu             = $storeData['id_kartu'];
            $transaksi->kode_verivikasi_edc  = $storeData['kode_verivikasi_edc'];
        } else {
            $transaksi->id_kartu             = null;
            $transaksi->kode_verivikasi_edc  = null;
        }

        $order = Order::find($transaksi->id_order );
        $order->status_order = 2;
        
        $reservasi = Reservasi::find($order->id_reservasi);
        $reservasi->status_reservasi = 2;

        //$transaksi = Transaksi::create($storeData);//menambah data Transaksi baru
        if ($transaksi->save() && $order->save() && $reservasi->save()) {
            return response([
                'message' => 'Add Transaksi Succes',
                'data' => $transaksi,
            ], 200); 
        } //return data Transaksi baru dalam bentuk json
        return response([
            'message' => 'Add Transaksi Failed',
            'data' => null
        ], 400); //return message saat Transaksi gagal diedit

        
    }
}
