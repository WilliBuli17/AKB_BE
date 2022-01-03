<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator; //import library untuk validasi
use Illuminate\Support\Facades\DB;
use App\DetailOrder; //import modal DetailOrder
use App\Order; //import modal DetailOrder
use App\Menu; //import modal Menu
use App\Bahan; //import modal Menu

use function PHPUnit\Framework\isNull;

class DetailOrderController extends Controller
{
    //method untuk menampilkan 1 data DetailOrder 
    public function getChartOrder($id_order)
    {
        $checkNull = DetailOrder::where('detail_orders.id_order', $id_order)
                                ->where('detail_orders.status_item_order', '==','0')
                                ->first(); 
                                
        $detailOrder = DetailOrder::join('menus', 'detail_orders.id_menu', '=', 'menus.id')
                                ->where('detail_orders.id_order', $id_order)
                                ->where('detail_orders.status_item_order', '==','0')
                                ->get(['menus.nama_menu', 'menus.foto_menu', 'menus.jumlah_menu_tersedia','detail_orders.*']); 

        $detailOrder->makeHidden(['created_at','updated_at']);                        

        if (!is_null($checkNull)) {
            return response([
                'message' => 'Mengambil Daftar Chart Success',
                'data' => $detailOrder
            ], 200);
        } //return data semua DetailOrder dalam bentuk json

        return response([
            'message' => 'Daftar Chart Kosong',
            'data' => null
        ], 200); //return message saat data DetailOrder tidak ditemukan
    }

    //method untuk menampilkan 1 data DetailOrder 
    public function getOrderFix($id_order)
    {

        $id = $id_order;
        $order = Order::find($id); //mencari data Order berdasarkan id
        if (is_null($order)) {
            return response([
                'message' => 'Order Not Found',
                'data' => null
            ], 200);
        } //return message saat data Order tidak ditemukan

        
        $checkNull = DetailOrder::where('detail_orders.id_order', $id_order)
                                ->where('detail_orders.status_item_order', '!=','0')
                                ->first();
        
        $detailOrder = DetailOrder::join('menus', 'detail_orders.id_menu', '=', 'menus.id')
                                ->where('detail_orders.id_order', $id_order)
                                ->where('detail_orders.status_item_order', '!=','0')
                                ->orderBy('detail_orders.id')
                                ->get(['menus.nama_menu', 'menus.foto_menu','detail_orders.*']); 

        $detailOrder->makeHidden(['created_at','updated_at']);                        

        if (!is_null($checkNull)) {
            return response([
                'message' => 'Mengambil Daftar Pesanan Success',
                'total_harga_order' =>  $order->total_harga_order,
                'data' => $detailOrder
            ], 200);
        } //return data semua DetailOrder dalam bentuk json

        return response([
            'message' => 'Daftar Pesanan Kosong',
            'data' => null
        ], 200); //return message saat data DetailOrder tidak ditemukan
    }

    //method untuk menampilkan 1 data DetailOrder 
    public function getOrderFixForEmployee($status_item_order)
    {
        $detailOrder = DetailOrder::join('menus', 'detail_orders.id_menu', '=', 'menus.id')
                                    ->join('orders', 'detail_orders.id_order', '=', 'orders.id')
                                    ->join('reservasis', 'orders.id_reservasi', '=', 'reservasis.id')
                                    ->join('customers', 'reservasis.id_customer', '=', 'customers.id')
                                    ->where('detail_orders.status_item_order', $status_item_order)
                                    ->where('orders.status_order', '!=' ,'2')
                                    ->orderBy('detail_orders.id')
                                    ->orderBy('reservasis.id_table')
                                    ->get(['menus.nama_menu', 'menus.foto_menu','detail_orders.*', 'reservasis.id_table','customers.nama_customer',]); 

        $detailOrder->makeHidden(['created_at','updated_at']);                        

        if (count($detailOrder) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $detailOrder
            ], 200);
        } //return data semua DetailOrder dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Menu kosong
    }

    //method untuk menambah 1 data DetailOrder baru(create)
    public function store(Request $request)
    {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'jumlah_item_order' => 'required|numeric',
            'harga_item_order' => 'required|numeric',
            'id_order' => 'required|numeric',
            'id_menu' => 'required|numeric',
        ]); //membuat rule validasi input
    
        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 200); //return error invalid input
        }
    
        $menu = Menu::find($storeData['id_menu']); //mencari data Menu berdasarkan id

        $queryCoba = DetailOrder::join('menus', 'detail_orders.id_menu', '=', 'menus.id')
                                ->groupBy('menus.id_bahan')
                                ->where('detail_orders.id_order', $storeData['id_order'])
                                ->where('menus.id_bahan', $menu->id_bahan)
                                ->where('detail_orders.status_item_order', '=','0'); 
        $totalItemOrder = (int)$queryCoba->sum('jumlah_item_order');

        $bahan          = Bahan::find($menu->id_bahan);
        
        if (floor($bahan->jumlah_bahan / $bahan->ukuran_porsi) < ($totalItemOrder + $storeData['jumlah_item_order'])) {
            return response(['message' => 'Jumlah Item Menu Pesanan Melebihi Stok Tersedia'], 200);
            //return message saat data Menu tidak valid
        } 
        else {
            $detailOrder = new DetailOrder();
            $detailOrder->jumlah_item_order    = $storeData['jumlah_item_order'];
            $detailOrder->harga_item_order     = $storeData['harga_item_order'];
            $detailOrder->id_order             = $storeData['id_order'];
            $detailOrder->id_menu              = $storeData['id_menu'];
            $menu->jumlah_menu_tersedia        = $menu->jumlah_menu_tersedia - $storeData['jumlah_item_order'];

            $updateMenu = Menu::where('id_bahan', $bahan->id);
    
            //$order = DetailOrder::create($storeData);//menambah data DetailOrder baru
            if($detailOrder->save() && $updateMenu->update(['menus.jumlah_menu_tersedia' => $menu->jumlah_menu_tersedia])){
                return response([
                    'message' => 'Menambah Daftar Chart Success',
                    'data' => $detailOrder,
                ], 200); //return data DetailOrder baru dalam bentuk json
            }
        }
        return response([
            'message' => 'Menambah Daftar Chart Failed',
            'data' => null,
        ], 200); //return data DetailOrder baru dalam bentuk json
    }

    //method untuk mengubah 1 data DetailOrder (update)
    public function update(Request $request, $id_order, $id_menu, $status)
    {
        $id = $id_order;
        $order = Order::find($id); //mencari data Order berdasarkan id
        if ($order->status_order != 0) {
            return response(['message' => 'Anda Sudah Mengakhiri Sesi Pesanan Anda'], 200);
        } //return message saat data Order tidak ditemukan

        //$detailOrder = DetailOrder::find($id); //mencari data DetailOrder berdasarkan id
        $detailOrder = DetailOrder::where('id_order', $id_order)
                                    ->where('id_menu', $id_menu)
                                    ->where('status_item_order', '==','0')
                                    ->first(); //mencari data DetailOrder berdasarkan 
        if (is_null($detailOrder)) {
            return $this->store($request);
        } //return message saat data DetailOrder tidak ditemukan

        $updateData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($updateData, [
            'jumlah_item_order' => 'numeric'
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 200); //return error invalid input
        }
    
        $menu = Menu::find($detailOrder->id_menu); //mencari data Menu berdasarkan id

        $queryCoba = DetailOrder::join('menus', 'detail_orders.id_menu', '=', 'menus.id')
                                ->groupBy('menus.id_bahan')
                                ->where('detail_orders.id_order', $id_order)
                                ->where('menus.id_bahan', $menu->id_bahan)
                                ->where('detail_orders.status_item_order', '=','0')
                                ->where('detail_orders.id', '!=', $detailOrder->id); 
        $totalItemOrder = (int)$queryCoba->sum('jumlah_item_order');

        $bahan          = Bahan::find($menu->id_bahan);

        if ((floor($bahan->jumlah_bahan / $bahan->ukuran_porsi) + $detailOrder->jumlah_item_order) < ($totalItemOrder + $updateData['jumlah_item_order'])) {
            return response(['message' => 'Jumlah Item Menu Pesanan Melebihi Stok Tersedia'], 200); 
            //return message saat data Menu tidak valid
        } 
        else {
            $menu->jumlah_menu_tersedia            = $menu->jumlah_menu_tersedia + $detailOrder->jumlah_item_order;
            if($status == 1){
                $detailOrder->jumlah_item_order    = $updateData['jumlah_item_order'];
                $menu->jumlah_menu_tersedia        = $menu->jumlah_menu_tersedia - $detailOrder->jumlah_item_order;
            } else{
                $detailOrder->jumlah_item_order    = $detailOrder->jumlah_item_order + $updateData['jumlah_item_order'];
                $menu->jumlah_menu_tersedia        = $menu->jumlah_menu_tersedia - $detailOrder->jumlah_item_order;
            }

            $updateMenu = Menu::where('id_bahan', $bahan->id);

            if ($detailOrder->save() && $updateMenu->update(['menus.jumlah_menu_tersedia' => $menu->jumlah_menu_tersedia])){
                return response([
                    'message' => 'Mengubah Daftar Chart Success',
                    'data' => $detailOrder,
                ], 200);
            } //return data DetailOrder yang telah diedit dalam bentuk json
            return response([
                'message' => 'Mengubah Daftar Chart Failed',
                'data' => null,
            ], 200); //return message saat DetailOrder gagal diedit
        }
    }

    //method untuk mengubah 1 data DetailOrder (update)
    public function updateStatusMore($id_order)
    {
        $checkNull = DetailOrder::where('detail_orders.id_order', $id_order)
                                ->where('detail_orders.status_item_order', '==','0')
                                ->first(); 

        //$detailOrder = DetailOrder::find($id); //mencari data DetailOrder berdasarkan id
        $detailOrder = DetailOrder::where('id_order', $id_order)
                                    ->where('status_item_order', '=','0');
                                    //mencari data Order berdasarkan id_order
        if (is_null($checkNull)) {
            return response([
                'message' => 'Daftar Chart Kosong',
                'data' => null
            ], 200);
        } //return message saat data DetailOrder tidak ditemukan

        if ($this->updateBahanFromDetailOrder($id_order) == true && $detailOrder->update(['status_item_order' => 1])) {
            $this->updateOrderFromDetail($id_order);
            return response([
                'message' => 'Menambah Daftar Menu Pesanan Success',
                'data' => $detailOrder->get(),
            ], 200);
        } //return data DetailOrder yang telah diedit dalam bentuk json
        return response([
            'message' => 'Menambah Daftar Menu Pesanan Failed',
            'data' => null,
        ], 200); //return message saat DetailOrder gagal diedit*/
    }

    //method untuk mengubah 1 data DetailOrder (update)
    public function updateOrderFromDetail($id_order)
    {
        $id = $id_order;
        $checkNull = DetailOrder::where('id_order', $id_order)
                                    ->where('status_item_order', '!=','0');
                                    //mencari data Order berdasarkan id_order
        if (is_null($checkNull->first())) {
            return response([
                'message' => 'Daftar Chart Kosong',
                'data' => null
            ], 200);
        } //return message saat data DetailOrder tidak ditemukan

        $order = Order::find($id); //mencari data Order berdasarkan id
        if (is_null($order)) {
            return response([
                'message' => 'Order Not Found',
                'data' => null
            ], 200);
        } //return message saat data Order tidak ditemukan

        $queryOne                  = DetailOrder::where('id_order', $id_order)->where('status_item_order', '!=','0');
        $totalMenuOrder            = $queryOne->select('id_menu')->groupBy('id_menu')->get()->count();

        $queryTwo                  = DetailOrder::where('id_order', $id_order)->where('status_item_order', '!=','0');
        $totalItemOrder            = (int)$queryTwo->sum('jumlah_item_order');
        
        $queryThree                = DetailOrder::where('id_order', $id_order)->where('status_item_order', '!=','0');
        $subQuery_totalHargaOrder  = $queryThree->select('id_menu', 'harga_item_order','jumlah_item_order', DB::raw('(harga_item_order * jumlah_item_order) as total'))->get();
        $totalHargaOrder           = $subQuery_totalHargaOrder->sum('total');

        $order->total_menu_order   = $totalMenuOrder;
        $order->total_item_order   = $totalItemOrder;
        $order->total_harga_order  = $totalHargaOrder;

        if ($order->save()) {
            return response([
                'message' => 'Menambah Daftar Menu Pesanan Success',
                'data' => $order,
            ], 200);
        } //return data Order yang telah diedit dalam bentuk json
        return response([
            'message' => 'Menambah Daftar Menu Pesanan Failed',
            'data' => null
        ], 200); //return message saat Order gagal diedit
    }

    //method untuk mengubah 1 data DetailOrder (update)
    public function updateBahanFromDetailOrder($id_order)
    {
        $checkNull = DetailOrder::where('detail_orders.id_order', $id_order)
                                ->where('detail_orders.status_item_order', '==','0')
                                ->first();

        if (is_null($checkNull)) {
            return true;
        } 

        $updateBahan = DetailOrder::join('menus', 'detail_orders.id_menu', '=', 'menus.id')
                                ->join('bahans', 'menus.id_bahan', '=', 'bahans.id')
                                ->where('detail_orders.id_order', $id_order)
                                ->where('detail_orders.status_item_order', '=','0');        
    
        if ($updateBahan->update(['bahans.jumlah_bahan' => DB::raw('menus.jumlah_menu_tersedia * bahans.ukuran_porsi')])) {
            $menu = Menu::join('bahans', 'menus.id_bahan', '=', 'bahans.id');
            if ($menu->update(['menus.jumlah_menu_tersedia' => DB::raw('FLOOR(bahans.jumlah_bahan / bahans.ukuran_porsi)')])) {
                return true;
            } 
        } 
        return false;
    }

    //method untuk Update Status 1 data Detail Order (update)
    public function updateStatus($id, $status)
    {
        $detailOrder = DetailOrder::find($id); //mencari data DetailOrder berdasarkan id
        if (is_null($detailOrder)) {
            return response([
                'message' => 'Detail Order Not Found',
                'data' => null
            ], 404);
        } //return message saat data DetailOrder tidak ditemukan

        $detailOrder->status_item_order = $status;

        if ($detailOrder->save()) {
            return response([
                'message' => 'Update Status Detail Order Succes',
                'data' => $detailOrder,
            ], 200);
        } //return data DetailOrder yang telah diedit dalam bentuk json
        return response([
            'message' => 'Update Status Detail Order Failed',
            'data' => null
        ], 400); //return message saat DetailOrder gagal diedit
    }

    //method untuk menghapus 1 data DetailOrder (delete)
    public function destroy($id)
    {
        $detailOrder = DetailOrder::find($id); //mencari data DetailOrder berdasarkan id

        if (is_null($detailOrder)) {
            return response([
                'message' => 'Daftar Chart Kosong',
                'data' => null
            ], 200);
        } //return message saat data DetailOrder tidak ditemukan
        
        $menu  = Menu::find($detailOrder->id_menu); //mencari data Menu berdasarkan id
        $menu->jumlah_menu_tersedia = $menu->jumlah_menu_tersedia + $detailOrder->jumlah_item_order;

        $bahan          = Bahan::find($menu->id_bahan);
        $updateMenu     = Menu::where('id_bahan', $bahan->id);

        if ($detailOrder->delete() && $updateMenu->update(['menus.jumlah_menu_tersedia' => $menu->jumlah_menu_tersedia])){
            return response([
                'message' => 'Menghapus Daftar Chart Succes',
                'data' => $detailOrder,
            ], 200);
        } //return message saat berhasil menghapus data DetailOrder
        return response([
            'message' => 'Menghapus Daftar Chart Failed',
            'data' => null
        ], 200); //return message saat gagal menghapus data Detail Order
    }
}
