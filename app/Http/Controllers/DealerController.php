<?php

namespace App\Http\Controllers;

use App\Models\Dealer;
use App\Http\Requests\StoreDealerRequest;
use App\Http\Requests\UpdateDealerRequest;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DealerController extends Controller
{
    /**
     * Display a listing of dealers
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Dealer::getAllDealers();

            return DataTables::of($data)
                ->addIndexColumn()
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('dealers.index');
    }

    /**
     * Store a newly created dealer
     *
     * @param StoreDealerRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreDealerRequest $request)
    {
        try {
            $validated = $request->validated();

            $dealer = Dealer::createDealer($validated);

            return response()->json([
                'status'  => true,
                'message' => 'Dealer created successfully!',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while creating the dealer.',
            ], 500);
        }
    }

    /**
     * Update the specified dealer
     *
     * @param UpdateDealerRequest $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateDealerRequest $request, $id)
    {
        try {
            $validated = $request->validated();
            $dealer = Dealer::updateDealer($id, $validated);

            return response()->json([
                'status'  => true,
                'message' => 'Dealer updated successfully!',
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Dealer not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'An error occurred while updating the dealer.',
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified dealer
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        try {
            $dealer = Dealer::getDealerByIdOrFail($id);

            return response()->json([
                'status'  => true,
                'data'    => $dealer->toArray(),
                'message' => 'Dealer fetched successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'data'    => null,
                'message' => 'Dealer not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'data'    => null,
                'message' => 'An error occurred while fetching the dealer.',
            ], 500);
        }
    }

    /**
     * Remove the specified dealer from storage
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            Dealer::deleteDealer($id);

            return response()->json([
                'status'  => true,
                'message' => 'Dealer deleted successfully!',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Dealer not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to delete dealer',
            ], 500);
        }
    }
}
