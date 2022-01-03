<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\User;
use Validator;

class PegawaiController extends Controller
{
    //method untuk menampilkan semua data pegawai (read)
    public function getAll()
    {
        $pegawai = User::where('jabatan_pegawai', 'NOT LIKE', 'admin')
                        ->get(); //mengambil semua data pegawai
                
        $pegawai->makeHidden(['created_at','updated_at']);

        if (count($pegawai) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $pegawai
            ], 200);
        } //return data semua pegawai dalam bentuk json

        return response([
            'message' => 'Pegwai Empty',
            'data' => null
        ], 404); //return message data pegawai kosong
    }

    //method untuk menampilkan semua data pegawai yang belum dihapus (read)
    public function getCustom($status_pegawai)
    {
        $pegawai = User::where('status_pegawai', $status_pegawai)
                        ->where('jabatan_pegawai', 'NOT LIKE', 'admin')
                        ->get(); //mengambil semua data pegawai yang belum dihapus
                
        $pegawai->makeHidden(['created_at','updated_at']);

        if (count($pegawai) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $pegawai
            ], 200);
        } //return data semua pegawai dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data pegawai kosong
    }

    //method untuk menampilkan 1 data pegawai 
    public function getOne($id)
    {
        $pegawai = User::find($id); //mencari data pegawai berdasarkan id

        $pegawai->makeHidden(['created_at','updated_at']);

        if (!is_null($pegawai)) {
            return response([
                'message' => 'Retrive Pegawai Success',
                'data' => $pegawai
            ], 200);
        } //return data semua pegawai dalam bentuk json

        return response([
            'message' => 'Pegawai Not Found',
            'data' => null
        ], 404); //return message saat data pegawai tidak ditemukan
    }

    //method untuk menambah 1 data pegawai baru(create)
    public function store(Request $request)
    {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'nama_pegawai' => 'required|max:60',
            'gender_pegawai' => 'required',
            'telepon_pegawai' => 'required|numeric|digits_between:10,13|starts_with:08',
            'jabatan_pegawai' => 'required',
            'tanggal_bergabung_pegawai' => 'required|date_format:Y-m-d',
            'foto_pegawai' => 'nullable|file|image',
            'email' => 'required|email:rfc,dns|unique:users',
            'password' => 'required'
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        if (!is_null($request->file('foto_pegawai'))) {
            $file          = $request->file('foto_pegawai');
            $nama_file     = time() . "_" . $file->getClientOriginalName();
            $tujuan_upload = 'data_pegawai';
            $file->move($tujuan_upload, $nama_file);
        } else {
            $nama_file = 'avatar.png';
        }

        $pegawai = new User();
        $pegawai->nama_pegawai              = $storeData['nama_pegawai'];
        $pegawai->gender_pegawai            = $storeData['gender_pegawai'];
        $pegawai->telepon_pegawai           = $storeData['telepon_pegawai'];
        $pegawai->jabatan_pegawai           = $storeData['jabatan_pegawai'];
        $pegawai->tanggal_bergabung_pegawai = $storeData['tanggal_bergabung_pegawai'];
        $pegawai->foto_pegawai              = $nama_file;
        $pegawai->email                     = $storeData['email'];
        $pegawai->password                  = bcrypt($request->password);

        $pegawai->save();

        //$pegawai = User::create($storeData);//menambah data pegawai baru
        return response([
            'message' => 'Add Pegawai Succes',
            'data' => $pegawai,
        ], 200); //return data pegawai baru dalam bentuk json
    }

    //method untuk mengubah 1 data pegawai (update)
    public function update(Request $request, $id)
    {
        $pegawai = User::find($id); //mencari data pegawai berdasarkan id
        if (is_null($pegawai)) {
            return response([
                'message' => 'Pegawai Not Found',
                'data' => null
            ], 404);
        } //return message saat data pegawai tidak ditemukan

        $updateData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($updateData, [
            'nama_pegawai' => 'max:60',
            'gender_pegawai' => '',
            'telepon_pegawai' => 'numeric|digits_between:10,13|starts_with:08',
            'jabatan_pegawai' => '',
            'tanggal_bergabung_pegawai' => 'date_format:Y-m-d',
            'foto_pegawai' => 'nullable|file|image',
            'email' => ['email:rfc,dns', Rule::unique('users')->ignore($pegawai)]
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        if (!is_null($request->file('foto_pegawai'))) {
            $file          = $request->file('foto_pegawai');
            $nama_file     = time() . "_" . $file->getClientOriginalName();
            $tujuan_upload = 'data_pegawai';
            $file->move($tujuan_upload, $nama_file);

            $pegawai->foto_pegawai     = $nama_file;
        }

        $pegawai->nama_pegawai              = $updateData['nama_pegawai'];
        $pegawai->gender_pegawai            = $updateData['gender_pegawai'];
        $pegawai->telepon_pegawai           = $updateData['telepon_pegawai'];
        $pegawai->jabatan_pegawai           = $updateData['jabatan_pegawai'];
        $pegawai->tanggal_bergabung_pegawai = $updateData['tanggal_bergabung_pegawai'];
        $pegawai->email                     = $updateData['email'];

        if ($pegawai->save()) {
            return response([
                'message' => 'Update Pegawai Success',
                'data' => $pegawai,
            ], 200);
        } //return data pegawai yang telah diedit dalam bentuk json
        return response([
            'message' => 'Update Pegawai Failed',
            'data' => null,

        ], 400); //return message saat pegawai gagal diedit
    }

    //method untuk mengubah 1 data pegawai (update)
    public function updateAccount(Request $request, $id)
    {
        $pegawai = User::find($id); //mencari data pegawai berdasarkan id
        if (is_null($pegawai)) {
            return response([
                'message' => 'Pegawai Not Found',
                'data' => null
            ], 404);
        } //return message saat data pegawai tidak ditemukan

        $updateData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($updateData, [
            'email' => ['email:rfc,dns', Rule::unique('users')->ignore($pegawai)],
            'password' => ''
        ]); //membuat rule validasi input

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        if (!is_null($request->email)) {
            $pegawai->email = $updateData['email'];
        }
        if (!is_null($request->password)) {
            $pegawai->password   = bcrypt($request->password);
        }

        if ($pegawai->save()) {
            return response([
                'message' => 'Update Account Pegawai Success',
                'data' => $pegawai,
            ], 200);
        } //return data pegawai yang telah diedit dalam bentuk json
        return response([
            'message' => 'Update Account Pegawai Failed',
            'data' => null,

        ], 400); //return message saat pegawai gagal diedit
    }

    //method untuk mengubah 1 data pegawai (update)
    public function updateStatus(Request $request, $id)
    {
        $pegawai = User::find($id); //mencari data pegawai berdasarkan id
        if (is_null($pegawai)) {
            return response([
                'message' => 'Pegawai Not Found',
                'data' => null
            ], 404);
        } //return message saat data pegawai tidak ditemukan

        if ($pegawai->status_pegawai == 1) {
            $pegawai->status_pegawai = 0;
        } else if ($pegawai->status_pegawai == 0) {
            $pegawai->status_pegawai = 1;
        }

        if ($pegawai->save()) {
            return response([
                'message' => 'Update Status Pegawai Success',
                'data' => $pegawai,
            ], 200);
        } //return data pegawai yang telah diedit dalam bentuk json
        return response([
            'message' => 'Update Status Pegawai Failed',
            'data' => null,
        ], 400); //return message saat pegawai gagal diedit
    }
}
