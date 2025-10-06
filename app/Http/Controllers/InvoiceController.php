<?php
namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    protected $googleContactService;

    public function __construct()
    {
        $this->googleContactService = app('GoogleContact');
    }

    public function index()
    {
        $allInvoices = Invoice::where('deleted', 0)->orderBy('invoice_date', 'desc')->get();

        return view('invoice.index', ['allInvoices' => $allInvoices]);
    }

    public function newInvoice()
    {
        $stocksModel = Purchase::where('deleted', 0)->orderBy('purchase_date', 'desc')->get();

        $lastInvoice   = Invoice::orderBy('id', 'desc')->first();
        $currentYear   = date('Y');
        $nextInvoiceNo = 1; // Default to 1
        if ($lastInvoice) {
            // Extract the year from the last invoice number
            preg_match('/MF(\d{4})/', (string) $lastInvoice->invoice_no, $matches);
            $lastYear = $matches[1] ?? null;

            if ($lastYear == $currentYear) {
                // If the year matches, increment the invoice number
                $lastId        = intval(substr((string) $lastInvoice->invoice_no, -4)); // Get the last numeric part
                $nextInvoiceNo = $lastId + 1;
            }
        }

        $formattedNumber = 'MF' . $currentYear . str_pad($nextInvoiceNo, 4, '0', STR_PAD_LEFT);

        return view('invoice.add', [
            'stocksModel' => $stocksModel,
            'lastId'      => $formattedNumber,
        ]);
    }

    public function createInvoice(Request $request)
    {
        // Convert date format from dd/mm/yyyy to Y-m-d using Carbon
        if ($request->invoice_date) {
            try {
                $convertedDate = Carbon::createFromFormat('d/m/Y', $request->invoice_date)->format('Y-m-d');
                $request->merge(['invoice_date' => $convertedDate]);
            } catch (\Exception $e) {
                // If conversion fails, keep original value for validation to handle
            }
        }

        if ($request->warranty_expiry_date) {
            try {
                $convertedDate = Carbon::createFromFormat('d/m/Y', $request->warranty_expiry_date)->format('Y-m-d');
                $request->merge(['warranty_expiry_date' => $convertedDate]);
            } catch (\Exception $e) {
                // If conversion fails, keep original value for validation to handle
            }
        }

        $request->validate([
            'item_id'          => ['required', Rule::unique('invoices')->where(fn($q) => $q->where('deleted', 0))],
            'customer_name'    => 'required|string|max:255',
            'item_description' => 'required|string',
            'invoice_date'     => 'required|date',
            'invoice_no'       => 'required|string',
            'total_amount'     => 'required|numeric|min:0',
            'net_amount'       => 'required|numeric|min:0',
            'payment_type'     => 'required|string',
        ]);

        $purchase = Purchase::findOrFail($request->item_id);

        $profit = floatval($request->net_amount - $purchase->purchase_price);

        $invoice = Invoice::create([
            'item_id'              => $request->item_id,
            'item_description'     => $request->item_description,
            'customer_name'        => $request->customer_name,
            'customer_no'          => $request->customer_no,
            'invoice_date'         => $request->invoice_date,
            'warranty_expiry_date' => $request->warranty_expiry_date ?? null,
            'invoice_no'           => $request->invoice_no,
            'total_amount'         => $request->total_amount,
            'net_amount'           => $request->net_amount,
            'cgst_rate'            => $request->cgst_rate ?? 0,
            'sgst_rate'            => $request->sgst_rate ?? 0,
            'igst_rate'            => $request->igst_rate ?? 0,
            'cgst_amount'          => $request->cgst_amount ?? 0,
            'sgst_amount'          => $request->sgst_amount ?? 0,
            'igst_amount'          => $request->igst_amount ?? 0,
            'declaration'          => $request->declaration ?? null,
            'tax_amount'           => $request->tax_amount ?? 0,
            'discount'             => $request->discount_amount ?? 0,
            'discount_rate'        => $request->discount_rate ?? 0,
            'quantity'             => $request->quantity ?? 1,
            'customer_address'     => $request->customer_address ?? null,
            'payment_type'         => $request->payment_type,
            'is_paid'              => 1,
            'profit'               => $profit,
            'invoice_by'           => Auth::id(),
        ]);

        // Mark purchase as sold
        $purchase->update([
            'is_sold'   => 1,
            'sell_date' => $request->invoice_date,
        ]);

        if ($request->customer_no_sync == 'on') {
            $request->merge(['invoice_id' => $invoice->id]);

            $result = $this->googleContactService->syncContact($request);

            if (!$result['success']) {
                if (isset($result['redirect'])) {
                    return redirect()->to($result['redirect']);
                }
                return response()->json(['error' => $result['error'] ?? 'Contact sync failed'], 500);
            }
        }

        return redirect()->route('print-invoice', $invoice->id);
    }


    public function invoiceDetail($id)
    {
        $invoice = Invoice::with(['purchase', 'user'])->findOrFail($id);
        $amountInWords = $this->amoutInWords(floatval($invoice->net_amount));

        return view('invoice.detail', ['invoice' => $invoice, 'amountInWords' => $amountInWords]);
    }

    public function printInvoice($id)
    {
        $invoice       = Invoice::with(['purchase', 'user'])->findOrFail($id);
        $amountInWords = $this->amoutInWords(floatval($invoice->net_amount));

        return view('invoice.print', ['invoice' => $invoice, 'amountInWords' => $amountInWords]);
    }

    public function fetchModelData($imei)
    {
        $purchase = Purchase::where('imei', 'LIKE', "%{$imei}%")
            ->where('is_sold', 0)
            ->first();
        $count = $purchase ? 1 : 0;

        return response()->json([
            'purchase' => $purchase,
            'count'    => $count,
        ]);
    }

    public function editInvoice($id)
    {
        $invoice     = Invoice::findOrFail($id);
        $stocksModel = Purchase::where('deleted', 0)->orderBy('purchase_date', 'desc')->get();

        return view('invoice.edit', ['invoice' => $invoice, 'stocksModel' => $stocksModel]);
    }

    public function updateInvoice(Request $request, $id)
    {
        $invoice    = Invoice::findOrFail($id);

        // Convert date format from dd/mm/yyyy to Y-m-d using Carbon
        if ($request->invoice_date) {
            try {
                $convertedDate = Carbon::createFromFormat('d/m/Y', $request->invoice_date)->format('Y-m-d');
                $request->merge(['invoice_date' => $convertedDate]);
            } catch (\Exception $e) {
                // If conversion fails, keep original value for validation to handle
            }
        }

        if ($request->warranty_expiry_date) {
            try {
                $convertedDate = Carbon::createFromFormat('d/m/Y', $request->warranty_expiry_date)->format('Y-m-d');
                $request->merge(['warranty_expiry_date' => $convertedDate]);
            } catch (\Exception $e) {
                // If conversion fails, keep original value for validation to handle
            }
        }

        // Validate dates after conversion
        $request->validate([
            'invoice_date' => 'required|date',
            'warranty_expiry_date' => 'nullable|date',
        ]);

        $updateData = $request->all();

        // check if contact name or contact number is chagnges?
        $isContactInformationChanges = false;
        if ($invoice->customer_name != $request->customer_name || $invoice->customer_no != $request->customer_no) {
            $isContactInformationChanges = true;
        }

        if (isset($updateData['customer_no_sync'])) {
            unset($updateData['customer_no_sync']);
        }

        $updateData['profit']     = floatval($request->net_amount - Purchase::findOrFail($request->item_id)->purchase_price);
        $updateData['customer_address'] = $request->customer_address ?? null;
        $updateData['invoice_by'] = Auth::user()->id;
        $invoice->update($updateData);

        $customer_no_sync = $request->customer_no_sync;
        if ($customer_no_sync == 'on' && $isContactInformationChanges) {
            $request->merge(['invoice_id' => $invoice->id]);
            $result = $this->googleContactService->syncContact($request);
            if ($result['success'] == false) {
                if (isset($result['redirect'])) {
                    // Redirect the user to the Google authentication page (or other redirect URL)
                    return redirect()->to($result['redirect']);
                }

                // If no redirect URL is set, return an error message
                return response()->json(['error' => $result['error']], 500);
            }
        }

        return redirect()->route('allinvoices')->withStatus('Invoice Updated Successfully..');
    }

    public function amoutInWords(float $amount): string
    {
        $amount_after_decimal = round($amount - ($num = floor($amount)), 2) * 100;
        // Check if there is any number after decimal
        $amt_hundred  = null;
        $count_length = strlen($num);
        $x            = 0;
        $string       = [];
        $change_words = [0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five', 6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten', 11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen', 16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'];
        $here_digits  = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];

        while ($x < $count_length) {
            $get_divider = ($x == 2) ? 10 : 100;
            $amount      = floor($num % $get_divider);
            $num         = floor($num / $get_divider);
            $x += $get_divider == 10 ? 1 : 2;
            if ($amount !== 0.0) {
                $add_plural  = (($counter = count($string)) && $amount > 9) ? 's' : null;
                $amt_hundred = ($counter == 1 && $string[0]) ? ' and ' : null;
                $string[]    = ($amount < 21) ? $change_words[$amount] . ' ' . $here_digits[$counter] . $add_plural . ' ' . $amt_hundred : $change_words[floor($amount / 10) * 10] . ' ' . $change_words[$amount % 10] . ' ' . $here_digits[$counter] . $add_plural . ' ' . $amt_hundred;
            } else {
                $string[] = null;
            }
        }
        $implode_to_Rupees = implode('', array_reverse($string));
        $get_paise         = ($amount_after_decimal > 0) ? 'And ' . ($change_words[$amount_after_decimal / 10] . '
        ' . $change_words[$amount_after_decimal % 10]) . ' Paise' : '';

        return ($implode_to_Rupees !== '' && $implode_to_Rupees !== '0' ? $implode_to_Rupees . 'Only ' : '') . $get_paise;
    }
}
