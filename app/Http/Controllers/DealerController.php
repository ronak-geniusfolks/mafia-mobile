<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreDealerRequest;
use App\Http\Requests\UpdateDealerRequest;
use App\Models\Dealer;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DealerController extends Controller
{
    /**
     * Display a listing of dealers.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = Dealer::query();

            return DataTables::of($data)
                ->addIndexColumn()
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('dealers.index');
    }

    /**
     * Store a newly created dealer.
     */
    public function store(StoreDealerRequest $request)
    {
        try {
            $dealer = Dealer::create($request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Dealer created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the dealer.',
            ], 500);
        }
    }

    /**
     * Update the specified dealer.
     */
    public function update(UpdateDealerRequest $request, $id)
    {
        try {
            $dealer = Dealer::findOrFail($id);
            $dealer->update($request->validated());

            return response()->json([
                'status' => true,
                'message' => 'Dealer updated successfully!',
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Dealer not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the dealer.',
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified dealer.
     */
    public function edit($id)
    {
        try {
            $dealer = Dealer::findOrFail($id);

            return response()->json([
                'status' => true,
                'data' => $dealer->toArray(),
                'message' => 'Dealer fetched successfully',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'Dealer not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => 'An error occurred while fetching the dealer.',
            ], 500);
        }
    }

    /**
     * Remove the specified dealer from storage.
     */
    public function destroy($id)
    {
        try {
            $dealer = Dealer::findOrFail($id);
            $dealer->delete();

            return response()->json([
                'status' => true,
                'message' => 'Dealer deleted successfully!',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Dealer not found',
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to delete dealer',
            ], 500);
        }
    }
}
