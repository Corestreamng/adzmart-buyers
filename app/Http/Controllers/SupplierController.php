<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }
    /**
     * Get all supliers
     *
     */
    public function getAllSuppliers(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);

            $data = Supplier::with(['owner'])->where('is_verified',true);

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

            return [
                'status' => 'success',
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
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            return response()->json([
                'status'=> 'failure',
                'message'=>"An error occured"
            ], 500);
        }
    }

    /**
     * get single supplier
     */
    public function getSupplier(Request $request, $supplier_id){
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number

            $data = $supplier = Supplier::with(['owner'])->where('is_verified',true)->where('id',$supplier_id)->get();

            return [
                'status' => 'success',
                'data' => $data,
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            return response()->json([
                'status'=> 'failure',
                'message'=>"An error occured"
            ], 500);
        }

    }
}
