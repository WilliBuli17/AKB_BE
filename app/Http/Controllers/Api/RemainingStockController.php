<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator; //import library untuk validasi
use App\RemainingStock; //import modal Customer
use App\Bahan; //import modal Bahan
use App\Menu; //import modal Bahan
use Illuminate\Support\Facades\DB;

class RemainingStockController extends Controller
{
    //method untuk menampilkan semua data RemainingStock (read)
    public function getAll()
    {
        $remainingStock = RemainingStock::all(); //mengambil semua data RemainingStock

        $remainingStock->makeHidden(['created_at','updated_at']);

        if (count($remainingStock) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $remainingStock
            ], 200);
        } //return data semua RemainingStock dalam bentuk json

        return response([
            'message' => 'Remaining Stock Empty',
            'data' => null
        ], 404); //return message data RemainingStock kosong
    }

    //method untuk menampilkan semua data RemainingStock yang belum dihapus (read)
    public function getCustomStatus($is_Deleted)
    {
        $remainingStock = RemainingStock::join('bahans', 'remaining_stocks.id_bahan', '=', 'bahans.id')
                                        ->where('remaining_stocks.is_Deleted', $is_Deleted)
                                        ->get(['bahans.nama_bahan','remaining_stocks.*']); 
                                        //mengambil semua data RemainingStock yang belum dihapus

        $remainingStock->makeHidden(['created_at','updated_at']);

        if (count($remainingStock) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $remainingStock
            ], 200);
        } //return data semua Remaining Stock dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Remaining Stock kosong
    }

    //method untuk menampilkan 1 data RemainingStock 
    public function getOne($id)
    {
        $remainingStock = RemainingStock::find($id); //mencari data RemainingStock berdasarkan id

        $remainingStock->makeHidden(['created_at','updated_at']);

        if (!is_null($remainingStock)) {
            return response([
                'message' => 'Retrive Remaining Stock Success',
                'data' => $remainingStock
            ], 200);
        } //return data semua RemainingStock dalam bentuk json

        return response([
            'message' => 'Remaining Stock Not Found',
            'data' => null
        ], 404); //return message saat data RemainingStock tidak ditemukan
    }

    //method untuk menampilkan semua data RemainingStock yang belum dihapus (read)
    public function getCustomByBahan($is_Deleted, $tanggal_stock)
    {
        /*$first = DB::table('bahans')
                        ->select(['bahans.id', 'bahans.nama_bahan', 
                                    DB::raw('null as satuan_stock'),
                                    DB::raw('null as jumlah_stock'), 
                                    DB::raw('null as tanggal_stock')])
                        ->whereNotIn('id', function ($query) use ($tanggal_stock, $is_Deleted) {
                                    $query->select('id_bahan')
                                        ->from('remaining_stocks')
                                        ->where('tanggal_stock', $tanggal_stock)
                                        ->where('is_Deleted', $is_Deleted);
                                });
                        ->get();*/

        $remainingStock = Bahan::join('remaining_stocks', 'remaining_stocks.id_bahan', '=', 'bahans.id')
                                        ->where('remaining_stocks.tanggal_stock', $tanggal_stock)
                                        ->where('remaining_stocks.is_Deleted', $is_Deleted)
                                        /*->union($first)*/
                                        ->orderBy('id')
                                        ->get(['bahans.id',
                                                'bahans.nama_bahan',
                                                'remaining_stocks.satuan_stock',
                                                'remaining_stocks.jumlah_stock',
                                                'remaining_stocks.tanggal_stock']); 
                                        //mengambil semua data RemainingStock yang belum dihapus

        //$remainingStock->makeHidden(['created_at','updated_at']);

        if (count($remainingStock) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $remainingStock
            ], 200);
        } //return data semua RemainingStock Stock dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data RemainingStock kosong
    }

    //method untuk menambah 1 data RemainingStock baru(create)
    public function store(Request $request)
    {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'id_bahan' => 'required|numeric',
            'jumlah_stock' => 'required|numeric',
            'satuan_stock' => 'required',
            'tanggal_stock' => 'required|date_format:Y-m-d',
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }
        
        $checkNull = RemainingStock::where('id_bahan', $storeData['id_bahan'])
                                    ->where('tanggal_stock', $storeData['tanggal_stock'])
                                    ->where('is_Deleted', 0)
                                    ->first();

        if (is_null($checkNull)) {
            $remainingStock = new RemainingStock();
            $remainingStock->id_bahan           = $storeData['id_bahan'];
            $remainingStock->jumlah_stock       = $storeData['jumlah_stock'];
            $remainingStock->satuan_stock       = $storeData['satuan_stock'];
            $remainingStock->tanggal_stock      = $storeData['tanggal_stock'];
        } 
        else {
            $remainingStock = RemainingStock::where('id_bahan', $storeData['id_bahan'])
                                            ->where('tanggal_stock', $storeData['tanggal_stock'])
                                            ->where('is_Deleted', 0)
                                            ->first();

            $remainingStock->jumlah_stock   = $remainingStock->jumlah_stock + $storeData['jumlah_stock'];
        }

        $bahan = Bahan::find($storeData['id_bahan']);
        $bahan->makeHidden(['created_at','updated_at']);
        
        if($bahan->jumlah_bahan < $remainingStock->jumlah_stock){
            return response([
                'message' => 'Data Tidak Bisa Dimasukkan, Jumlah Sisa Melebihi Stok Yang Tersedia',
            ], 401);
        }

        if ($remainingStock->save()) {
            return response([
                'message' => 'Add Remaining Stock Success',
                'data' => $remainingStock,
            ], 200);
        } //return data RemainingStock yang telah diedit dalam bentuk json
        return response([
            'message' => 'Add Remaining Stock Failed',
            'data' => null,
        ], 400);
    }

    //method untuk mengubah 1 data RemainingStock (update)
    public function update(Request $request, $id)
    {
        $remainingStock = RemainingStock::find($id); //mencari data RemainingStock berdasarkan id
        if (is_null($remainingStock)) {
            return response([
                'message' => 'Remaining Stock Not Found',
                'data' => null
            ], 404);
        } //return message saat data RemainingStock tidak ditemukan

        $updateData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($updateData, [
            'id_bahan' => 'numeric',
            'jumlah_stock' => 'numeric',
            'satuan_stock' => '',
            'tanggal_stock' => 'date_format:Y-m-d',
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }
        
        $checkNull = RemainingStock::where('id_bahan', $updateData['id_bahan'])
                                    ->where('tanggal_stock', $updateData['tanggal_stock'])
                                    ->where('is_Deleted', 0)
                                    ->where('id', '!=', $id)
                                    ->first();

        if (is_null($checkNull)) {
            $bahan = Bahan::find($updateData['id_bahan']);
            $bahan->makeHidden(['created_at','updated_at']);
            
            if($bahan->jumlah_bahan < $updateData['jumlah_stock']){
                return response([
                    'message' => 'Data Tidak Bisa Diupdate, Jumlah Sisa Melebihi Stok Yang Tersedia',
                ], 401);
            }

            $remainingStock->id_bahan           = $updateData['id_bahan'];
            $remainingStock->jumlah_stock       = $updateData['jumlah_stock'];
            $remainingStock->satuan_stock       = $updateData['satuan_stock'];
            $remainingStock->tanggal_stock      = $updateData['tanggal_stock'];

            if ($remainingStock->save()) {
                return response([
                    'message' => 'Update Remaining Stock Success',
                    'data' => $remainingStock,
                ], 200);
            } //return data RemainingStock yang telah diedit dalam bentuk json
        } 
        else {
            $updateRemaining = RemainingStock::where('id_bahan', $updateData['id_bahan'])
                                            ->where('tanggal_stock', $updateData['tanggal_stock'])
                                            ->where('is_Deleted', 0)
                                            ->where('id', '!=', $id)
                                            ->first();

            $updateRemaining->jumlah_stock = $updateRemaining->jumlah_stock + $updateData['jumlah_stock'];

            $bahan = Bahan::find($updateData['id_bahan']);
            $bahan->makeHidden(['created_at','updated_at']);
            
            if($bahan->jumlah_bahan < $updateRemaining->jumlah_stock){
                return response([
                    'message' => 'Data Tidak Bisa Diupdate, Jumlah Sisa Melebihi Stok Yang Tersedia',
                ], 401);
            }
        
            if ($remainingStock->delete() && $updateRemaining->save()) {
                return response([
                    'message' => 'Update Remaining Stock Succes',
                    'data' => $updateRemaining,
                ], 200);
            } //return data RemainingStock yang telah diedit dalam bentuk json
        } 
        return response([
            'message' => 'Update Remaining Stock Failed',
            'data' => null,
        ], 400); //return message saat RemainingStock gagal diedit
    }

    //method untuk menghapus 1 data RemainingStock (delete)
    public function destroy($id)
    {
        $remainingStock = RemainingStock::find($id); //mencari data RemainingStock berdasarkan id

        if (is_null($remainingStock)) {
            return response([
                'message' => 'Remaining Stock Not Found',
                'data' => null
            ], 404);
        } //return message saat data Remaining Stock tidak ditemukan

        if ($remainingStock->delete()) {
            return response([
                'message' => 'Delete Remaining Stock Succes',
                'data' => $remainingStock,
            ], 200);
        } //return message saat berhasil menghapus data Remaining Stock
        return response([
            'message' => 'Delete Remaining Stock Failed',
            'data' => null
        ], 400); //return message saat gagal menghapus data Remaining Stock
    }

    //method untuk soft Delete 1 data RemainingStock (update)
    public function delete($id)
    {
        $remainingStock = RemainingStock::find($id); //mencari data RemainingStock berdasarkan id
        if (is_null($remainingStock)) {
            return response([
                'message' => 'Remaining Stock Not Found',
                'data' => null
            ], 404);
        } //return message saat data RemainingStock tidak ditemukan

        $remainingStock->is_Deleted = 1;

        if ($remainingStock->save()) {
            return response([
                'message' => 'Delete Remaining Stock Succes',
                'data' => $remainingStock,
            ], 200);
        } //return data RemainingStock yang telah diedit dalam bentuk json
        return response([
            'message' => 'Delete Remaining Stock Failed',
            'data' => null
        ], 400); //return message saat RemainingStock gagal diedit
    }

    //method untuk soft Delete 1 data RemainingStock (update)
    public function restore($id)
    {
        $remainingStock = RemainingStock::find($id); //mencari data RemainingStock berdasarkan id
        if (is_null($remainingStock)) {
            return response([
                'message' => 'Remaining Stock Not Found',
                'data' => null
            ], 404);
        } //return message saat data RemainingStock tidak ditemukan

        $remainingStock->is_Deleted = 0;

        if ($remainingStock->save()) {
            return response([
                'message' => 'Restore Remaining Stock Succes',
                'data' => $remainingStock,
            ], 200);
        } //return data RemainingStock yang telah diedit dalam bentuk json
        return response([
            'message' => 'Restore Remaining Stock Failed',
            'data' => null
        ], 400); //return message saat RemainingStock gagal diedit
    }
}
