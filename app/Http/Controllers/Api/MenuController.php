<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator; //import library untuk validasi
use App\Menu; //import modal Menu

class MenuController extends Controller
{
    //method untuk menampilkan semua data Menu (read)
    public function getAll()
    {
        $menu = Menu::all(); //mengambil semua data Menu

        $menu->makeHidden(['created_at','updated_at']);

        if (count($menu) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $menu
            ], 200);
        } //return data semua Menu dalam bentuk json

        return response([
            'message' => 'Menu Empty',
            'data' => null
        ], 404); //return message data Menu kosong
    }

    //method untuk menampilkan semua data Menu yang belum dihapus (read)
    public function getCustomDelete($is_Deleted)
    {
        $menu = Menu::where('is_Deleted', $is_Deleted)
                    ->get(); //mengambil semua data Menu yang belum dihapus

        $menu->makeHidden(['created_at','updated_at']);

        if (count($menu) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $menu
            ], 200);
        } //return data semua Menu dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Menu kosong
    }

    //method untuk menampilkan semua data Menu yang belum dihapus (read)
    public function getCustomStock($is_Deleted, $check)
    {
        if($check == 1){
            $menu = Menu::where('is_Deleted', $is_Deleted)
                        ->where('jumlah_menu_tersedia', '>', 0)
                        ->get(); //mengambil semua data Menu yang belum dihapus
        } else{
            $menu = Menu::where('is_Deleted', $is_Deleted)
                        ->where('jumlah_menu_tersedia', '<=', 0)
                        ->get(); //mengambil semua data Menu yang belum dihapus
        }
        
        $menu->makeHidden(['created_at','updated_at']);

        if (count($menu) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $menu
            ], 200);
        } //return data semua Menu dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Menu kosong
    }

    //method untuk menampilkan semua data Menu yang belum dihapus (read)
    public function getCustomTipe($is_Deleted, $tipe)
    {
        if($tipe == 1){
            $menu = Menu::where('is_Deleted', $is_Deleted)
                        ->where('jumlah_menu_tersedia', '>', 0)
                        ->where('tipe_menu', '=', 'Makanan Utama')
                        ->get(); //mengambil semua data Menu yang belum dihapus
        } else if($tipe == 2){
            $menu = Menu::where('is_Deleted', $is_Deleted)
                        ->where('jumlah_menu_tersedia', '>', 0)
                        ->where('tipe_menu', '=', 'Side Dish')
                        ->get(); //mengambil semua data Menu yang belum dihapus
        } else {
            $menu = Menu::where('is_Deleted', $is_Deleted)
                        ->where('jumlah_menu_tersedia', '>', 0)
                        ->where('tipe_menu', '=', 'Minuman')
                        ->get(); //mengambil semua data Menu yang belum dihapus
        }
        
        $menu->makeHidden(['created_at','updated_at']);

        if (count($menu) > 0) {
            return response([
                'message' => 'Mengambil Menu Success',
                'data' => $menu
            ], 200);
        } //return data semua Menu dalam bentuk json

        return response([
            'message' => 'Menu Kosong',
            'data' => null
        ], 200); //return message data Menu kosong
    }

    //method untuk menampilkan 1 data Menu 
    public function getOne($id)
    {
        $menu = Menu::find($id); //mencari data Menu berdasarkan id

        $menu->makeHidden(['created_at','updated_at']);

        if (!is_null($menu)) {
            return response([
                'message' => 'Retrive Menu Success',
                'data' => $menu
            ], 200);
        } //return data semua Menu dalam bentuk json

        return response([
            'message' => 'Menu Not Found',
            'data' => null
        ], 404); //return message saat data Menu tidak ditemukan
    }

    //method untuk menambah 1 data Menu baru(create)
    public function store(Request $request)
    {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'nama_menu' => 'required|max:60|unique:menus',
            'tipe_menu' => 'required|max:60',
            'deskripsi_menu' => 'required', 
            'satuan_menu' => 'required',
            'harga_menu' => 'required|numeric',
            'jumlah_menu_tersedia' => 'required|numeric',
            'foto_menu' => 'nullable|file|image',
            'id_bahan' => 'required|numeric'
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        if (!is_null($request->file('foto_menu'))) {
            $file          = $request->file('foto_menu');
            $nama_file     = time() . "_" . $file->getClientOriginalName();
            $tujuan_upload = 'data_menu';
            $file->move($tujuan_upload, $nama_file);
        } else {
            $nama_file = 'No_Image.png';
        }

        $menu = new Menu();
        $menu->nama_menu              = $storeData['nama_menu'];
        $menu->tipe_menu              = $storeData['tipe_menu'];
        $menu->deskripsi_menu         = $storeData['deskripsi_menu'];
        $menu->foto_menu              = $nama_file;
        $menu->satuan_menu            = $storeData['satuan_menu'];
        $menu->harga_menu             = $storeData['harga_menu'];
        $menu->jumlah_menu_tersedia   = $storeData['jumlah_menu_tersedia'];
        $menu->id_bahan               = $storeData['id_bahan'];

        $menu->save();

        //$menu = Menu::create($storeData);//menambah data Menu baru
        return response([
            'message' => 'Add Menu Succes',
            'data' => $menu,
        ], 200); //return data Menu baru dalam bentuk json
    }

    //method untuk mengubah 1 data Menu (update)
    public function update(Request $request, $id)
    {
        $menu = Menu::find($id); //mencari data Menu berdasarkan id
        if (is_null($menu)) {
            return response([
                'message' => 'Menu Not Found',
                'data' => null
            ], 404);
        } //return message saat data Menu tidak ditemukan

        $updateData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($updateData, [
            'nama_menu' => ['max:60', Rule::unique('menus')->ignore($menu)],
            'tipe_menu' => 'max:60',
            'deskripsi_menu' => '',
            'satuan_menu' => '',
            'harga_menu' => 'numeric',
            'jumlah_menu_tersedia' => 'numeric',
            'foto_menu' => 'nullable|file|image',
            'id_bahan' => 'numeric'
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        if (!is_null($request->file('foto_menu'))) {
            $file          = $request->file('foto_menu');
            $nama_file     = time() . "_" . $file->getClientOriginalName();
            $tujuan_upload = 'data_menu';
            $file->move($tujuan_upload, $nama_file);

            $menu->foto_menu     = $nama_file;
        }

        $menu->nama_menu              = $updateData['nama_menu'];
        $menu->tipe_menu              = $updateData['tipe_menu'];
        $menu->deskripsi_menu         = $updateData['deskripsi_menu'];
        $menu->satuan_menu            = $updateData['satuan_menu'];
        $menu->harga_menu             = $updateData['harga_menu'];
        $menu->jumlah_menu_tersedia   = $updateData['jumlah_menu_tersedia'];
        $menu->id_bahan               = $updateData['id_bahan'];

        if ($menu->save()) {
            return response([
                'message' => 'Update Menu Success',
                'data' => $menu,
            ], 200);
        } //return data Menu yang telah diedit dalam bentuk json
        return response([
            'message' => 'Update Menu Failed',
            'data' => null,

        ], 400); //return message saat Menu gagal diedit
    }

    //method untuk menghapus 1 data Menu (delete)
    public function destroy($id)
    {
        $menu = Menu::find($id); //mencari data Menu berdasarkan id

        if (is_null($menu)) {
            return response([
                'message' => 'Menu Not Found',
                'data' => null
            ], 404);
        } //return message saat data Menu tidak ditemukan

        if ($menu->delete()) {
            return response([
                'message' => 'Delete Menu Succes',
                'data' => $menu,
            ], 200);
        } //return message saat berhasil menghapus data Menu
        return response([
            'message' => 'Delete Menu Failed',
            'data' => null
        ], 400); //return message saat gagal menghapus data Menu
    }

    //method untuk soft Delete 1 data Menu (update)
    public function delete($id)
    {
        $menu = Menu::find($id); //mencari data Menu berdasarkan id
        if (is_null($menu)) {
            return response([
                'message' => 'Menu Not Found',
                'data' => null
            ], 404);
        } //return message saat data Menu tidak ditemukan

        $menu->is_Deleted = 1;

        if ($menu->save()) {
            return response([
                'message' => 'Delete Menu Succes',
                'data' => $menu,
            ], 200);
        } //return data Menu yang telah diedit dalam bentuk json
        return response([
            'message' => 'Delete Menu Failed',
            'data' => null
        ], 400); //return message saat Menu gagal diedit
    }

    //method untuk soft Delete 1 data Menu (update)
    public function restore($id)
    {
        $menu = Menu::find($id); //mencari data Menu berdasarkan id
        if (is_null($menu)) {
            return response([
                'message' => 'Menu Not Found',
                'data' => null
            ], 404);
        } //return message saat data Menu tidak ditemukan

        $menu->is_Deleted = 0;

        if ($menu->save()) {
            return response([
                'message' => 'Restore Menu Succes',
                'data' => $menu,
            ], 200);
        } //return data Menu yang telah diedit dalam bentuk json
        return response([
            'message' => 'Restore Menu Failed',
            'data' => null
        ], 400); //return message saat Menu gagal diedit
    }
}
