<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Table;
use Validator;
use Illuminate\Support\Facades\DB;

class TableController extends Controller
{
    //method untuk menampilkan semua data Table (read)
    public function getAll()
    {
        $table = Table::all(); //mengambil semua data Table

        $table->makeHidden(['created_at','updated_at']);

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
    }

    //method untuk menampilkan semua data Table yang belum dihapus (read)
    public function getCustomDelete($is_Deleted)
    {
        $table = Table::where('is_Deleted', $is_Deleted)->get(); //mengambil semua data Table yang belum dihapus

        $table->makeHidden(['created_at','updated_at']);

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
    }

    //method untuk menampilkan semua data Table yang tersedia(read)
    public function getCustomStatus($status_table)
    {
        $table = Table::where('status_table', $status_table)->get(); //mengambil semua data Table yang tersedia

        $table->makeHidden(['created_at','updated_at']);

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
    }

    //method untuk menampilkan semua data Table yang tersedia(read)
    public function getMejaByReservasi($tanggal_reservasi, $sesi_reservasi, $status_reservasi, $is_Deleted)
    {
        $first = DB::table('tables')
                        ->select(['tables.id', 'tables.status_table', 
                                    DB::raw('null as id_table'),
                                    DB::raw('null as nama_customer')])
                        ->whereNotIn('id', function ($query) use ($tanggal_reservasi, $sesi_reservasi, $status_reservasi, $is_Deleted) {
                                    $query->select('id_table')
                                        ->from('reservasis')
                                        ->where('tanggal_reservasi', $tanggal_reservasi)
                                        ->where('sesi_reservasi', $sesi_reservasi)
                                        ->where('status_reservasi', $status_reservasi)
                                        ->where('is_Deleted', $is_Deleted);
                                })
                        ->where('is_Deleted', $is_Deleted);
                        /*->get();*/
                            
        $table = Table::join('reservasis', 'reservasis.id_table', '=', 'tables.id')
                            ->join('customers', 'reservasis.id_customer', '=', 'customers.id')
                            ->where('tanggal_reservasi', $tanggal_reservasi)
                            ->where('sesi_reservasi', $sesi_reservasi)
                            ->where('status_reservasi', $status_reservasi)
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
    }

    //method untuk menampilkan 1 data Table
    public function getOne($id)
    {
        $table = Table::find($id); //mencari data Table berdasarkan id

        $table->makeHidden(['created_at','updated_at']);

        if (!is_null($table)) {
            return response([
                'message' => 'Retrive Table Success',
                'data' => $table
            ], 200);
        } //return data semua Table dalam bentuk json

        return response([
            'message' => 'Table Not Found',
            'data' => null
        ], 404); //return message saat data Table tidak ditemukan
    }

    //method untuk menambah 1 data Table baru(create)
    public function store()
    {
        $table = new Table();
        $table->save();

        return response([
            'message' => 'Add Table Succes',
            'data' => $table,
        ], 200); //return data Table baru dalam bentuk json
    }

    //method untuk mengubah 1 data Table (update)
    public function update($id)
    {
        $table = Table::find($id); //mencari data Table berdasarkan id
        if (is_null($table)) {
            return response([
                'message' => 'Table Not Found',
                'data' => null
            ], 404);
        } //return message saat data Table tidak ditemukan

        if ($table->status_table == 1) {
            $table->status_table = 0;
        } else if ($table->status_table == 0) {
            $table->status_table = 1;
        }

        if ($table->save()) {
            return response([
                'message' => 'Update Table Succes',
                'data' => $table,
            ], 200);
        } //return data Table yang telah diedit dalam bentuk json
        return response([
            'message' => 'Update Table Failed',
            'data' => null
        ], 400); //return message saat Table gagal diedit
    }

    //method untuk menghapus 1 data Table (delete)
    public function destroy($id)
    {
        $table = Table::find($id); //mencari data Table berdasarkan id

        if (is_null($table)) {
            return response([
                'message' => 'Table Not Found',
                'data' => null
            ], 404);
        } //return message saat data Table tidak ditemukan

        if ($table->delete()) {
            return response([
                'message' => 'Delete Table Succes',
                'data' => $table,
            ], 200);
        } //return message saat berhasil menghapus data Table
        return response([
            'message' => 'Delete Table Failed',
            'data' => null
        ], 400); //return message saat gagal menghapus data Table
    }

    //method untuk soft Delete 1 data Table (update)
    public function delete($id)
    {
        $table = Table::find($id); //mencari data Table berdasarkan id
        if (is_null($table)) {
            return response([
                'message' => 'Table Not Found',
                'data' => null
            ], 404);
        } //return message saat data Table tidak ditemukan

        $table->is_Deleted = 1;

        if ($table->save()) {
            return response(
                [
                    'message' => 'Delete Table Succes',
                    'data' => $table,
                ],
                200
            );
        } //return data Table yang telah diedit dalam bentuk json
        return response([
            'message' => 'Delete Table Failed',
            'data' => null
        ], 400); //return message saat Table gagal diedit
    }

    //method untuk soft Delete 1 data Table (update)
    public function restore($id)
    {
        $table = Table::find($id); //mencari data Table berdasarkan id
        if (is_null($table)) {
            return response([
                'message' => 'Table Not Found',
                'data' => null
            ], 404);
        } //return message saat data Table tidak ditemukan

        $table->is_Deleted = 0;

        if ($table->save()) {
            return response([
                'message' => 'Restore Table Succes',
                'data' => $table,
            ], 200);
        } //return data Table yang telah diedit dalam bentuk json
        return response([
            'message' => 'Restore Table Failed',
            'data' => null
        ], 400); //return message saat Table gagal diedit
    }
}
