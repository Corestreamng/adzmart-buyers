<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MailingList;

class MailingListController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['subscribe']]);
    }

    public function subscribe(Request $request)
    {
        try {
            DB::beginTransaction();
            $request->validate([
                'email' => 'required|email|unique:mailing_lists,email'
            ]);
            $email = $request->email;
            $sub = new MailingList;
            $sub->email = $email;
            $sub->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'user subscribed successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), ['exception' => $e]);
            return response()->json([
                'status' => 'failure',
                'message' => "An error occured"
            ], 500);
        }
    }

    public function unsubscribe(Request $request)
    {
        try {
            DB::beginTransaction();
            $email = Auth::user()->email;
            $sub = MailingList::where('email', $email)->first();
            // dd($email);
            $sub->subscribed = false;
            $sub->update();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'user unsubscribed successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), ['exception' => $e]);
            return response()->json([
                'status' => 'failure',
                'message' => "An error occured"
            ], 500);
        }
    }
}
