<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

use Tymon\JWTAuth\Exception\JWTException;
use JWTAuth;
use App\User;

class AuthController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:5'
        ]);

        if($validator->fails())
            return response()->json($validator->errors(), 404);

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');
        $role = 2;
        $user = new User([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
            'role_id' => $role
        ]);

        if ($user->save()) {
            $user->signin = [
                'href' => 'api/v1/user/signin',
                'method' => 'POST',
                'params' => 'email, password'
            ];
            $response = [
                'msg' => 'User created',
                'user' => $user
            ];
            return response()->json($response, 201);
        }

        $response = [
            'msg' => 'An error occurred'
        ];

        return response()->json($response, 404);
    }

    public function signin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if($validator->fails())
            return response()->json($validator->errors(), 404);
        
        $credentials = $request->only('email', 'password');
        
        try {
            if(! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['msg' => 'Invalid credentials'], 401);
            }
        } catch(JWTException $e) {
            return response()->json(['msg' => 'Could not create token'], 500);
        }

        return response()->json(['token' => $token]);
    }
}
