<?php

namespace App\Http\Controllers;

use App\Models\Billboard;
use App\Models\Cinema;
use App\Models\Payment;
use App\Models\PrintUnit;
use App\Models\RadioUnit;
use App\Models\TVUnit;
use Illuminate\Http\Request;
use App\Models\UnitOrder;
use App\Models\UnitOrderBillboardItem;
use App\Models\UnitOrderBillboardItemMedia;
use App\Models\UnitOrderCinemaItem;
use App\Models\UnitOrderCinemaItemMedia;
use App\Models\UnitOrderPrintItem;
use App\Models\UnitOrderPrintItemMedia;
use App\Models\UnitOrderRadioItem;
use App\Models\UnitOrderRadioItemMedia;
use App\Models\UnitOrderTVItem;
use App\Models\UnitOrderTVItemMedia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Rfc4122\UuidV4;

class UnitOrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    /**
     * create an order
     */
    public function createOrder(Request $request)
    {
        try {
            $buyer_id = Auth::id();
            $request->validate([
                'items' => 'required|array',
                'items.*' => 'required|numeric',
                'unit_types' => 'required|array',
                'unit_types.*' => 'required|string|in:tv,radio,print,cinema,billboard',
                'media' => 'nullable|array',
                'media.*' => 'file',
                'desc' => 'nullable|array',
                'desc.*' => 'text',
            ]);

            $items = $request->input('items');
            $unit_types = $request->input('unit_types');
            $media = $request->input('media');
            $descriptions = $request->input('desc');
            DB::beginTransaction();
            $unit_order = new UnitOrder;
            $unit_order->buyer_id = $buyer_id;
            $unit_order->save();
            $total_amount = 0;
            for ($i = 0; $i < count($items); $i++) {
                if ($unit_types[$i] == 'tv') {
                    $tv_item = new UnitOrderTVItem;
                    $tv_item->tv_unit_id = $items[$i];
                    $unit_amount = TVUnit::find($items[$i])->total;
                    if(TVUnit::find($items[$i])->is_sold ==true){
                        return response()->json([
                            'status' => 'failure',
                            'message' => 'some of the units you specified have already been sold'
                        ],400);
                    }
                    $total_amount = $total_amount + $unit_amount;
                    $tv_item->unit_order_id = $unit_order->id;
                    $tv_item->description = $descriptions[$i] ?? null;
                    $tv_item->save();
                    if (isset($media[$i]) && $request->hasFile('media')[$i]) {
                        $file = $request->file('media')[$i];
                        $path = $file->store('uploads/tv_media');
                        $unit_item_media = new UnitOrderTVItemMedia;
                        $unit_item_media->unit_order_item_id = $tv_item->id;
                        $unit_item_media->media = $path;
                        $unit_item_media->public_id = UuidV4::fromDateTime(now());
                        $unit_item_media->save();
                    }
                } elseif ($unit_types[$i] == 'radio') {
                    $radio_item = new UnitOrderRadioItem;
                    $radio_item->radio_unit_id = $items[$i];
                    $unit_amount = RadioUnit::find($items[$i])->total;
                    if(RadioUnit::find($items[$i])->is_sold ==true){
                        return response()->json([
                            'status' => 'failure',
                            'message' => 'some of the units you specified have already been sold'
                        ],400);
                    }
                    $total_amount = $total_amount + $unit_amount;
                    $radio_item->unit_order_id = $unit_order->id;
                    $radio_item->description = $descriptions[$i] ?? null;
                    $radio_item->save();
                    if (isset($media[$i]) && $request->hasFile('media')[$i]) {
                        $file = $request->file('media')[$i];
                        $path = $file->store('uploads/radio_media');
                        $unit_item_media = new UnitOrderRadioItemMedia;
                        $unit_item_media->unit_order_item_id = $radio_item->id;
                        $unit_item_media->media = $path;
                        $unit_item_media->public_id = UuidV4::fromDateTime(now());
                        $unit_item_media->save();
                    }
                } elseif ($unit_types[$i] == 'print') {
                    $print_item = new UnitOrderPrintItem;
                    $print_item->print_unit_id = $items[$i];
                    $unit_amount = PrintUnit::find($items[$i])->total;
                    if(PrintUnit::find($items[$i])->is_sold ==true){
                        return response()->json([
                            'status' => 'failure',
                            'message' => 'some of the units you specified have already been sold'
                        ],400);
                    }
                    $total_amount = $total_amount + $unit_amount;
                    $print_item->unit_order_id = $unit_order->id;
                    $print_item->description = $descriptions[$i] ?? null;
                    $print_item->save();
                    if (null != $media[$i] && $request->hasFile('media')[$i]) {
                        $file = $request->file('media')[$i];
                        $path = $file->store('uploads/radio_media');
                        $unit_item_media = new UnitOrderPrintItemMedia;
                        $unit_item_media->unit_order_item_id = $print_item->id;
                        $unit_item_media->media = $path;
                        $unit_item_media->public_id = UuidV4::fromDateTime(now());
                        $unit_item_media->save();
                    }
                } elseif ($unit_types[$i] == 'cinema') {
                    $cinema_item = new UnitOrderCinemaItem;
                    $cinema_item->cinema_unit_id = $items[$i];
                    $unit_amount = Cinema::find($items[$i])->total;
                    if(Cinema::find($items[$i])->is_sold ==true){
                        return response()->json([
                            'status' => 'failure',
                            'message' => 'some of the units you specified have already been sold'
                        ],400);
                    }
                    $total_amount = $total_amount + $unit_amount;
                    $cinema_item->unit_order_id = $unit_order->id;
                    $cinema_item->description = $descriptions[$i] ?? null;
                    $cinema_item->save();
                    if (null != $media[$i] && $request->hasFile('media')[$i]) {
                        $file = $request->file('media')[$i];
                        $path = $file->store('uploads/cinema_media');
                        $unit_item_media = new UnitOrderCinemaItemMedia;
                        $unit_item_media->unit_order_item_id = $cinema_item->id;
                        $unit_item_media->media = $path;
                        $unit_item_media->public_id = UuidV4::fromDateTime(now());
                        $unit_item_media->save();
                    }
                } elseif ($unit_types[$i] == 'billboard') {
                    $billboard_item = new UnitOrderBillboardItem;
                    $billboard_item->billboard_unit_id = $items[$i];
                    $unit_amount = Billboard::find($items[$i])->total;
                    if(Billboard::find($items[$i])->is_sold ==true){
                        return response()->json([
                            'status' => 'failure',
                            'message' => 'some of the units you specified have already been sold'
                        ],400);
                    }
                    $total_amount = $total_amount + $unit_amount;
                    $billboard_item->unit_order_id = $unit_order->id;
                    $billboard_item->description = $descriptions[$i] ?? null;
                    $billboard_item->save();
                    if (null != $media[$i] && $request->hasFile('media')[$i]) {
                        $file = $request->file('media')[$i];
                        $path = $file->store('uploads/radio_media');
                        $unit_item_media = new UnitOrderBillboardItemMedia;
                        $unit_item_media->unit_order_item_id = $billboard_item->id;
                        $unit_item_media->media = $path;
                        $unit_item_media->public_id = UuidV4::fromDateTime(now());
                        $unit_item_media->save();
                    }
                }
            }
            $unit_order->update([
                'total_amount' => $total_amount
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Order created',
                'data' => $unit_order->with(['tv_unit_items', 'radio_unit_items', 'cinema_unit_items', 'print_unit_items', 'billboard_unit_items'])->where('id', $unit_order->id)->first()
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

    /**
     * Get all active orders belonging to the signed in user
     */
    public function getAllMyOrders(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrder::with(['tv_unit_items', 'radio_unit_items', 'cinema_unit_items', 'print_unit_items', 'billboard_unit_items', 'payments'])
                ->where('buyer_id', Auth::id())->where('status', 1);

            $columns = [
                'total_amount',
            ];
            // Apply search condition if provided
            if ($search) {
                $data->where(function ($q) use ($search, $columns) {
                    foreach ($columns as $column) {
                        $q->orWhere($column, 'LIKE', '%' . $search . '%');
                    }
                });
            }

            // Apply filters if provided
            foreach ($filters as $filter) {
                $column = $filter['column'];
                $value = $filter['value'];

                if (in_array($column, $columns)) {
                    $data->where($column, $value);
                }
            }

            // Apply sorting if provided
            if ($sortColumn && in_array($sortColumn, $columns)) {
                $data->orderBy($sortColumn, $sortDirection);
            }
            $data = $data->paginate($perPage, ['*'], 'page', $page);
            return response()->json([
                'status' => 'success',
                'message' => 'all orders fetched',
                'data' => $data->items(),
                'meta' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'prev_page_url' => $data->previousPageUrl(),
                    'next_page_url' => $data->nextPageUrl(),
                    'first_page_url' => $data->url(1),
                    'last_page_url' => $data->url($data->lastPage()),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            return response()->json([
                'status' => 'failure',
                'message' => "An error occured"
            ], 500);
        }
    }

    /**
     * delete an order
     */
    public function deleteOrder($order_id)
    {
        try {
            DB::beginTransaction();
            $deleted_order = UnitOrder::find($order_id);
            if ($deleted_order->buyer_id  == Auth::id()) {
                $deleted_order = UnitOrder::destroy($order_id);
                return response()->json([
                    'status' => 'success',
                    'message' => 'order deleted successfully',
                ]);
            } else {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'you are not authorized to delete this order',
                ], 403);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage(), ['exception' => $e]);
            return response()->json([
                'status' => 'failure',
                'message' => "An error occured"
            ], 500);
        }
    }

    /**
     * pay for an order
     */
    public function payOrder(Request $request, $order_id)
    {
        try {
            DB::beginTransaction();
            $order = UnitOrder::find($order_id);
            $request->validate([
                'amount' => "required|numeric",
                'payment_method' => 'required|string',
                'payment_metadata' => 'required|string',
                'payment_date' => 'nullable|string'
            ]);
            if ($order->total_amount > $request->input('amount')) {
                return response()->json([
                    'ststus' => 'failure',
                    'message' => 'The paymnet amount is lower than the total cost of the order'
                ], 400);
            }
            if ($order && $order->buyer_id  == Auth::id()) {
                $payment = new Payment;
                $payment->unit_order_id = $order_id;
                $payment->amount = $request->input('amount');
                $payment->payment_method = $request->input('payment_method');
                $payment->payment_metadata = $request->input('payment_metadata');
                $payment->payment_date = $request->input('payment_date', date('Y-m-d H:i', time()));
                $payment->save();
                // dd($order->tv_unit_items);
                foreach ($order->tv_unit_items as $item) {
                    $item = TVUnit::find($item->tv_unit_id);
                    $item->update([
                        'is_sold' => true
                    ]);
                }
                foreach ($order->radio_unit_items as $item) {
                    $item = RadioUnit::find($item->radio_unit_id);
                    $item->update([
                        'is_sold' => true
                    ]);
                }
                foreach ($order->cinema_unit_items as $item) {
                    $item = Cinema::find($item->cinema_unit_id);
                    $item->update([
                        'is_sold' => true
                    ]);
                }
                foreach ($order->print_unit_items as $item) {
                    $item = PrintUnit::find($item->print_unit_id);
                    $item->update([
                        'is_sold' => true
                    ]);
                }
                foreach ($order->billboard_unit_items as $item) {
                    $item = Billboard::find($item->billboard_unit_id);
                    $item->update([
                        'is_sold' => true
                    ]);
                }
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'order payed for successfully',
                    'data' => $payment
                ]);
            } else {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'you are not authorized to pay for this order',
                ], 403);
            }
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
