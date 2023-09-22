<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PasswordReset;
use App\Mail\ResetPasswordMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'sendResetLinkEmail', 'resetPassword']]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        $token = Auth::attempt($credentials);
        if (!$token) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        if ($user->blocked == false) {
            return response()->json([
                'status' => 'success',
                'user' => $user,
                'authorisation' => [
                    'token' => $token,
                    'type' => 'bearer',
                ]
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User Blocked',
            ], 401);
        }
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:buyers',
            'password' => 'required|string|min:6',
            'business_name' => 'nullable|min:2|max:255',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'business_name' => $request->business_name ?? null
        ]);

        $token = Auth::login($user);
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
        Auth::logout();
        return response()->json([
            'status' => 'success',
            'message' => 'Successfully logged out',
        ]);
    }

    public function getUserProfile()
    {
        $user = Auth::user();

        if ($user) {
            return response()->json([
                'status' => 'success',
                'message' => 'User profile fetched successfully',
                'user' => $user,
            ]);
        } else {
            return response()->json([
                'status' => 'failure',
                'message' => 'User profile fetch failed',
            ], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'business_name' => 'nullable|min:2|max:255',
            'password' => 'nullable|min:6|confirmed'
        ]);

        $user = Auth::id();

        $user = User::find($user);


        $user = $user->update([
            'name' => $request->name,
            'business_name' => $request->business_name ?? null
        ]);

        if (isset($request->password)) {
            User::find(Auth::id())->update(['password' => Hash::make($request->password)]);
        }

        if ($user) {
            $user = Auth::id();

            $user = User::find($user);
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

    public function updateProfilePic(Request $request)
    {
        $request->validate([
            'pic' => 'required|file|max:10240',
        ]);

        $user = Auth::id();

        $user = User::find($user);

        // Store the uploaded file
        $uploadedFile = $request->file('pic');
        $filename = time() . '_' . $uploadedFile->getClientOriginalName();
        $path = $uploadedFile->storeAs('local', $filename);


        $user = $user->update([
            'pic' => $path,
        ]);

        if ($user && $path) {
            return response()->json([
                'status' => 'success',
                'message' => 'User profile picture updated successfully',
                'user' => Auth::user(),
            ]);
        } else {
            return response()->json([
                'status' => 'failure',
                'message' => 'User profile picture update failed',
            ], 500);
        }
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::user(),
            'authorisation' => [
                'token' => Auth::refresh(),
                'type' => 'bearer',
            ]
        ]);
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

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

        $user = User::where('email', $passwordReset->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        $passwordReset->delete();

        return response()->json(['message' => 'Password reset successfully']);
    }
}
