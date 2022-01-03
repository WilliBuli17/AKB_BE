<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator; //import library untuk validasi
use App\Bahan; //import modal Customer

class BahanController extends Controller
{
    //method untuk menampilkan semua data Bahan (read)
    public function getAll()
    {
        $bahan = Bahan::all(); //mengambil semua data Bahan

        $bahan->makeHidden(['created_at','updated_at']);

        if (count($bahan) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $bahan
            ], 200);
        } //return data semua Bahan dalam bentuk json

        return response([
            'message' => 'Bahan Empty',
            'data' => null
        ], 404); //return message data Bahan kosong
    }

    //method untuk menampilkan semua data Bahan yang belum dihapus (read)
    public function getCustomStatus($is_Deleted)
    {
        $bahan = Bahan::where('is_Deleted', $is_Deleted)->get(); //mengambil semua data Bahan yang belum dihapus

        $bahan->makeHidden(['created_at','updated_at']);

        if (count($bahan) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $bahan
            ], 200);
        } //return data semua Bahan dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Bahan kosong
    }

    //method untuk menampilkan semua data Bahan yang belum dihapus (read)
    public function getCustomByJumlah($is_Deleted, $check)
    {
        if($check == 1){
            $bahan = Bahan::where('is_Deleted', $is_Deleted)
                        ->whereRaw('jumlah_bahan >= ukuran_porsi')
                        ->get(); //mengambil semua data Bahan yang belum dihapus
        } else{
            $bahan = Bahan::where('is_Deleted', $is_Deleted)
                        ->whereRaw('jumlah_bahan < ukuran_porsi')
                        ->get(); //mengambil semua data Bahan yang belum dihapus
        }
        
        $bahan->makeHidden(['created_at','updated_at']);

        if (count($bahan) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $bahan
            ], 200);
        } //return data semua Bahan dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Bahan kosong
    }
    
    //method untuk menampilkan 1 data Bahan 
    public function getOne($id)
    {
        $bahan = Bahan::find($id); //mencari data Bahan berdasarkan id

        $bahan->makeHidden(['created_at','updated_at']);

        if (!is_null($bahan)) {
            return response([
                'message' => 'Retrive Bahan Success',
                'data' => $bahan
            ], 200);
        } //return data semua Bahan dalam bentuk json

        return response([
            'message' => 'Bahan Not Found',
            'data' => null
        ], 404); //return message saat data Bahan tidak ditemukan
    }

    //method untuk menambah 1 data Bahan baru(create)
    public function store(Request $request)
    {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'nama_bahan' => 'required|max:60|unique:bahans',
            'jenis_bahan' => 'required',
            'total_berat_bersih' => 'required|numeric',
            'ukuran_porsi' => 'required|numeric',
            'satuan_bahan' => 'required'
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        $bahan = Bahan::create($storeData);//menambah data Bahan baru
        return response([
            'message' => 'Add Bahan Succes',
            'data' => $bahan,
        ], 200); //return data Bahan baru dalam bentuk json
    }

    //method untuk mengubah 1 data Bahan (update)
    public function update(Request $request, $id)
    {
        $bahan = Bahan::find($id); //mencari data Bahan berdasarkan id
        if (is_null($bahan)) {
            return response([
                'message' => 'Bahan Not Found',
                'data' => null
            ], 404);
        } //return message saat data Bahan tidak ditemukan

        $updateData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($updateData, [
            'nama_bahan' => ['max:60', Rule::unique('bahans')->ignore($bahan)],
            'jenis_bahan' => '',
            'total_berat_bersih' => 'numeric',
            'ukuran_porsi' => 'numeric',
            'satuan_bahan' => ''
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        $bahan->nama_bahan                = $updateData['nama_bahan'];
        $bahan->jenis_bahan               = $updateData['jenis_bahan'];
        $bahan->total_berat_bersih        = $updateData['total_berat_bersih'];
        $bahan->ukuran_porsi              = $updateData['ukuran_porsi'];
        $bahan->satuan_bahan              = $updateData['satuan_bahan'];

        if ($bahan->save()) {
            return response([
                'message' => 'Update Bahan Success',
                'data' => $bahan,
            ], 200);
        } //return data Bahan yang telah diedit dalam bentuk json
        return response([
            'message' => 'Update Bahan Failed',
            'data' => null,

        ], 400); //return message saat Bahan gagal diedit
    }

    //method untuk menghapus 1 data Bahan (delete)
    public function destroy($id)
    {
        $bahan = Bahan::find($id); //mencari data Bahan berdasarkan id

        if (is_null($bahan)) {
            return response([
                'message' => 'Bahan Not Found',
                'data' => null
            ], 404);
        } //return message saat data Bahan tidak ditemukan

        if ($bahan->delete()) {
            return response([
                'message' => 'Delete Bahan Succes',
                'data' => $bahan,
            ], 200);
        } //return message saat berhasil menghapus data Bahan
        return response([
            'message' => 'Delete Bahan Failed',
            'data' => null
        ], 400); //return message saat gagal menghapus data Bahan
    }

    //method untuk soft Delete 1 data Bahan (update)
    public function delete($id)
    {
        $bahan = Bahan::find($id); //mencari data Bahan berdasarkan id
        if (is_null($bahan)) {
            return response([
                'message' => 'Bahan Not Found',
                'data' => null
            ], 404);
        } //return message saat data Bahan tidak ditemukan

        $bahan->is_Deleted = 1;

        if ($bahan->save()) {
            return response([
                'message' => 'Delete Bahan Succes',
                'data' => $bahan,
            ], 200);
        } //return data Bahan yang telah diedit dalam bentuk json
        return response([
            'message' => 'Delete Bahan Failed',
            'data' => null
        ], 400); //return message saat Bahan gagal diedit
    }

    //method untuk soft Delete 1 data Bahan (update)
    public function restore($id)
    {
        $bahan = Bahan::find($id); //mencari data Bahan berdasarkan id
        if (is_null($bahan)) {
            return response([
                'message' => 'Bahan Not Found',
                'data' => null
            ], 404);
        } //return message saat data Bahan tidak ditemukan

        $bahan->is_Deleted = 0;

        if ($bahan->save()) {
            return response([
                'message' => 'Restore Bahan Succes',
                'data' => $bahan,
            ], 200);
        } //return data Bahan yang telah diedit dalam bentuk json
        return response([
            'message' => 'Restore Bahan Failed',
            'data' => null
        ], 400); //return message saat Bahan gagal diedit
    }
}
