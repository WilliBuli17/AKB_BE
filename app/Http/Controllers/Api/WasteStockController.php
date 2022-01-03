<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator; //import library untuk validasi
use App\WasteStock; //import modal WasteStock
use App\Bahan; //import modal Bahan
use App\Menu; //import modal Bahan
use Illuminate\Support\Facades\DB;

class WasteStockController extends Controller
{
    //method untuk menampilkan semua data WasteStock (read)
    public function getAll()
    {
        $wasteStock = WasteStock::all(); //mengambil semua data WasteStock

        $wasteStock->makeHidden(['created_at','updated_at']);

        if (count($wasteStock) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $wasteStock
            ], 200);
        } //return data semua WasteStock dalam bentuk json

        return response([
            'message' => 'Waste Stock Empty',
            'data' => null
        ], 404); //return message data WasteStock kosong
    }

    //method untuk menampilkan semua data WasteStock yang belum dihapus (read)
    public function getCustomStatus($is_Deleted)
    {
        $wasteStock = WasteStock::join('bahans', 'waste_stocks.id_bahan', '=', 'bahans.id')
                                        ->where('waste_stocks.is_Deleted', $is_Deleted)
                                        ->get(['bahans.nama_bahan','waste_stocks.*']); 
                                        //mengambil semua data WasteStock yang belum dihapus

        $wasteStock->makeHidden(['created_at','updated_at']);

        if (count($wasteStock) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $wasteStock
            ], 200);
        } //return data semua Waste Stock dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Waste Stock kosong
    }

    //method untuk menampilkan 1 data WasteStock 
    public function getOne($id)
    {
        $wasteStock = WasteStock::find($id); //mencari data WasteStock berdasarkan id

        $wasteStock->makeHidden(['created_at','updated_at']);

        if (!is_null($wasteStock)) {
            return response([
                'message' => 'Retrive Waste Stock Success',
                'data' => $wasteStock
            ], 200);
        } //return data semua WasteStock dalam bentuk json

        return response([
            'message' => 'Waste Stock Not Found',
            'data' => null
        ], 404); //return message saat data WasteStock tidak ditemukan
    }

    //method untuk menampilkan semua data WasteStock yang belum dihapus (read)
    public function getCustomByBahan($is_Deleted, $tanggal_stock)
    {
        /*$first = DB::table('bahans')
                        ->select(['bahans.id', 'bahans.nama_bahan', 
                                    DB::raw('null as satuan_stock'),
                                    DB::raw('null as jumlah_stock'), 
                                    DB::raw('null as tanggal_stock')])
                        ->whereNotIn('id', function ($query) use ($tanggal_stock, $is_Deleted) {
                                    $query->select('id_bahan')
                                        ->from('waste_stocks')
                                        ->where('tanggal_stock', $tanggal_stock)
                                        ->where('is_Deleted', $is_Deleted);
                                });
                        ->get();*/

        $wasteStock = Bahan::join('waste_stocks', 'waste_stocks.id_bahan', '=', 'bahans.id')
                            ->where('waste_stocks.tanggal_stock', $tanggal_stock)
                            ->where('waste_stocks.is_Deleted', $is_Deleted)
                            /*->union($first)*/
                            ->orderBy('id')
                            ->get(['bahans.id',
                                    'bahans.nama_bahan',
                                    'waste_stocks.satuan_stock',
                                    'waste_stocks.jumlah_stock',
                                    'waste_stocks.tanggal_stock']); 
                            //mengambil semua data WasteStock yang belum dihapus

        //$wasteStock->makeHidden(['created_at','updated_at']);

        if (count($wasteStock) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $wasteStock
            ], 200);
        } //return data semua WasteStock Stock dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data WasteStock kosong
    }

    //method untuk menambah 1 data WasteStock baru(create)
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
        
        $checkNull = WasteStock::where('id_bahan', $storeData['id_bahan'])
                                    ->where('tanggal_stock', $storeData['tanggal_stock'])
                                    ->where('is_Deleted', 0)
                                    ->first();

        if (is_null($checkNull)) {
            $wasteStock = new WasteStock();
            $wasteStock->id_bahan           = $storeData['id_bahan'];
            $wasteStock->jumlah_stock       = $storeData['jumlah_stock'];
            $wasteStock->satuan_stock       = $storeData['satuan_stock'];
            $wasteStock->tanggal_stock      = $storeData['tanggal_stock'];
        } 
        else {
            $wasteStock = WasteStock::where('id_bahan', $storeData['id_bahan'])
                                            ->where('tanggal_stock', $storeData['tanggal_stock'])
                                            ->where('is_Deleted', 0)
                                            ->first();

            $wasteStock->jumlah_stock   = $wasteStock->jumlah_stock + $storeData['jumlah_stock'];
        }

        $bahan = Bahan::find($storeData['id_bahan']);
        $bahan->makeHidden(['created_at','updated_at']);
        $bahan->jumlah_bahan = $bahan->jumlah_bahan - $storeData['jumlah_stock'];

        if($bahan->jumlah_bahan < 0){
            return response([
                'message' => 'Data Tidak Bisa Dimasukkan, Jumlah Buang Melebihi Stok Yang Tersedia',
            ], 401);
        }

        if ($wasteStock->save() && $bahan->save() && $this->updateMenuFromStock($storeData['id_bahan']) == true ) {
            return response([
                'message' => 'Add Waste Stock Success',
                'data' => $wasteStock,
            ], 200);
        } //return data WasteStock yang telah diedit dalam bentuk json
        return response([
            'message' => 'Add Waste Stock Failed',
            'data' => null,
        ], 400);
    }

    //method untuk mengubah 1 data WasteStock (update)
    public function update(Request $request, $id)
    {
        $wasteStock = WasteStock::find($id); //mencari data WasteStock berdasarkan id
        if (is_null($wasteStock)) {
            return response([
                'message' => 'Waste Stock Not Found',
                'data' => null
            ], 404);
        } //return message saat data WasteStock tidak ditemukan

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
        
        $checkNull = WasteStock::where('id_bahan', $updateData['id_bahan'])
                                    ->where('tanggal_stock', $updateData['tanggal_stock'])
                                    ->where('is_Deleted', 0)
                                    ->where('id', '!=', $id)
                                    ->first();

        if (is_null($checkNull)) {
            $bahan = Bahan::find($updateData['id_bahan']);
            $bahan->makeHidden(['created_at','updated_at']);            
            $bahan->jumlah_bahan = ($bahan->jumlah_bahan + $wasteStock->jumlah_stock) - $updateData['jumlah_stock'];

            if($bahan->jumlah_bahan < 0){
                return response([
                    'message' => 'Data Tidak Bisa Diupdate, Jumlah Buang Melebihi Stok Yang Tersedia',
                ], 401);
            }

            $wasteStock->id_bahan           = $updateData['id_bahan'];
            $wasteStock->jumlah_stock       = $updateData['jumlah_stock'];
            $wasteStock->satuan_stock       = $updateData['satuan_stock'];
            $wasteStock->tanggal_stock      = $updateData['tanggal_stock'];

            if ($wasteStock->save() && $bahan->save() && $this->updateMenuFromStock($updateData['id_bahan']) == true) {
                return response([
                    'message' => 'Update Waste Stock Success',
                    'data' => $wasteStock,
                ], 200);
            } //return data WasteStock yang telah diedit dalam bentuk json
        } 
        else {
            $updateWaste = WasteStock::where('id_bahan', $updateData['id_bahan'])
                                            ->where('tanggal_stock', $updateData['tanggal_stock'])
                                            ->where('is_Deleted', 0)
                                            ->where('id', '!=', $id)
                                            ->first();

            $bahan = Bahan::find($updateData['id_bahan']);
            $bahan->makeHidden(['created_at','updated_at']);
            $bahan->jumlah_bahan = ($bahan->jumlah_bahan + $wasteStock->jumlah_stock) - $updateData['jumlah_stock'];
            
            if($bahan->jumlah_bahan < 0){
                return response([
                    'message' => 'Data Tidak Bisa Diupdate, Jumlah Buang Melebihi Stok Yang Tersedia',
                ], 401);
            }
            
            $updateWaste->jumlah_stock       = $updateWaste->jumlah_stock + $updateData['jumlah_stock'];

            if ($wasteStock->delete() && $updateWaste->save() && $bahan->save() && $this->updateMenuFromStock($updateData['id_bahan']) == true) {
                return response([
                    'message' => 'Update Waste Stock Succes',
                    'data' => $updateWaste,
                ], 200);
            } //return data WasteStock yang telah diedit dalam bentuk json
        }
        return response([
            'message' => 'Update Waste Stock Failed',
            'data' => null,
        ], 400); //return message saat WasteStock gagal diedit
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

    //method untuk menghapus 1 data WasteStock (delete)
    public function destroy($id)
    {
        $wasteStock = WasteStock::find($id); //mencari data WasteStock berdasarkan id

        if (is_null($wasteStock)) {
            return response([
                'message' => 'Waste Stock Not Found',
                'data' => null
            ], 404);
        } //return message saat data Waste Stock tidak ditemukan

        if ($wasteStock->delete()) {
            return response([
                'message' => 'Delete Waste Stock Succes',
                'data' => $wasteStock,
            ], 200);
        } //return message saat berhasil menghapus data Waste Stock
        return response([
            'message' => 'Delete Waste Stock Failed',
            'data' => null
        ], 400); //return message saat gagal menghapus data Waste Stock
    }

    //method untuk soft Delete 1 data WasteStock (update)
    public function delete($id)
    {
        $wasteStock = WasteStock::find($id); //mencari data WasteStock berdasarkan id
        if (is_null($wasteStock)) {
            return response([
                'message' => 'Waste Stock Not Found',
                'data' => null
            ], 404);
        } //return message saat data WasteStock tidak ditemukan

        $checkNull = WasteStock::where('id_bahan', $wasteStock->id_bahan)
                                    ->where('tanggal_stock', $wasteStock->tanggal_stock)
                                    ->where('is_Deleted', 1)
                                    ->where('id', '!=', $id)
                                    ->first();

        if (is_null($checkNull)) {
            $wasteStock->is_Deleted = 1;

            $bahan = Bahan::find($wasteStock->id_bahan);
            $bahan->makeHidden(['created_at','updated_at']);
            $bahan->jumlah_bahan = $bahan->jumlah_bahan + $wasteStock->jumlah_stock;

            if ($wasteStock->save()&& $bahan->save() && $this->updateMenuFromStock($wasteStock->id_bahan) == true) {
                return response([
                    'message' => 'Delete Waste Stock Succes',
                    'data' => $wasteStock,
                ], 200);
            } //return data WasteStock yang telah diedit dalam bentuk json
        }
        else {
            $updateWaste = WasteStock::where('id_bahan', $wasteStock->id_bahan)
                                            ->where('tanggal_stock', $wasteStock->tanggal_stock)
                                            ->where('is_Deleted', 1)
                                            ->where('id', '!=', $id)
                                            ->first();

            $updateWaste->jumlah_stock       = $updateWaste->jumlah_stock + $wasteStock->jumlah_stock;

            $bahan = Bahan::find($wasteStock->id_bahan);
            $bahan->makeHidden(['created_at','updated_at']);
            $bahan->jumlah_bahan = $bahan->jumlah_bahan + $wasteStock->jumlah_stock;

            if ($wasteStock->delete() && $updateWaste->save() && $bahan->save() && $this->updateMenuFromStock($wasteStock->id_bahan) == true) {
                return response([
                    'message' => 'Delete Waste Stock Succes',
                    'data' => $updateWaste,
                ], 200);
            } //return data WasteStock yang telah diedit dalam bentuk json
        }
        return response([
            'message' => 'Delete Waste Stock Failed',
            'data' => null
        ], 400); //return message saat WasteStock gagal diedit
    }

    //method untuk soft Delete 1 data WasteStock (update)
    public function restore($id)
    {
        $wasteStock = WasteStock::find($id); //mencari data WasteStock berdasarkan id
        if (is_null($wasteStock)) {
            return response([
                'message' => 'Waste Stock Not Found',
                'data' => null
            ], 404);
        } //return message saat data WasteStock tidak ditemukan

        $checkNull = WasteStock::where('id_bahan', $wasteStock->id_bahan)
                                            ->where('tanggal_stock', $wasteStock->tanggal_stock)
                                            ->where('is_Deleted', 0)
                                            ->where('id', '!=', $id)
                                            ->first();

        if (is_null($checkNull)) {
            $wasteStock->is_Deleted = 0;

            $bahan = Bahan::find($wasteStock->id_bahan);
            $bahan->makeHidden(['created_at','updated_at']);
            $bahan->jumlah_bahan = $bahan->jumlah_bahan - $wasteStock->jumlah_stock;

            if($bahan->jumlah_bahan < 0){
                return response([
                    'message' => 'Data Tidak Bisa Direstore, Jumlah Buang Melebihi Stok Yang Tersedia',
                ], 401);
            }
            
            if ($wasteStock->save()&& $bahan->save() && $this->updateMenuFromStock($wasteStock->id_bahan) == true) {
                return response([
                    'message' => 'Restore Waste Stock Succes',
                    'data' => $wasteStock,
                ], 200);
            } //return data WasteStock yang telah diedit dalam bentuk json
        }
        else {
            $updateWaste = WasteStock::where('id_bahan', $wasteStock->id_bahan)
                                            ->where('tanggal_stock', $wasteStock->tanggal_stock)
                                            ->where('is_Deleted', 0)
                                            ->where('id', '!=', $id)
                                            ->first();            
            
            $updateWaste->jumlah_stock       = $updateWaste->jumlah_stock + $wasteStock->jumlah_stock;

            $bahan = Bahan::find($wasteStock->id_bahan);
            $bahan->makeHidden(['created_at','updated_at']);
            $bahan->jumlah_bahan = $bahan->jumlah_bahan - $wasteStock->jumlah_stock;

            if($bahan->jumlah_bahan < 0){
                return response([
                    'message' => 'Data Tidak Bisa Direstore, Jumlah Buang Melebihi Stok Yang Tersedia',
                ], 401);
            }

            if ($wasteStock->delete() && $updateWaste->save() && $bahan->save() && $this->updateMenuFromStock($wasteStock->id_bahan) == true) {
                return response([
                    'message' => 'Restore Waste Stock Succes',
                    'data' => $updateWaste,
                ], 200);
            } //return data WasteStock yang telah diedit dalam bentuk json

        }
        return response([
            'message' => 'Restore Waste Stock Failed',
            'data' => null
        ], 400); //return message saat WasteStock gagal diedit
    }
}
