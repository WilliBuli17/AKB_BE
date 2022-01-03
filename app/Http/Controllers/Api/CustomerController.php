<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator; //import library untuk validasi
use App\Customer; //import modal Customer

class CustomerController extends Controller
{
    //method untuk menampilkan semua data Customer (read)
    public function getAll()
    {
        $customer = Customer::all(); //mengambil semua data Customer

        $customer->makeHidden(['created_at','updated_at']);

        if (count($customer) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $customer
            ], 200);
        } //return data semua Customer dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Customer kosong
    }

    //method untuk menampilkan semua data Customer yang belum dihapus (read)
    public function getCustom($is_Deleted)
    {
        $customer = Customer::where('is_Deleted', $is_Deleted)->get(); //mengambil semua data Customer yang belum dihapus

        $customer->makeHidden(['created_at','updated_at']);

        if (count($customer) > 0) {
            return response([
                'message' => 'Retrive All Success',
                'data' => $customer
            ], 200);
        } //return data semua Customer dalam bentuk json

        return response([
            'message' => 'Empty',
            'data' => null
        ], 404); //return message data Customer kosong
    }

    //method untuk menampilkan 1 data Customer
    public function getOne($id)
    {
        $customer = Customer::find($id); //mencari data Customer berdasarkan id

        $customer->makeHidden(['created_at','updated_at']);

        if (!is_null($customer)) {
            return response([
                'message' => 'Retrive Customer Success',
                'data' => $customer
            ], 200);
        } //return data semua Customer dalam bentuk json

        return response([
            'message' => 'Customer Not Found',
            'data' => null
        ], 404); //return message saat data Customer tidak ditemukan
    }

    //method untuk menambah 1 data Customer baru(create)
    public function store(Request $request)
    {
        $storeData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($storeData, [
            'nama_customer' => 'required|max:60|unique:customers',
            'email_customer' => 'nullable|email:rfc,dns',
            'telepon_customer' => 'nullable|numeric|digits_between:10,13|starts_with:08'
        ]); //membuat rule validasi input

        if ($validate->fails()){
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        $customer = new Customer();
        $customer->nama_customer   = $storeData['nama_customer'];
        if (!is_null($request->email_customer)) {
            $customer->email_customer   = $storeData['email_customer'];
        } 

        if (!is_null($request->telepon_customer)) {
            $customer->telepon_customer   = $storeData['telepon_customer'];
        } 

        $customer->save();

        //$customer = Customer::create($storeData); //menambah data Customer baru
        return response([
            'message' => 'Add Customer Succes',
            'data' => $customer,
        ], 200); //return data Customer baru dalam bentuk json
    }

    //method untuk menghapus 1 data Customer (delete)
    public function destroy($id)
    {
        $customer = Customer::find($id); //mencari data Customer berdasarkan id

        if (is_null($customer)) {
            return response([
                'message' => 'Customer Not Found',
                'data' => null
            ], 404);
        } //return message saat data Customer tidak ditemukan

        if ($customer->delete()) {
            return response([
                'message' => 'Delete Customer Succes',
                'data' => $customer,
            ], 200);
        } //return message saat berhasil menghapus data Customer
        return response([
            'message' => 'Delete Customer Failed',
            'data' => null
        ], 400); //return message saat gagal menghapus data Customer
    }

    //method untuk mengubah 1 data Customer (update)
    public function update(Request $request, $id)
    {
        $customer = Customer::find($id); //mencari data Customer berdasarkan id
        if (is_null($customer)) {
            return response([
                'message' => 'Customer Not Found',
                'data' => null
            ], 404);
        } //return message saat data Customer tidak ditemukan

        $updateData = $request->all(); //mengambil semua input dari api client
        $validate = Validator::make($updateData, [
            'nama_customer' => ['max:60', Rule::unique('customers')->ignore($customer)],
            'email_customer' => 'nullable|email:rfc,dns',
            'telepon_customer' => 'nullable|numeric|digits_between:10,13|starts_with:08'
        ]); //membuat rule validasi input

        if ($validate->fails()){
            return response(['message' => $validate->errors()], 400); //return error invalid input
        }

        if (!is_null($request->nama_customer)) {
            $customer->nama_customer   = $updateData['nama_customer'];
        }

        if (!is_null($request->email_customer)) {
            $customer->email_customer   = $updateData['email_customer'];
        }
        else{
            $customer->email_customer   = null;
        }

        if (!is_null($request->telepon_customer)) {
            $customer->telepon_customer   = $updateData['telepon_customer'];
        } 
        else {
            $customer->telepon_customer   = null;
        }

        if ($customer->save()) {
            return response([
                'message' => 'Update Customer Succes',
                'data' => $customer,
            ], 200);
        } //return data Customer yang telah diedit dalam bentuk json
        return response([
            'message' => 'Update Customer Failed',
            'data' => null
        ], 400); //return message saat Customer gagal diedit
    }

    //method untuk soft Delete 1 data Customer (update)
    public function delete($id)
    {
        $customer = Customer::find($id); //mencari data Customer berdasarkan id
        if (is_null($customer)) {
            return response([
                'message' => 'Customer Not Found',
                'data' => null
            ], 404);
        } //return message saat data Customer tidak ditemukan

        $customer->is_Deleted = 1;

        if ($customer->save()) {
            return response([
                    'message' => 'Delete Customer Succes',
                    'data' => $customer,
                ], 200);
        } //return data Customer yang telah diedit dalam bentuk json
        return response([
            'message' => 'Delete Customer Failed',
            'data' => null
        ], 400); //return message saat Customer gagal diedit
    }

    //method untuk soft Delete 1 data Customer (update)
    public function restore($id)
    {
        $customer = Customer::find($id); //mencari data Customer berdasarkan id
        if (is_null($customer)) {
            return response([
                'message' => 'Customer Not Found',
                'data' => null
            ], 404);
        } //return message saat data Customer tidak ditemukan

        $customer->is_Deleted = 0;

        if ($customer->save()) {
            return response([
                'message' => 'Restore Customer Succes',
                'data' => $customer,
            ], 200);
        } //return data Customer yang telah diedit dalam bentuk json
        return response([
            'message' => 'Restore Customer Failed',
            'data' => null
        ], 400); //return message saat Customer gagal diedit
    }
}
