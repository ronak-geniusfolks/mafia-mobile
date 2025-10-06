<?php
namespace App\Http\Controllers;

use App\Constants\ResponseCode;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Services\JsonService;
use App\Services\OpeningBalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    protected $jsonService;
    protected $openingBalanceService;

    public function __construct()
    {
        $this->jsonService           = new JsonService();
        $this->openingBalanceService = new OpeningBalanceService();
    }

    public function getFilter()
    {
        $filters = [
            'date_from'        => request('date_from'),
            'date_to'          => request('date_to'),
            'transaction_type' => request('transaction_type'),
            'payment_method'   => request('payment_method'),
        ];
        return $filters;
    }

    public function index()
    {
        try {
            $paymentMethods = PaymentMethod::all();
            if (\request()->ajax()) {
                $columns = [
                    'id',
                    'payment_date',
                    'transaction_type',
                    'payment_method',
                    'amount',
                    'note',
                    'created_at',
                ];

                $filters        = $this->getFilter();
                $data           = (new Transaction())->getTransactions($columns, $filters);
                $openingBalance = (new Transaction())->getOpeningBalance($filters);

                // Calculate total IN and OUT
                $totalIn  = $data->where('transaction_type', 'credit')->sum('amount');
                $totalOut = $data->where('transaction_type', 'debit')->sum('amount');

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->with('openingBalance', $openingBalance['balance'])
                    ->with('cashBalance', $openingBalance['cash_balance'])
                    ->with('bankBalance', $openingBalance['bank_balance'])
                    ->with('totalIn', $totalIn)
                    ->with('totalOut', $totalOut)
                    ->make(true);
            }

            return view('finance.index', compact('paymentMethods'));
        } catch (\Exception $e) {
            if (\request()->ajax()) {
                return $this->jsonService->sendResponse(false, null, 'Error loading transactions: ' . $e->getMessage(), ResponseCode::INTERNAL_SERVER_ERROR);
            }

            return redirect()->back()->with('error', 'Error loading transactions: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'payment_date'     => 'required|date',
                'transaction_type' => 'required|string',
                'payment_method'   => 'required|string',
                'amount'           => 'required|numeric',
                'note'             => 'nullable|string',
            ]);

            if (! empty($request->id)) {
                $transaction = Transaction::find($request->id);
                if ($transaction) {
                    $transaction->update([
                        'payment_date'     => $request->payment_date,
                        'transaction_type' => $request->transaction_type,
                        'payment_method'   => $request->payment_method,
                        'amount'           => $request->amount,
                        'note'             => $request->note,
                        'updated_by'       => Auth::id(),
                    ]);

                    $this->openingBalanceService->recalculateFrom($request->payment_date);

                    $filters        = $this->getFilter();
                    $openingBalance = (new Transaction())->getOpeningBalance($filters);

                    $columns = [
                        'id',
                        'payment_date',
                        'transaction_type',
                        'amount',
                    ];
                    $data     = (new Transaction())->getTransactions($columns, $filters);
                    $totalIn  = $data->where('transaction_type', 'credit')->sum('amount');
                    $totalOut = $data->where('transaction_type', 'debit')->sum('amount');

                    $responseData = [
                        'transaction'    => $transaction ?? null,
                        'openingBalance' => $openingBalance['balance'],
                        'cashBalance'    => $openingBalance['cash_balance'],
                        'bankBalance'    => $openingBalance['bank_balance'],
                        'totalIn'        => $totalIn,
                        'totalOut'       => $totalOut,
                    ];

                    return $this->jsonService->sendResponse(true, $responseData, 'Transaction updated successfully', ResponseCode::SUCCESS);
                } else {
                    return $this->jsonService->sendResponse(false, null, 'Transaction not found', ResponseCode::NOT_FOUND);
                }
            } else {
                $data               = $request->all();
                $data['created_by'] = Auth::id();
                $transaction        = Transaction::create($data);
                $this->openingBalanceService->recalculateFrom($request->payment_date);

                $filters        = $this->getFilter();
                $openingBalance = (new Transaction())->getOpeningBalance($filters);

                $columns = [
                    'id',
                    'payment_date',
                    'transaction_type',
                    'amount',
                ];

                $data     = (new Transaction())->getTransactions($columns, $filters);
                $totalIn  = $data->where('transaction_type', 'credit')->sum('amount');
                $totalOut = $data->where('transaction_type', 'debit')->sum('amount');

                $responseData = [
                    'transaction'    => $transaction ?? null,
                    'openingBalance' => $openingBalance['balance'],
                    'cashBalance'    => $openingBalance['cash_balance'],
                    'bankBalance'    => $openingBalance['bank_balance'],
                    'totalIn'        => $totalIn,
                    'totalOut'       => $totalOut,
                ];

                return $this->jsonService->sendResponse(true, $responseData, 'Transaction created successfully', ResponseCode::CREATED);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->jsonService->sendResponse(false, $e->errors(), 'Validation failed', ResponseCode::UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            return $this->jsonService->sendResponse(false, null, 'Error saving transaction: ' . $e->getMessage(), ResponseCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function delete($id)
    {
        try {
            $transaction = Transaction::find($id);

            if (! $transaction) {
                return $this->jsonService->sendResponse(false, null, 'Transaction not found', ResponseCode::NOT_FOUND);
            }

            $recalcFromDate = $transaction->payment_date;
            $transaction->delete();
            $this->openingBalanceService->recalculateFrom($recalcFromDate);

            $filters        = $this->getFilter();
            $openingBalance = (new Transaction())->getOpeningBalance($filters);
            $data           = (new Transaction())->getTransactions(['id', 'payment_date', 'transaction_type', 'amount'], $filters);
            $totalIn        = $data->where('transaction_type', 'credit')->sum('amount');
            $totalOut       = $data->where('transaction_type', 'debit')->sum('amount');

            return $this->jsonService->sendResponse(true, [
                'openingBalance' => $openingBalance,
                'totalIn'        => $totalIn,
                'totalOut'       => $totalOut,
            ], 'Transaction deleted successfully', ResponseCode::SUCCESS);
        } catch (\Exception $e) {
            return $this->jsonService->sendResponse(false, null, 'Error deleting transaction: ' . $e->getMessage(), ResponseCode::INTERNAL_SERVER_ERROR);
        }
    }

    public function edit($id)
    {
        try {
            $transaction = Transaction::find($id);

            if (! $transaction) {
                return $this->jsonService->sendResponse(false, null, 'Transaction not found', ResponseCode::NOT_FOUND);
            }

            return $this->jsonService->sendResponse(true, $transaction, 'Transaction retrieved successfully', ResponseCode::SUCCESS);
        } catch (\Exception $e) {
            return $this->jsonService->sendResponse(false, null, 'Error fetching transaction: ' . $e->getMessage(), ResponseCode::INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Resync all opening balances and redirect back with message
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resyncBalances()
    {
        try {
            $result = $this->openingBalanceService->recalculateAll();

            if ($result['success']) {
                return redirect()->route('transactions.index')->with('status', $result['message']);
            } else {
                return redirect()->route('transactions.index')->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            return redirect()->route('transactions.index')->with('error', 'Error resyncing balances: ' . $e->getMessage());
        }
    }
}
