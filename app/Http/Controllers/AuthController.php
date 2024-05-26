<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
/**
* @OA\Post(
* path="/api/register",
* operationId="Register",
* tags={"Register"},
* summary="User Register",
* description="User Register here",
*     @OA\RequestBody(
*         @OA\JsonContent(),
*         @OA\MediaType(
*            mediaType="multipart/form-data",
*            @OA\Schema(
*               type="object",
*               required={"fullname","username","email","tel","role", "password", "password_confirmation"},
*               @OA\Property(property="fullname", type="text"),
*               @OA\Property(property="username", type="text"),
*               @OA\Property(property="tel", type="text"),
*               @OA\Property(property="role", type="text"),
*               @OA\Property(property="email", type="text"),
*               @OA\Property(property="password", type="password"),
*               @OA\Property(property="password_confirmation", type="password")
*            ),
*        ),
*    ),
*      @OA\Response(
*          response=201,
*          description="Register Successfully",
*          @OA\JsonContent()
*       ),
*      @OA\Response(
*          response=200,
*          description="Register Successfully",
*          @OA\JsonContent()
*       ),
*      @OA\Response(
*          response=422,
*          description="Unprocessable Entity",
*          @OA\JsonContent()
*       ),
*      @OA\Response(response=400, description="Bad request"),
*      @OA\Response(response=404, description="Resource Not Found"),
* )
*/

    // Register User
    public function register(Request $request)
    {

        // Validate field
        $fields = $request->validate([
            'fullname' => 'required|string',
            'username' => 'required|string',
            'email' => 'required|string|unique:users,email',
            'password' => 'required|string|confirmed',
            'tel' => 'required',
            'role' => 'required|integer',
        ]);

        // Create user
        $user = User::create([
            'fullname' => $fields['fullname'],
            'username' => $fields['username'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'tel' => $fields['tel'],
            'role' => $fields['role'],
        ]);

        $response = [
            'status' => true,
            'message' => "User registered successfully",
            'user' => $user,
        ];

        return response($response, 201);
    }

    /**
* @OA\Post(
*     path="/api/login",
*     operationId="Login",
*     tags={"Login"},
*     summary="User Login",
*     description="User Login here",
*     @OA\RequestBody(
*         required=true,
*         @OA\MediaType(
*            mediaType="multipart/form-data",
*            @OA\Schema(
*               type="object",
*               required={"email", "password"},
*               @OA\Property(property="email", type="string", example="sanjay@gmail.com"),
*               @OA\Property(property="password", type="string", example="123456"),
*            ),
*        ),
*        @OA\MediaType(
*            mediaType="application/json",
*            @OA\Schema(
*               type="object",
*               required={"email", "password"},
*               @OA\Property(property="email", type="string", example="sanjay@gmail.com"),
*               @OA\Property(property="password", type="string", example="123456"),
*            ),
*        ),
*    ),
*    @OA\Response(
*        response=201,
*        description="Login Successfully",
*        @OA\JsonContent()
*    ),
*    @OA\Response(
*        response=200,
*        description="Login Successfully",
*        @OA\JsonContent()
*    ),
*    @OA\Response(
*        response=422,
*        description="Unprocessable Entity",
*        @OA\JsonContent()
*    ),
*    @OA\Response(response=400, description="Bad request"),
*    @OA\Response(response=404, description="Resource Not Found"),
* )
*/
    // Login User
    public function login(Request $request)
    {
        // Validate field
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        // Check email
        $user = User::where('email', $fields['email'])->first();

        // Check password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'status' => false,
                'message' => 'Login failed',
            ], 401);
        } else {

            // ลบ token เก่าออกแล้วค่อยสร้างใหม่
            $user->tokens()->delete();

            // Create token
            $token = $user->createToken($request->userAgent(), ["$user->role"])->plainTextToken;

            $response = [
                'status' => true,
                'message' => 'Login successfully',
                'user' => $user,
                'token' => $token,
            ];

            return response($response, 201);
        }

    }

// Refresh Token
    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        $token = $user->createToken($request->userAgent(), ["$user->role"])->plainTextToken;
        $response = [
            'status' => true,
            'message' => 'Token refreshed',
            'user' => $user,
            'token' => $token,
        ];
        return response($response, 201);
    }

    // Logout User
    public function logout(Request $request){
        auth()->user()->tokens()->delete();
        return [
            'status' => true,
            'message' => 'Logged out'
        ];
    }

}
