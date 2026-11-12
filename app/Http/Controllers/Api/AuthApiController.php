<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthLoginRequest;
use App\Http\Requests\AuthRegisterRequest;
use App\Http\Resources\NguoiDungResource;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthApiController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function register(AuthRegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'message' => 'Đăng ký thành công. Vui lòng đăng nhập bằng tài khoản vừa tạo.',
            'user' => new NguoiDungResource($user),
            'redirect' => route('login'),
        ], 201);
    }

    public function login(AuthLoginRequest $request): JsonResponse
    {
        try {
            $user = $this->authService->login(
                $request->string('identifier')->toString(),
                $request->string('password')->toString(),
                $request->boolean('remember'),
                $request->ip(),
            );
        } catch (AuthenticationException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }

        $request->session()->regenerate();

        return response()->json([
            'message' => 'Đăng nhập thành công.',
            'user' => new NguoiDungResource($user),
            'redirect' => route('home'),
        ]);
    }

    public function me(): JsonResponse
    {
        $user = Auth::guard('web')->user();

        abort_if($user === null, 401, 'Bạn chưa đăng nhập.');

        return response()->json(['user' => new NguoiDungResource($user)]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Đã đăng xuất.']);
    }
}
