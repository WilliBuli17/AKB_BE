<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator; //import library untuk validasi
use App\Kartu; //import modal Kartu

class KartuController extends Controller
{
    //method untuk menampilkan semua data Kartu yang belum dihapus (read)
    public function getKartuCustom($tipe_kartu)
    {
        $kartu = Kartu::where('tipe_kartu', $tipe_kartu)
                    ->get(); //mengambil semua data Kartu yang belum dihapus

        $kartu->makeHidden(['created_at','updated_at']);

        if (count($kartu) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $kartu
            ], 200);
        } //return data semua Kartu dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Kartu kosong
    }

    ///method untuk menambah 1 data Kartu baru(create)
    public function store(Request $request)
    {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'nama_pemilik_kartu' => 'nullable',
            'tipe_kartu' => 'required',
            'nomer_kartu' => 'required',
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        $kartu = Kartu::create($storeData);//menambah data Kartu baru
        return response([
            'message' => 'Add Kartu Succes',
            'data' => $kartu,
        ], 200); //return data Kartu baru dalam bentuk json
    }
}
