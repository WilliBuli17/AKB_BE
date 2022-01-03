<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator; //import library untuk validasi
use App\IncomingStock; //import modal IncomingStock
use App\Bahan; //import modal Bahan
use App\Menu; //import modal Bahan
use Illuminate\Support\Facades\DB;
use App\WasteStock; //import modal WasteStock
use App\RemainingStock; //import modal WasteStock

class IncomingStockController extends Controller
{
    //method untuk menampilkan semua data IncomingStock (read)
    public function getAll()
    {
        $incomingStock = IncomingStock::all(); //mengambil semua data IncomingStock

        $incomingStock->makeHidden(['created_at','updated_at']);

        if (count($incomingStock) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $incomingStock
            ], 200);
        } //return data semua IncomingStock dalam bentuk json

        return response([
            'message' => 'Incoming Stock Empty',
            'data' => null
        ], 404); //return message data IncomingStock kosong
    }

    //method untuk menampilkan semua data IncomingStock yang belum dihapus (read)
    public function getCustomStatus($is_Deleted)
    {
        $incomingStock = IncomingStock::join('bahans', 'incoming_stocks.id_bahan', '=', 'bahans.id')
                                        ->where('incoming_stocks.is_Deleted', $is_Deleted)
                                        ->get(['bahans.nama_bahan','incoming_stocks.*']); 
                                        //mengambil semua data IncomingStock yang belum dihapus

        $incomingStock->makeHidden(['created_at','updated_at']);

        if (count($incomingStock) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $incomingStock
            ], 200);
        } //return data semua Incoming Stock dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Incoming Stock kosong
    }

    //method untuk menampilkan 1 data IncomingStock 
    public function getOne($id)
    {
        $incomingStock = IncomingStock::find($id); //mencari data IncomingStock berdasarkan id

        $incomingStock->makeHidden(['created_at','updated_at']);

        if (!is_null($incomingStock)) {
            return response([
                'message' => 'Retrive Incoming Stock Success',
                'data' => $incomingStock
            ], 200);
        } //return data semua IncomingStock dalam bentuk json

        return response([
            'message' => 'Incoming Stock Not Found',
            'data' => null
        ], 404); //return message saat data IncomingStock tidak ditemukan
    }

    //method untuk menampilkan semua data IncomingStock yang belum dihapus (read)
    public function getCustomByBahan($is_Deleted, $tanggal_stock)
    {
        /*$first = DB::table('bahans')
                        ->select(['bahans.id', 'bahans.nama_bahan', 
                                    DB::raw('null as satuan_stock'),
                                    DB::raw('null as jumlah_stock'), 
                                    DB::raw('null as harga_stock'),
                                    DB::raw('null as tanggal_stock')])
                        ->whereNotIn('id', function ($query) use ($tanggal_stock, $is_Deleted) {
                                    $query->select('id_bahan')
                                        ->from('incoming_stocks')
                                        ->where('tanggal_stock', $tanggal_stock)
                                        ->where('is_Deleted', $is_Deleted);
                                });
                        ->get();*/

        $incomingStock = Bahan::join('incoming_stocks', 'incoming_stocks.id_bahan', '=', 'bahans.id')
                                        ->where('incoming_stocks.tanggal_stock', $tanggal_stock)
                                        ->where('incoming_stocks.is_Deleted', $is_Deleted)
                                        /*->union($first)*/
                                        ->orderBy('id')
                                        ->get(['bahans.id',
                                                'bahans.nama_bahan',
                                                'incoming_stocks.satuan_stock',
                                                'incoming_stocks.jumlah_stock',
                                                'incoming_stocks.harga_stock',
                                                'incoming_stocks.tanggal_stock']); 
                                        //mengambil semua data IncomingStock yang belum dihapus

        //$incomingStock->makeHidden(['created_at','updated_at']);

        if (count($incomingStock) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $incomingStock
            ], 200);
        } //return data semua Incoming Stock dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Incoming Stock kosong
    }

    //method untuk menampilkan semua data IncomingStock yang belum dihapus (read)
    public function getHistorycal($is_Deleted, $tanggal_stock_satu, $tanggal_stock_dua)
    {
        $wasteStock = WasteStock::join('bahans', 'waste_stocks.id_bahan', '=', 'bahans.id')
                                ->whereBetween('waste_stocks.tanggal_stock', [$tanggal_stock_satu, $tanggal_stock_dua])
                                ->where('waste_stocks.is_Deleted', $is_Deleted)
                                ->select('bahans.id',
                                            'bahans.nama_bahan',
                                            'waste_stocks.satuan_stock',
                                            'waste_stocks.jumlah_stock',
                                            DB::raw("null as harga_stock"),
                                            'waste_stocks.tanggal_stock',
                                            DB::raw("'Waste Stock' as status_stock")); 

        $remainingStocks = RemainingStock::join('bahans', 'remaining_stocks.id_bahan', '=', 'bahans.id')
                                ->whereBetween('remaining_stocks.tanggal_stock', [$tanggal_stock_satu, $tanggal_stock_dua])
                                ->where('remaining_stocks.is_Deleted', $is_Deleted)
                                ->select('bahans.id',
                                            'bahans.nama_bahan',
                                            'remaining_stocks.satuan_stock',
                                            'remaining_stocks.jumlah_stock',
                                            DB::raw("null as harga_stock"),
                                            'remaining_stocks.tanggal_stock',
                                            DB::raw("'Remaining Stock' as status_stock"));

        $incomingStock = IncomingStock::join('bahans', 'incoming_stocks.id_bahan', '=', 'bahans.id')
                                ->whereBetween('incoming_stocks.tanggal_stock', [$tanggal_stock_satu, $tanggal_stock_dua])
                                ->where('incoming_stocks.is_Deleted', $is_Deleted)
                                ->select('bahans.id',
                                            'bahans.nama_bahan',
                                            'incoming_stocks.satuan_stock',
                                            'incoming_stocks.jumlah_stock',
                                            'incoming_stocks.harga_stock',
                                            'incoming_stocks.tanggal_stock',
                                            DB::raw("'Incoming Stock' as status_stock"))
                                ->union($remainingStocks)
                                ->union($wasteStock)
                                ->get();

        if (count($incomingStock) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $incomingStock
            ], 200);
        } //return data semua Incoming Stock dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Incoming Stock kosong
    }

    //method untuk menambah 1 data IncomingStock baru(create)
    public function store(Request $request)
    {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'id_bahan' => 'required|numeric',
            'jumlah_stock' => 'required|numeric',
            'satuan_stock' => 'required',
            'harga_stock' => 'required|numeric',
            'tanggal_stock' => 'required|date_format:Y-m-d',
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }
        
        $checkNull = IncomingStock::where('id_bahan', $storeData['id_bahan'])
                                    ->where('tanggal_stock', $storeData['tanggal_stock'])
                                    ->where('is_Deleted', 0)
                                    ->first();

        if (is_null($checkNull)) {
            $incomingStock = new IncomingStock();
            $incomingStock->id_bahan           = $storeData['id_bahan'];
            $incomingStock->jumlah_stock       = $storeData['jumlah_stock'];
            $incomingStock->satuan_stock       = $storeData['satuan_stock'];
            $incomingStock->harga_stock        = $storeData['harga_stock'];
            $incomingStock->tanggal_stock      = $storeData['tanggal_stock'];
        } 
        else {
            $incomingStock = IncomingStock::where('id_bahan', $storeData['id_bahan'])
                                            ->where('tanggal_stock', $storeData['tanggal_stock'])
                                            ->where('is_Deleted', 0)
                                            ->first();

            $incomingStock->jumlah_stock   = $incomingStock->jumlah_stock + $storeData['jumlah_stock'];
            $incomingStock->harga_stock    = $incomingStock->harga_stock + $storeData['harga_stock'];
        }

        $bahan = Bahan::find($storeData['id_bahan']);
        $bahan->makeHidden(['created_at','updated_at']);
        $bahan->jumlah_bahan = $bahan->jumlah_bahan + ($storeData['jumlah_stock'] * ($bahan->total_berat_bersih / 100));

        if ($incomingStock->save() && $bahan->save() && $this->updateMenuFromStock($storeData['id_bahan']) == true ) {
            return response([
                'message' => 'Add Incoming Stock Success',
                'data' => $incomingStock,
            ], 200);
        } //return data IncomingStock yang telah diedit dalam bentuk json
        return response([
            'message' => 'Add Incoming Stock Failed',
            'data' => null,
        ], 400);
    }

    //method untuk mengubah 1 data IncomingStock (update)
    public function update(Request $request, $id)
    {
        $incomingStock = IncomingStock::find($id); //mencari data IncomingStock berdasarkan id
        if (is_null($incomingStock)) {
            return response([
                'message' => 'Incoming Stock Not Found',
                'data' => null
            ], 404);
        } //return message saat data IncomingStock tidak ditemukan

        $updateData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($updateData, [
            'id_bahan' => 'numeric',
            'jumlah_stock' => 'numeric',
            'satuan_stock' => '',
            'harga_stock' => 'numeric',
            'tanggal_stock' => 'date_format:Y-m-d',
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }
        
        $checkNull = IncomingStock::where('id_bahan', $updateData['id_bahan'])
                                    ->where('tanggal_stock', $updateData['tanggal_stock'])
                                    ->where('is_Deleted', 0)
                                    ->where('id', '!=', $id)
                                    ->first();

        if (is_null($checkNull)) {
            $bahan = Bahan::find($updateData['id_bahan']);
            $bahan->makeHidden(['created_at','updated_at']);
            $bahan->jumlah_bahan = ($bahan->jumlah_bahan - ($incomingStock->jumlah_stock * ($bahan->total_berat_bersih / 100))) + ($updateData['jumlah_stock'] * ($bahan->total_berat_bersih / 100));
            
            if($bahan->jumlah_bahan < 0){
                return response([
                    'message' => 'Data Tidak Bisa Diupdate, Bahan Sudah Dipakai',
                ], 401);
            }
    
            $incomingStock->id_bahan           = $updateData['id_bahan'];
            $incomingStock->jumlah_stock       = $updateData['jumlah_stock'];
            $incomingStock->satuan_stock       = $updateData['satuan_stock'];
            $incomingStock->harga_stock        = $updateData['harga_stock'];
            $incomingStock->tanggal_stock      = $updateData['tanggal_stock'];

            if ($incomingStock->save() && $bahan->save() && $this->updateMenuFromStock($updateData['id_bahan']) == true) {
                return response([
                    'message' => 'Update Incoming Stock Success',
                    'data' => $incomingStock,
                ], 200);
            } //return data IncomingStock yang telah diedit dalam bentuk json
        } 
        else {
            $updateIncoming = IncomingStock::where('id_bahan', $updateData['id_bahan'])
                                            ->where('tanggal_stock', $updateData['tanggal_stock'])
                                            ->where('is_Deleted', 0)
                                            ->where('id', '!=', $id)
                                            ->first();

            $bahan = Bahan::find($updateData['id_bahan']);
            $bahan->makeHidden(['created_at','updated_at']);
            $bahan->jumlah_bahan = ($bahan->jumlah_bahan - ($incomingStock->jumlah_stock * ($bahan->total_berat_bersih / 100))) + ($updateData['jumlah_stock'] * ($bahan->total_berat_bersih / 100));
            
            if($bahan->jumlah_bahan < 0){
                return response([
                    'message' => 'Data Tidak Bisa Diupdate, Bahan Sudah Dipakai',
                ], 401);
            }

            $updateIncoming->jumlah_stock       = $updateIncoming->jumlah_stock + $updateData['jumlah_stock'];
            $updateIncoming->harga_stock        = $updateIncoming->harga_stock + $updateData['harga_stock'];

            if ($incomingStock->delete() && $updateIncoming->save() && $bahan->save() && $this->updateMenuFromStock($updateData['id_bahan']) == true) {
                return response([
                    'message' => 'Update Incoming Stock Succes',
                    'data' => $updateIncoming,
                ], 200);
            } //return data IncomingStock yang telah diedit dalam bentuk json
        }
        return response([
            'message' => 'Update Incoming Stock Failed',
            'data' => null,
        ], 400); //return message saat IncomingStock gagal diedit
    }

    public function updateMenuFromStock($id_bahan){
        $checkNull = Menu::join('bahans', 'menus.id_bahan', '=', 'bahans.id')
                            ->where('menus.id_bahan', $id_bahan)
                            ->first();

        if (is_null($checkNull)) {
            return true;
        } 
        
        $menu = Menu::join('bahans', 'menus.id_bahan', '=', 'bahans.id')
                    ->where('menus.id_bahan', $id_bahan);

        if ($menu->update(['menus.jumlah_menu_tersedia' => DB::raw('FLOOR(bahans.jumlah_bahan / bahans.ukuran_porsi)')])) {
            return true;
        } 
        return false;
    }

    //method untuk menghapus 1 data IncomingStock (delete)
    public function destroy($id)
    {
        $incomingStock = IncomingStock::find($id); //mencari data IncomingStock berdasarkan id

        if (is_null($incomingStock)) {
            return response([
                'message' => 'Incoming Stock Not Found',
                'data' => null
            ], 404);
        } //return message saat data Incoming Stock tidak ditemukan

        if ($incomingStock->delete()) {
            return response([
                'message' => 'Delete Incoming Stock Succes',
                'data' => $incomingStock,
            ], 200);
        } //return message saat berhasil menghapus data Incoming Stock
        return response([
            'message' => 'Delete Incoming Stock Failed',
            'data' => null
        ], 400); //return message saat gagal menghapus data Incoming Stock
    }

    //method untuk soft Delete 1 data IncomingStock (update)
    public function delete($id)
    {
        $incomingStock = IncomingStock::find($id); //mencari data IncomingStock berdasarkan id
        if (is_null($incomingStock)) {
            return response([
                'message' => 'Incoming Stock Not Found',
                'data' => null
            ], 404);
        } //return message saat data IncomingStock tidak ditemukan

        $checkNull = IncomingStock::where('id_bahan', $incomingStock->id_bahan)
                                    ->where('tanggal_stock', $incomingStock->tanggal_stock)
                                    ->where('is_Deleted', 1)
                                    ->where('id', '!=', $id)
                                    ->first();

        if (is_null($checkNull)) {
            $incomingStock->is_Deleted = 1;

            $bahan = Bahan::find($incomingStock->id_bahan);
            $bahan->makeHidden(['created_at','updated_at']);
            $bahan->jumlah_bahan = $bahan->jumlah_bahan - ($incomingStock->jumlah_stock * ($bahan->total_berat_bersih / 100));
            
            if($bahan->jumlah_bahan < 0){
                return response([
                    'message' => 'Data Tidak Bisa Dihapus, Bahan Sudah Dipakai',
                ], 401);
            }

            if ($incomingStock->save() && $bahan->save() && $this->updateMenuFromStock($incomingStock->id_bahan) == true) {
                return response([
                    'message' => 'Delete Incoming Stock Succes',
                    'data' => $incomingStock,
                ], 200);
            } //return data IncomingStock yang telah diedit dalam bentuk json
        } 
        else {
            $updateIncoming = IncomingStock::where('id_bahan', $incomingStock->id_bahan)
                                            ->where('tanggal_stock', $incomingStock->tanggal_stock)
                                            ->where('is_Deleted', 1)
                                            ->where('id', '!=', $id)
                                            ->first();

            $updateIncoming->jumlah_stock       = $updateIncoming->jumlah_stock + $incomingStock->jumlah_stock;
            $updateIncoming->harga_stock        = $updateIncoming->harga_stock + $incomingStock->harga_stock;

            $bahan = Bahan::find($incomingStock->id_bahan);
            $bahan->makeHidden(['created_at','updated_at']);
            $bahan->jumlah_bahan = $bahan->jumlah_bahan - ($incomingStock->jumlah_stock * ($bahan->total_berat_bersih / 100));
            
            if($bahan->jumlah_bahan < 0){
                return response([
                    'message' => 'Data Tidak Bisa Dihapus, Bahan Sudah Dipakai',
                ], 401);
            }

            if ($incomingStock->delete() && $updateIncoming->save() && $bahan->save() && $this->updateMenuFromStock($incomingStock->id_bahan) == true) {
                return response([
                    'message' => 'Delete Incoming Stock Succes',
                    'data' => $updateIncoming,
                ], 200);
            } //return data IncomingStock yang telah diedit dalam bentuk json
        }
        return response([
            'message' => 'Delete Incoming Stock Failed',
            'data' => null
        ], 400); //return message saat IncomingStock gagal diedit
    }

    //method untuk soft Delete 1 data IncomingStock (update)
    public function restore($id)
    {
        $incomingStock = IncomingStock::find($id); //mencari data IncomingStock berdasarkan id
        if (is_null($incomingStock)) {
            return response([
                'message' => 'Incoming Stock Not Found',
                'data' => null
            ], 404);
        } //return message saat data IncomingStock tidak ditemukan

        $checkNull = IncomingStock::where('id_bahan', $incomingStock->id_bahan)
                                            ->where('tanggal_stock', $incomingStock->tanggal_stock)
                                            ->where('is_Deleted', 0)
                                            ->where('id', '!=', $id)
                                            ->first();

        if (is_null($checkNull)) {
            $incomingStock->is_Deleted = 0;
    
            $bahan = Bahan::find($incomingStock->id_bahan);
            $bahan->makeHidden(['created_at','updated_at']);
            $bahan->jumlah_bahan = $bahan->jumlah_bahan + ($incomingStock->jumlah_stock * ($bahan->total_berat_bersih / 100));
    
            if ($incomingStock->save()  && $bahan->save() && $this->updateMenuFromStock($incomingStock->id_bahan) == true) {
                return response([
                    'message' => 'Restore Incoming Stock Succes',
                    'data' => $incomingStock,
                ], 200);
            } //return data IncomingStock yang telah diedit dalam bentuk json
        } 
        else {
            $updateIncoming = IncomingStock::where('id_bahan', $incomingStock->id_bahan)
                                            ->where('tanggal_stock', $incomingStock->tanggal_stock)
                                            ->where('is_Deleted', 0)
                                            ->where('id', '!=', $id)
                                            ->first();

            $updateIncoming->jumlah_stock       = $updateIncoming->jumlah_stock + $incomingStock->jumlah_stock;
            $updateIncoming->harga_stock        = $updateIncoming->harga_stock + $incomingStock->harga_stock;

            $bahan = Bahan::find($incomingStock->id_bahan);
            $bahan->makeHidden(['created_at','updated_at']);
            $bahan->jumlah_bahan = $bahan->jumlah_bahan + ($incomingStock->jumlah_stock * ($bahan->total_berat_bersih / 100));

            if ($incomingStock->delete() && $updateIncoming->save() && $bahan->save() && $this->updateMenuFromStock($incomingStock->id_bahan) == true) {
                return response([
                    'message' => 'Restore Incoming Stock Succes',
                    'data' => $updateIncoming,
                ], 200);
            } //return data IncomingStock yang telah diedit dalam bentuk json
        }
        return response([
            'message' => 'Restore Incoming Stock Failed',
            'data' => null
        ], 400); //return message saat IncomingStock gagal diedit
    }
}
