<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use JWTAuth;
use JWTAuthException;

use App\User;

class AuthController extends Controller
{
    public function store(Request $request)
    {
        $this->validate($request,[
            'name'=>'required',
            'email'=>'required|email',
            'password'=>'required|min:5'
        ]);

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');

        $user = new User([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password)
        ]);

        $credentials = [
            'email' => $email,
            'password' => $password
        ];


        if($user->save())
        {
            $token = null;
            try{
                if(!$token = JWTAuth::attempt($credentials))
                {
                    return response()->json([
                        'msg' => 'Email or Password ar incorrect'
                    ], 404);
                }
            } catch (JWTAuthException $e)
            {
                return response()->json([
                    'msg' => 'failed_to_create_token'
                ],400);
            }
            $user->signin = [
                'href' => 'api/v1/user/signin',
                'method'=>'POST',
                'params' => 'email, password'
            ];

            $response = [
                'msg' => 'User Created',
                'user' => $user,
                'token' => $token
            ];

            return response()->json($response,201);
        }

        $response = [
            'msg' => 'An error occured'
        ];

        return response()->json($response, 404);
    }

    public function signin(Request $request)
    {
        $this->validate($request,[
            'email'=>'required|email',
            'password'=>'required|min:5'
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        if($user = User::where('email', $email)->first())
        {
            $credentials = [
                'email'=> $email,
                'password' => $password
            ];

            $token = null;
            try{
                if(!$token = JWTAuth::attempt($credentials))
                {
                    return response()->json([
                        'msg' => 'Email or Password are incorrect'
                    ], 404);
                }
            } catch(JWTAuthException $e)
            {
                return response()->json([
                    'msg' => 'failed_to_create_token',
                ],404);
            }

            $response = [
                'msg' => 'User Sigin',
                'user' =>$user,
                'token' => $token
            ];

            return response()->json($response, 201);
        }
        $response = [
            'msg' => 'An error occured'
        ];

        return response()->json($response,404);
    }
}