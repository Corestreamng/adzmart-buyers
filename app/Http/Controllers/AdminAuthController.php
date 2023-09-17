<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\PasswordReset;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api-admin', ['except' => ['login', 'register', 'sendResetLinkEmail', 'resetPassword']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);
        $credentials = $request->only('email', 'password');

        $token = Auth::guard('api-admin')->attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::guard('api-admin')->user();
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:admins',
            'password' => 'required|string|min:6',
        ]);

        $user = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = Auth::guard('api-admin')->login($user);
        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorisation' => [
                'token' => $token,
                'type' => 'bearer',
            ]
        ]);
    }

    public function logout()
    {
        Auth::guard('api-admin')->logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|min:6|confirmed'
        ]);

        $user = Auth::guard('api-admin')->id();

        $user = Admin::find($user);


        $user = $user->update([
            'name' => $request->name,
        ]);

        if (isset($request->password)) {
            Admin::find(Auth::guard('api-admin')->id())->update(['password' => Hash::make($request->password)]);
        }


        if ($user) {
            $user = Auth::guard('api-admin')->id();

            $user = Admin::find($user);
            return response()->json([
                'status' => 'success',
                'message' => 'User profile updated successfully',
                'user' => $user,
            ]);
        } else {
            return response()->json([
                'status' => 'failure',
                'message' => 'User profile update failed',
            ], 500);
        }
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::guard('api-admin')->user(),
            'authorisation' => [
                'token' => Auth::guard('api-admin')->refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = Admin::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            ['email' => $user->email, 'token' => Str::random(60)]
        );

        if ($passwordReset) {
            $this->sendResetEmail($user, $passwordReset->token);

            return response()->json(['message' => 'Reset email sent']);
        }

        return response()->json(['message' => 'Unable to send reset email'], 500);
    }

    private function sendResetEmail($user, $token)
    {
        $resetUrl = 'https://adzmart.com/reset-pass?token=' . $token;

        Mail::to($user->email)->send(new ResetPasswordMail($resetUrl));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $passwordReset = PasswordReset::where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$passwordReset) {
            return response()->json(['message' => 'Invalid token'], 400);
        }

        $user = Admin::where('email', $passwordReset->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        $passwordReset->delete();

        return response()->json(['message' => 'Password reset successfully']);
    }
}
