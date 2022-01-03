<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator; //import library untuk validasi
use App\Reservasi; //import modal Reservasi
use App\Table;
use Illuminate\Support\Facades\DB;

class ReservasiController extends Controller
{
    //method untuk menampilkan semua data Reservasi (read)
    public function getAll()
    {
        $reservasi = Reservasi::all(); //mengambil semua data Reservasi

        $reservasi->makeHidden(['created_at','updated_at']);

        if (count($reservasi) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $reservasi
            ], 200);
        } //return data semua Reservasi dalam bentuk json

        return response([
            'message' => 'Reservasi Empty',
            'data' => null
        ], 404); //return message data Reservasi kosong
    }

    //method untuk menampilkan semua data Reservasi yang belum dihapus (read)
    public function getCustomDelete($is_Deleted)
    {
        $reservasi = Reservasi::join('customers', 'reservasis.id_customer', '=', 'customers.id')
                                ->join('users', 'reservasis.id_pegawai', '=', 'users.id')
                                ->where('reservasis.is_Deleted', $is_Deleted)
                                ->get(['customers.nama_customer','reservasis.*', 'users.nama_pegawai']); 

        $reservasi->makeHidden(['created_at','updated_at']);
        
        if (count($reservasi) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $reservasi
            ], 200);
        } //return data semua Reservasi dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Reservasi kosong
    }

    //method untuk menampilkan semua data Reservasi yang tersedia(read)
    public function getCustomStatus($status_reservasi, $is_Deleted)
    {
        $reservasi = Reservasi::join('customers', 'reservasis.id_customer', '=', 'customers.id')
                                ->join('users', 'reservasis.id_pegawai', '=', 'users.id')
                                ->where('status_reservasi', $status_reservasi)
                                ->where('reservasis.is_Deleted', $is_Deleted)
                                ->get(['customers.nama_customer','reservasis.*', 'users.nama_pegawai']); //mengambil semua data Reservasi yang tersedia

        $reservasi->makeHidden(['created_at','updated_at']);

        if (count($reservasi) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $reservasi
            ], 200);
        } //return data semua Reservasi dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Reservasi kosong
    }

    //method untuk menampilkan semua data Reservasi yang tersedia(read)
    public function getCustomByTanggalDanSesi($id, $tanggal_reservasi, $sesi_reservasi, $is_Deleted)
    {        $first = DB::table('tables')
                        ->select(['tables.id', 'tables.status_table', 
                                    DB::raw('null as id_table'),
                                    DB::raw('null as nama_customer')])
                        ->whereNotIn('id', function ($query) use ($id, $tanggal_reservasi, $sesi_reservasi, $is_Deleted) {
                                    $query->select('id_table')
                                        ->from('reservasis')
                                        ->where('reservasis.id', '!=', $id)
                                        ->where('tanggal_reservasi', $tanggal_reservasi)
                                        ->where('sesi_reservasi', $sesi_reservasi)
                                        ->where('status_reservasi', '!=','2')
                                        ->where('is_Deleted', $is_Deleted);
                                })
                        ->where('is_Deleted', $is_Deleted);
                        /*->get();*/
                            
        $table = Table::join('reservasis', 'reservasis.id_table', '=', 'tables.id')
                            ->join('customers', 'reservasis.id_customer', '=', 'customers.id')
                            ->where('reservasis.id', '!=', $id)
                            ->where('tanggal_reservasi', $tanggal_reservasi)
                            ->where('sesi_reservasi', $sesi_reservasi)
                            ->where('status_reservasi', '!=','2')
                            ->where('reservasis.is_Deleted', $is_Deleted)
                            ->union($first)
                            ->orderBy('id')
                            ->get(['tables.id', 'tables.status_table','reservasis.id_table','customers.nama_customer']);                    

        if (count($table) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $table
            ], 200);
        } //return data semua Table dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Table kosong

        /*$reservasi = Reservasi::where('tanggal_reservasi', $tanggal_reservasi)
                                ->where('sesi_reservasi', $sesi_reservasi)
                                ->where('status_reservasi', '!=','2')
                                ->where('is_Deleted', $is_Deleted)
                                ->get(); //mengambil semua data Reservasi yang tersedia

        $reservasi->makeHidden(['created_at','updated_at']);

        if (count($reservasi) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $reservasi
            ], 200);
        } //return data semua Reservasi dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Reservasi kosong*/
    }    
    
    //method untuk menampilkan 1 data Reservasi 
    public function getOne($id)
    {
        $reservasi = Reservasi::join('customers', 'reservasis.id_customer', '=', 'customers.id')
                                ->where('reservasis.id', $id)
                                ->where('reservasis.status_reservasi', '!=','2')
                                ->get(['customers.nama_customer','reservasis.*']); 

        $reservasi->makeHidden(['created_at','updated_at']);                        

        if (!is_null($reservasi)) {
            return response([
                'message' => 'Retrive Reservasi Success',
                'data' => $reservasi
            ], 200);
        } //return data semua Reservasi dalam bentuk json

        return response([
            'message' => 'Reservasi Not Found',
            'data' => null
        ], 404); //return message saat data Reservasi tidak ditemukan
    }

    //method untuk menambah 1 data reservasi baru(create)
    public function store(Request $request)
    {
        $storeData = $request->all();
        $validate = Validator::make($storeData, [
            'tanggal_reservasi' => 'required|date_format:Y-m-d',
            'sesi_reservasi' => 'required',
            'id_customer' => 'required|numeric',
            'id_table' => 'required|numeric',
            'id_pegawai' => 'required|numeric',
        ]);
        
        if ($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $reservasi = Reservasi::create($storeData);
        return response([
            'message' => 'Add Reservasi Success',
            'data' => $reservasi,
        ], 200);
    }

    //method untuk mengubah 1 data reservasi (update)
    public function update(Request $request, $id)
    {
        $reservasi = Reservasi::find($id);
        if (is_null($reservasi)) {
            return response([
                'message' => 'Reservasi Not Found',
                'data' => null
            ], 404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'tanggal_reservasi' => 'date_format:Y-m-d',
            'sesi_reservasi' => '',
            'id_customer' => 'numeric',
            'id_table' => 'numeric',
            'id_pegawai' => 'numeric',
        ]);
        
        if ($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $reservasi->tanggal_reservasi   = $updateData['tanggal_reservasi'];
        $reservasi->sesi_reservasi      = $updateData['sesi_reservasi'];
        $reservasi->id_customer         = $updateData['id_customer'];
        $reservasi->id_table            = $updateData['id_table'];
        $reservasi->id_pegawai          = $updateData['id_pegawai'];


        if ($reservasi->save()) {
            return response([
                'message' => 'Update Reservasi Succes',
                'data' => $reservasi,
            ], 200);
        }

        return response([
            'message' => 'Update Reservasi Failed',
            'data' => null,
        ], 400);
    }

    //method untuk mengubah 1 data Reservasi (update)
    public function updateStatus($id, $status)
    {
        $reservasi = Reservasi::find($id); //mencari data Reservasi berdasarkan id
        if (is_null($reservasi)) {
            return response([
                'message' => 'Reservasi Not Found',
                'data' => null
            ], 404);
        } //return message saat data Reservasi tidak ditemukan

        $reservasi->status_reservasi = $status;

        if ($reservasi->save()) {
            return response([
                'message' => 'Update Status Reservasi Success',
                'data' => $reservasi,
            ], 200);
        } //return data Reservasi yang telah diedit dalam bentuk json
        return response([
            'message' => 'Update Status Reservasi Failed',
            'data' => null,

        ], 400); //return message saat Reservasi gagal diedit
    }

    //method untuk menghapus 1 data Reservasi (delete)
    public function destroy($id)
    {
        $reservasi = Reservasi::find($id); //mencari data Reservasi berdasarkan id

        if (is_null($reservasi)) {
            return response([
                'message' => 'Reservasi Not Found',
                'data' => null
            ], 404);
        } //return message saat data Reservasi tidak ditemukan

        if ($reservasi->delete()) {
            return response([
                'message' => 'Delete Reservasi Succes',
                'data' => $reservasi,
            ], 200);
        } //return message saat berhasil menghapus data Reservasi
        return response([
            'message' => 'Delete Reservasi Failed',
            'data' => null
        ], 400); //return message saat gagal menghapus data Reservasi
    }

    //method untuk soft Delete 1 data Reservasi (update)
    public function delete($id)
    {
        $reservasi = Reservasi::find($id); //mencari data Reservasi berdasarkan id
        if (is_null($reservasi)) {
            return response([
                'message' => 'Reservasi Not Found',
                'data' => null
            ], 404);
        } //return message saat data Reservasi tidak ditemukan

        $reservasi->is_Deleted = 1;

        if ($reservasi->save()) {
            return response([
                'message' => 'Delete Reservasi Succes',
                'data' => $reservasi,
            ], 200);
        } //return data Reservasi yang telah diedit dalam bentuk json
        return response([
            'message' => 'Delete Reservasi Failed',
            'data' => null
        ], 400); //return message saat Reservasi gagal diedit
    }

    //method untuk soft Delete 1 data Reservasi (update)
    public function restore(Request $request, $id)
    {
        $reservasi = Reservasi::find($id); //mencari data Reservasi berdasarkan id
        if (is_null($reservasi)) {
            return response([
                'message' => 'Reservasi Not Found',
                'data' => null
            ], 404);
        } //return message saat data Reservasi tidak ditemukan

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'tanggal_reservasi' => 'date_format:Y-m-d',
            'sesi_reservasi' => '',
            'id_customer' => 'numeric',
            'id_table' => 'numeric',
            'id_pegawai' => 'numeric',
        ]);
        
        if ($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $reservasi->tanggal_reservasi   = $updateData['tanggal_reservasi'];
        $reservasi->sesi_reservasi      = $updateData['sesi_reservasi'];
        $reservasi->id_customer         = $updateData['id_customer'];
        $reservasi->id_table            = $updateData['id_table'];
        $reservasi->id_pegawai          = $updateData['id_pegawai'];
        $reservasi->is_Deleted          = 0;

        if ($reservasi->save()) {
            return response([
                'message' => 'Restore Reservasi Succes',
                'data' => $reservasi,
            ], 200);
        } //return data Reservasi yang telah diedit dalam bentuk json
        return response([
            'message' => 'Restore Reservasi Failed',
            'data' => null
        ], 400); //return message saat Reservasi gagal diedit
    }
}
