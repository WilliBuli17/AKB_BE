<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator; //import library untuk validasi
use App\Order; //import modal Order
use Illuminate\Support\Facades\DB;
use App\DetailOrder; //import modal DetailOrder
use App\Reservasi; //import modal Reservasi

class OrderController extends Controller
{
    //method untuk mengambil semua data Order 1 (select)
    public function getOrderAndReservation($status)
    {
        if($status == 1){
            $order = Order::join('reservasis', 'orders.id_reservasi', '=', 'reservasis.id')
                        ->join('customers', 'reservasis.id_customer', '=', 'customers.id')
                        ->where('orders.status_order', '!=' ,'2')
                        ->get(['customers.nama_customer','reservasis.id_table', 'orders.*']); //mengambil semua data Order yang tersedia
        } else {
            $order = Order::join('transaksis', 'transaksis.id_order', '=', 'orders.id')
                        ->join('reservasis', 'orders.id_reservasi', '=', 'reservasis.id')
                        ->join('customers', 'reservasis.id_customer', '=', 'customers.id')
                        ->where('orders.status_order', '=' ,'2')
                        ->select('customers.nama_customer','reservasis.id_table', 'orders.*', 'transaksis.id as transaksisID')
                        ->get(); //mengambil semua data Order yang tersedia
        }

        $order->makeHidden(['created_at','updated_at']);

        if (count($order) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $order
            ], 200);
        } //return data semua Order dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Order kosong
    }
    //method untuk menambah 1 data Order baru(create)
    public function storeChild(Request $request)
    {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'total_menu_order' => 'nullable|numeric',
            'total_item_order' => 'nullable|numeric',
            'total_harga_order' => 'nullable|numeric', 
            'tanggal_order' => 'required|date_format:Y-m-d',
            'id_reservasi' => 'required|numeric',
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        $order = new Order();
        if (!is_null($request->email_customer)) {
            $order->total_menu_order   = $storeData['total_menu_order'];
        } 

        if (!is_null($request->email_customer)) {
            $order->total_item_order   = $storeData['total_item_order'];
        } 

        if (!is_null($request->email_customer)) {
            $order->total_harga_order  = $storeData['total_harga_order'];
        } 

        $order->tanggal_order          = $storeData['tanggal_order'];
        $order->id_reservasi           = $storeData['id_reservasi'];

        $reservasi = Reservasi::find($order->id_reservasi);
        $reservasi->status_reservasi = 1;

        //$order = Order::create($storeData);//menambah data Order baru
        if ($order->save() && $reservasi->save()) {
            return response([
                'message' => 'Add Order Succes',
                'id' => $order->id,
                'data' => $order,
            ], 200); 
        } //return data Order yang telah diedit dalam bentuk json
        return response([
            'message' => 'Update Order Failed',
            'data' => null,
        ], 200); //return message saat Order gagal diedit
    }

    //method untuk mengubah 1 data Order (update)
    public function storeParent(Request $request, $id_reservasi)
    {
        $order = Order::where('id_reservasi', $id_reservasi)
                        ->first(); //mencari data Order berdasarkan id_reservasi
        if (is_null($order)) {
            return $this->storeChild($request);
        } //return message saat data Order tidak ditemukan

        
        $reservasi = Reservasi::find($id_reservasi);
        $reservasi->status_reservasi = 1;

        if ($order->save() && $reservasi->save()) {
            return response([
                'message' => 'Update Order Success',
                'id' => $order->id,
                'data' => $order,
            ], 200);
        } //return data Order yang telah diedit dalam bentuk json
        
        return response([
            'message' => 'Update Order Failed',
            'data' => null,
        ], 200); //return message saat Order gagal diedit
    }

    //method untuk mengubah 1 data Order (update)
    public function updateStatusOrder($id, $statusOrder)
    {
        $order = Order::find($id); //mencari data Order berdasarkan id
        if (is_null($order)) {
            return response([
                'message' => 'Order Not Found',
                'data' => null
            ], 200);
        } //return message saat data Order tidak ditemukan

        if($order->status_order != 0){
            return response(['message' => ' Anda Sudah Mengakhisi Sesi Pemesanan Anda'], 200);
        }
        
        $order->status_order = $statusOrder;

        if($statusOrder == 1){    
            $checkNull = DetailOrder::where('detail_orders.id_order', $id)
                                    ->where('detail_orders.status_item_order', '==','0')
                                    ->first();
                                    
            if (!is_null($checkNull)) {
                return response(['message' => 'Anda Masih Memiliki Pesanan, Silahkan Kosongkan Atau Pesan Semuanya'], 200);  
            }
        }

        if ($order->save()) {
            return response([
                'message' => 'Update Order Success',
                'data' => $order,
            ], 200);
        } //return data Order yang telah diedit dalam bentuk json
        return response([
            'message' => 'Update Order Failed',
            'data' => null,
        ], 200); //return message saat Order gagal diedit*/
    }
}
