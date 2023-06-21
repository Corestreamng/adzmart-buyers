<?php

namespace App\Http\Controllers;

use App\Models\ExpertReq;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ExpertReqController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api-admin', ['except' => ['store']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10); // Number of items per page
            $page = $request->input('page', 1); // Current page number
            $sortColumn = $request->input('sort_column');
            $sortDirection = $request->input('sort_direction', 'asc');
            $search = $request->input('search');
            $filters = $request->input('filters', []);

            $data = ExpertReq::with([
                'owner',
            ])->where('status',1);

            $columns = [
                'name',
                'email',
                'message',
                'answerd',
                'status',
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
                'message' => 'all expert requests fetched',
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        try {
            $request->validate([
                'name' => 'string|required',
                'email' => 'string|email|required',
                'message' => 'string|nullable'
            ]);

            DB::beginTransaction();
            $msg = new ExpertReq;
            $msg->name = $request->name;
            $msg->email = $request->email;
            $msg->message = $request->message;
            $msg->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Expert request created successfully'
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
     * Display the specified resource.
     *
     * @param  \App\Models\ExpertReq  $expertReq
     * @return \Illuminate\Http\Response
     */
    public function show($expertReq)
    {
        try {
            $data = ExpertReq::with([
                'owner',
            ])->where('id', $expertReq)->where('status',1)->first();

            return response()->json([
                'status' => 'success',
                'message' => 'expert request fetched',
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
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ExpertReq  $expertReq
     * @return \Illuminate\Http\Response
     */
    public function edit(ExpertReq $expertReq)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ExpertReq  $expertReq
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $expertReq)
    {
        try {
            $request->validate([
                'status' => 'integer|nullable',
                'answerd' => 'boolean|nullable'
            ]);
            DB::beginTransaction();
            $expertReq = ExpertReq::find($expertReq);
            $expertReq->update([
                'status'  => $request->status ?? 1,
                'answerd' => $request->answerd ?? 0
            ]);
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'expert request updated successfully'
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
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ExpertReq  $expertReq
     * @return \Illuminate\Http\Response
     */
    public function destroy(ExpertReq $expertReq)
    {
        //
    }
}
