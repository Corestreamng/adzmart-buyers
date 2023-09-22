<?php

namespace App\Http\Controllers;

use App\Models\Billboard;
use App\Models\Cinema;
use App\Models\Payment;
use App\Models\PrintUnit;
use App\Models\RadioUnit;
use App\Models\Supplier;
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
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Rfc4122\UuidV4;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api-admin', ['except' => []]);
    }
    /**
     * Get all active orders
     */
    public function getAllOrders(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrder::with(['tv_unit_items', 'tv_unit_items.unit', 'radio_unit_items', 'radio_unit_items.unit', 'cinema_unit_items', 'cinema_unit_items.unit', 'print_unit_items', 'print_unit_items.unit', 'billboard_unit_items', 'billboard_unit_items.unit', 'payments'])
                ->where('status', 1);

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
     * Get all funds
     */
    public function getAllFunds(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);

            $data = Payment::with([
                'unit_order'
            ]);

            $columns = [
                'amount',
                'payment_method',
                'payment_date',
                'payment_metadata'
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
            $total_funds = Payment::sum('amount');
            return response()->json([
                'status' => 'success',
                'message' => 'all payments fetched',
                'total' => $data->total(),
                'data' => ['total_funds' => $total_funds, 'payments' => $data->items()],
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
     * Get all funds
     */
    public function getAllUsers(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);

            $data = User::with([
                'team_members', 'team_owner'
            ]);

            $columns = [
                'name',
                'email',
                'business_name',
                'is_owner',
                'belongs_to'
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
                'message' => 'all users fetched',
                'total' => $data->total(),
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
     * Get all radio items
     */
    public function getAllRadioOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            // $data = UnitOrderRadioItem::with([
            //     'unit' => function ($q) {
            //         $q->where('progress', '=', "Running");
            //     },
            //     'unit_order','unit_order.payments','unit_order.payments' 'media'
            // ]);

            $data = UnitOrderRadioItem::with([
                'unit',
                'unit_order', 'unit_order.payments', 'media'
            ]);


            $columns = [
                'radio_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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

    public function getAllOrdersByStatus(Request $request)
    {
        try {
            $data = [];
            $data['running_radio'] = UnitOrderRadioItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Running");
            })->count();
            $data['pending_radio'] = UnitOrderRadioItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Not Initiated");
                $q->orWhere('progress', '=', "Processing");
            })->count();
            $data['complete_radio'] = UnitOrderRadioItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "complete");
            })->count();
            $data['declined_radio'] = UnitOrderRadioItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Declined");
            })->count();


            $data['running_tv'] = UnitOrderTVItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Running");
            })->count();
            $data['pending_tv'] = UnitOrderTVItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Not Initiated");
                $q->orWhere('progress', '=', "Processing");
            })->count();
            $data['complete_tv'] = UnitOrderTVItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "complete");
            })->count();
            $data['declined_tv'] = UnitOrderTVItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Declined");
            })->count();


            $data['running_print'] = UnitOrderPrintItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Running");
            })->count();
            $data['pending_print'] = UnitOrderPrintItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Not Initiated");
                $q->orWhere('progress', '=', "Processing");
            })->count();
            $data['complete_print'] = UnitOrderPrintItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "complete");
            })->count();
            $data['declined_print'] = UnitOrderPrintItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Declined");
            })->count();


            $data['running_cinema'] = UnitOrderCinemaItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Running");
            })->count();
            $data['pending_cinema'] = UnitOrderCinemaItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Not Initiated");
                $q->orWhere('progress', '=', "Processing");
            })->count();
            $data['complete_cinema'] = UnitOrderCinemaItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "complete");
            })->count();
            $data['declined_cinema'] = UnitOrderCinemaItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Declined");
            })->count();


            $data['running_billboard'] = UnitOrderBillboardItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Running");
            })->count();
            $data['pending_billboard'] = UnitOrderBillboardItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Not Initiated");
                $q->orWhere('progress', '=', "Processing");
            })->count();
            $data['complete_billboard'] = UnitOrderBillboardItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "complete");
            })->count();
            $data['declined_billboard'] = UnitOrderBillboardItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Declined");
            })->count();

            return response()->json([
                'status' => 'success',
                'message' => 'all stats fetched',
                'data' => $data
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
     * Get running radio items
     */
    public function getRunningRadioOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderRadioItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Running");
            });

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'radio_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getPendingRadioOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderRadioItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Processing");
            });

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'radio_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getCompleteRadioOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderRadioItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Complete");
            });

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'radio_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getDeclinedRadioOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderRadioItem::with([
                'unit' => function ($q) {
                    $q->where('progress', '=', "Declined");
                },
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ]);

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'radio_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get all radio items
     */
    public function getAllTVOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            // $data = UnitOrderRadioItem::with([
            //     'unit' => function ($q) {
            //         $q->where('progress', '=', "Running");
            //     },
            //     'unit_order','unit_order.payments','unit_order.payments' 'media'
            // ]);

            $data = UnitOrderTVItem::with([
                'unit',
                'unit_order', 'unit_order.payments', 'media'
            ]);

            $columns = [
                'tv_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getRunningTVOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderTVItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Running");
            });

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'tv_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getPendingTVOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderTVItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Processing");
            });

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'tv_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getCompleteTVOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderTVItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Complete");
            });

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'tv_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getDeclinedTVOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderTVItem::with([
                'unit' => function ($q) {
                    $q->where('progress', '=', "Declined");
                },
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ]);

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'radio_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get all radio items
     */
    public function getAllCinemaOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            // $data = UnitOrderRadioItem::with([
            //     'unit' => function ($q) {
            //         $q->where('progress', '=', "Running");
            //     },
            //     'unit_order','unit_order.payments', 'media'
            // ]);

            $data = UnitOrderCinemaItem::with([
                'unit',
                'unit_order', 'unit_order.payments', 'media'
            ]);

            $columns = [
                'cinema_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getRunningCinemaOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderCinemaItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Running");
            });

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'tv_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getPendingCinemaOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderCinemaItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Processing");
            });

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'tv_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getCompleteCinemaOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderCinemaItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Complete");
            });

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'tv_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getDeclinedCinemaOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderCinemaItem::with([
                'unit' => function ($q) {
                    $q->where('progress', '=', "Declined");
                },
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ]);

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'radio_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get all radio items
     */
    public function getAllBillboardOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            // $data = UnitOrderRadioItem::with([
            //     'unit' => function ($q) {
            //         $q->where('progress', '=', "Running");
            //     },
            //     'unit_order','unit_order.payments', 'media'
            // ]);

            $data = UnitOrderBillboardItem::with([
                'unit',
                'unit_order', 'unit_order.payments', 'media'
            ]);

            $columns = [
                'billboard_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getRunningBillboardOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderBillboardItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Running");
            });

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'tv_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getPendingBillboardOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderBillboardItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Processing");
            });

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'tv_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getCompleteBillboardOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderBillboardItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Complete");
            });

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'tv_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getDeclinedBillboardOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderBillboardItem::with([
                'unit' => function ($q) {
                    $q->where('progress', '=', "Declined");
                },
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ]);

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'radio_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get all radio items
     */
    public function getAllPrintOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            // $data = UnitOrderRadioItem::with([
            //     'unit' => function ($q) {
            //         $q->where('progress', '=', "Running");
            //     },
            //     'unit_order','unit_order.payments', 'media'
            // ]);

            $data = UnitOrderPrintItem::with([
                'unit',
                'unit_order', 'unit_order.payments', 'media'
            ]);

            $columns = [
                'print_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getRunningPrintOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderPrintItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Running");
            });

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'tv_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getPendingPrintOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderPrintItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Processing");
            });

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'tv_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getCompletePrintOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderPrintItem::with([
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ])->whereHas('unit', function ($q) {
                $q->where('progress', '=', "Complete");
            });

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'tv_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get running radio items
     */
    public function getDeclinedPrintOrderItems(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);
            $data = UnitOrderPrintItem::with([
                'unit' => function ($q) {
                    $q->where('progress', '=', "Declined");
                },
                'unit_order', 'unit_order.payments', 'media', 'unit'
            ]);

            // $data = UnitOrderRadioItem::with([
            //     'unit',
            //     'unit_order', 'unit_order.payments', 'media'
            // ]);

            $columns = [
                'radio_unit_id',
                'unit_order_id',
                'description',
                'quantity'
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
                'total' => $data->total(),
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
     * Get all unverifed supliers
     */
    public function getAllUnverifiedSellers(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);

            $data = Supplier::with([
                'owner',
            ])->where('is_verified', false);


            $columns = [
                'uuid',
                'company_name',
                'company_location',
                'rc_number',
                'government_id',
                'is_verified',
                'owner_id',
                'created_at',
                'updated_at',
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
                'message' => 'all unverified suppliers fetched',
                'total' => $data->total(),
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
     * Get suplier
     */
    public function getSeller(Request $request, $supplier_id)
    {
        try {
            $data = Supplier::with([
                'owner',
            ])->where('id', $supplier_id)->first();
            return response()->json([
                'status' => 'success',
                'message' => 'supplier fetched',
                'data' => $data,
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
     * Get all supliers items
     */
    public function getAllSellers(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);

            $data = Supplier::with([
                'owner',
            ]);

            $columns = [
                'uuid',
                'company_name',
                'company_location',
                'rc_number',
                'government_id',
                'is_verified',
                'owner_id',
                'created_at',
                'updated_at',
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
                'message' => 'all suppliers fetched',
                'total' => $data->total(),
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
     * approve suplier
     */
    public function approveSeller(Request $request, $supplier_id)
    {
        try {
            DB::beginTransaction();
            $supplier = Supplier::find($supplier_id);
            $supplier->update([
                'is_verified' => true
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'profile approval successful',
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
     * block suplier
     */
    public function blockSeller(Request $request, $supplier_id)
    {
        try {
            DB::beginTransaction();
            $supplier = Supplier::find($supplier_id);
            $supplier->update([
                'is_verified' => false
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'profile block successful',
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
     * unblock suplier
     */
    public function unblockSeller(Request $request, $supplier_id)
    {
        try {
            DB::beginTransaction();
            $supplier = Supplier::find($supplier_id);
            $supplier->update([
                'is_verified' => true
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'profile unblock successful',
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
     * block suplier
     */
    public function blockUser(Request $request, $user_id)
    {
        try {
            DB::beginTransaction();
            $user = User::find($user_id);
            $user->update([
                'blocked' => true
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'profile block successful',
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
     * unblock suplier
     */
    public function unblockUser(Request $request, $user_id)
    {
        try {
            DB::beginTransaction();
            $user = User::find($user_id);
            $user->update([
                'blocked' => false
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'profile unblock successful',
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
