<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UnitOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnitOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    /**
     * create an order
     */
    public function createOrder(Request $request){
        $buyer_id = Auth::id();

        
    }
}
