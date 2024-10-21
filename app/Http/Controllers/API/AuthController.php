<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    /**
     * @OA\Post(
     *     path="/register",
     *     tags={"Authentication"},
     *     summary="User register",
     *     description="Creates a new user and returns a JWT token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret")
     *         ),
     *     ),
     *     @OA\Response(response=201, description="User registered successfully."),
     *     @OA\Response(response=400, description="Validation error"),
     * )
     */

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);
        $user->assignRole('user');

        return $this->sendResponse([
            'user' => $user,
            'token' => $token,
            'message' => 'User registered successfully.',
        ], 201);
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Login user and get token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="rmartinez4298@gmail.com"),
     *             @OA\Property(property="password", type="string", example="123456789")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inicio de sesión exitoso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="access_token", type="string", example="eyJ0eXAiOiJKV1QiLCJh..."),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *             @OA\Property(property="expires_in", type="integer", example=3600),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="name", type="string", example="John Doe")
     *             ),
     *             @OA\Property(property="role", type="array",
     *                 @OA\Items(type="string", example="admin")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return $this->sendError(['error' => 'Invalid credentials'], 401);
        }

        return $this->sendResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => JWTAuth::user()->only('name'),
            'role'=> JWTAuth::user()->getRoleNames(),
            'message' => 'Login successful.',
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/logout",
     *     tags={"Authentication"},
     *     summary="Close session",
     *     description="Close the current user session",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Session closed successfully."),
     * )
     */
    public function logout(Request $request)
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return $this->sendResponse(['message' => 'Session closed successfully."),'], 200);
    }
}