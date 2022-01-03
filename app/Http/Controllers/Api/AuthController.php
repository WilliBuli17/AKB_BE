<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Validator;

class AuthController extends Controller
{
    //method login ke sistem
    public function login(Request $request)
    {
        $loginData = $request->all();
        $validate = Validator::make($loginData, [
            'email' => 'required|email:rfc,dns',
            'password' => 'required'
        ]); //mengambil data dari inputan user

        if ($validate->fails()) {
            return response(['message' => $validate->errors()], 400);
        }

        if (!Auth::attempt($loginData)) {
            return response(['message' => 'Email Atau Password Tidak Valid'], 401);
        }

        $user = Auth::user();

        if ($user->status_pegawai == 0) {
            return response([
                'message' => 'Akun Anda Telah Di Nonaktifkan',
            ], 401);
        } else {
            //membuat akses token
            $token = $user->createToken('Authentication Token')->accessToken; //generate token

            return response([
                'message' => 'Authenticated',
                'user' => $user,
                'token_type' => 'Bearer',
                'access_token' => $token
            ]);
        }
    }

    //keluar dari sisem
    public function logout(Request $request)
    {
        //menghapus token
        $request->user()->token()->revoke();
        return response([
            'message' => 'Successfully logged out'
        ]);
    }

    //method untuk menampilkan user aktif
    public function show($id)
    {
        $user = User::find($id); //mencari data user berdasarkan id

        if (!is_null($user)) {
            return response([
                'message' => 'Retrive User Success',
                'data' => $user
            ], 200);
        } //return data semua user dalam bentuk json

        return response([
            'message' => 'User Not Found',
            'data' => null
        ], 404); //return message saat data user tidak ditemukan
    }
}
