@extends('layout.app')

@section('title')
    Create Invoice
@endsection

@section('css')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css">
@endsection

@section('content')

<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title font-weight-bold"> CREATE INVOICE : #{{ $lastId }}</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @include('include.alert')

                    <h4 class="header-title text-uppercase">Invoice Basic Info: </h4>
                    <hr>
                    <form action="{{ route('create-invoice') }}" method="post" class="@if(count($errors)) was-validated @endif">
                        @csrf
                        <input type="hidden" name="invoice_no" value="{{ $lastId}}"/>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="customer_name">Customer Name*</label>
                                    <input type="text" required placeholder="Customer Name" name="customer_name" class="form-control border-bottom @error('customer_name') parsley-error @enderror" id="customer_name" value="{{ old('customer_name') }}">
                                    @error('customer_name')
                                        <div class="invalid-feedback">{{ $message}} </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label for="invoicedate">Invoice Date</label>
                                    <input type="text" name="invoice_date" class="form-control border-bottom" id="invoicedate" placeholder="dd/mm/yyyy" value="{{ date('d/m/Y') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="customer_no">Contact No:*
                                        {{-- <span class="pl-2">
                                            <input type="checkbox" name="customer_no_sync" id="customer_no_sync">
                                            <label class="pl-1 mb-0">Would you like to sync with your phone?</label>
                                        </span> --}}
                                    </label>
                                    <input type="text" name="customer_no" class="form-control border-bottom" id="customer_no" maxlength="10" tabindex="5" placeholder="Customer mobile">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="customer_address">Address:</label>
                                    <input type="text" name="customer_address" class="form-control border-bottom" id="customer_address" tabindex="6" placeholder="Customer Address">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="payment_type">Payment Mode:*</label>
                                    <select name="payment_type" class="form-control border-bottom @error('payment_type') parsley-error @enderror" id="payment_type" tabindex="7" required>
                                        <option value="">- Payment Type -</option>
                                        <option value="Cash">Cash</option>
                                        <option value="Online">Online/UPI</option>
                                        <option value="Credit Card">Credit Card</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <h4 class="header-title text-uppercase">Item Details</h4>
                                <hr>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 border p-1 text-center">
                                <b>DESCRIPTIONS</b>
                            </div>
                            <div class="col-md-2 border p-1 text-center">
                                <b>QUANTITY</b>
                            </div>
                            <div class="col-md-4 border p-1 text-center">
                                <b>TOTAL AMOUNT</b>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <button type="button" class="btn btn-sm btn-success mb-2" id="addItemBtn">
                                    <i class="mdi mdi-plus"></i> Add Item
                                </button>
                                <div id="itemsContainer"></div>
                            </div>
                        </div>

                        <div class="row mt-0">
                            <div class="col-md-2">
                                <label>CGST (%)</label>
                                <input type="text" class="form-control border-bottom" placeholder="CGST Rate" name="cgst_rate" id="cgst" oninput="calculateNetAmount()" tabindex="9">
                                <span class="float-right gststyle" id="cgstDisplay">0</span>
                                <input type="hidden" id="cgstAmount" name="cgst_amount" value="0">
                            </div>

                            <div class="col-md-2">
                                <label>SGST (%)</label>
                                <input type="text" class="form-control border-bottom" placeholder="SGST Rate" name="sgst_rate" id="sgst" oninput="calculateNetAmount()" tabindex="10">
                                <span class="float-right gststyle" id="sgstDisplay">0</span>
                                <input type="hidden" id="sgstAmount" name="sgst_amount" value="0">
                            </div>

                            <div class="col-md-2">
                                <label>IGST (%)</label>
                                <input type="text" class="form-control border-bottom" placeholder="IGST Rate" name="igst_rate" id="igst" oninput="calculateNetAmount()" tabindex="11">
                                <span class="float-right gststyle" id="igstDisplay">0</span>
                                <input type="hidden" id="igstAmount" name="igst_amount" value="0">
                            </div>
                            <div class="col-md-2">
                                <label>Discount Amount</label>
                                <input type="number" class="form-control border-bottom" placeholder="Discount" name="discount_amount" id="discAmount" oninput="calculateNetAmount()" min="0" tabindex="12">
                            </div>

                            <div class="col-md-4">
                                <ul style="list-style: none;float: right;">
                                    <li>
                                        <b>Total Amount:</b> ₹ <span type="text" id="totalAmountDisplay">0</span>
                                    </li>
                                    <li>
                                        <b>Discount:</b> ₹ <span type="text" id="discountAmount">0</span>
                                    </li>
                                    <li>
                                        <b>Tax:</b> ₹ <span type="text" id="taxDisplay">0</span>
                                        <input type="hidden" value="0" name="tax_amount" id="taxAmount">
                                    </li>
                                    <li>
                                        <b>Net Amount:</b> ₹ <span type="text" id="netAmountDisplay">0</span>
                                        <input type="hidden" value="0" name="net_amount" id="netAmount">
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <input type="text" name="declaration" class="form-control border-bottom" id="validationCustom05" placeholder="Declaration">
                                </div>
                                <button type="submit" class="btn btn-primary float-right mb-2 ml-2">SUBMIT</button>
                                <a class="btn btn-danger float-right mb-2 ml-2" href="{{ route('allinvoices') }}"><i class="mdi mdi-keyboard-backspace"></i> Back</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<script>
let itemCounter = 0;
    $(document).ready(function() {
    $("#invoicedate").datepicker({dateFormat: 'dd/mm/yy'});
    addItemRow();
    $(document).on('click', '#addItemBtn', addItemRow);
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.item-row').remove();
        calculateTotals();
    });
    $(document).on('blur', '.item-imei', function() {
        searchIMEI($(this).closest('.item-row'));
    });
    $(document).on('keydown', '.item-imei', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            searchIMEI($(this).closest('.item-row'));
        }
    });
    $(document).on('input', '.item-price, .item-quantity', function() {
        calculateItemTotal($(this).closest('.item-row'));
    });
    $(document).on('input', '#cgst, #sgst, #igst, #discAmount', calculateNetAmount);
});
function addItemRow() {
    itemCounter++;
    const rowHtml = `<div class="item-row border p-3 mb-2 rounded" data-index="${itemCounter}">
        <div class="row">
            <div class="col-md-4"><label>IMEI*:</label><input type="text" class="form-control item-imei" placeholder="Search IMEI.."><input type="hidden" class="item-id" name="items[${itemCounter}][item_id]"><small class="text-danger imei-error" style="display:none;">IMEI Not Found</small></div>
            <div class="col-md-6"><label>Description:</label><textarea class="form-control item-description" name="items[${itemCounter}][item_description]" rows="2"></textarea></div>
            <div class="col-md-2 text-right"><button type="button" class="btn btn-sm btn-danger remove-item mt-4"><i class="mdi mdi-delete"></i> Remove</button></div>
        </div>
        <div class="row mt-2">
            <div class="col-md-3"><label>Quantity*:</label><input type="number" class="form-control item-quantity" name="items[${itemCounter}][quantity]" value="1" min="1"></div>
            <div class="col-md-3"><label>Unit Price*:</label><input type="number" class="form-control item-price" name="items[${itemCounter}][unit_price]" step="0.01" min="0" placeholder="0.00"></div>
            <div class="col-md-3"><label>Total:</label><input type="text" class="form-control item-total" readonly value="0.00"></div>
        </div>
    </div>`;
    $('#itemsContainer').append(rowHtml);
    $('.item-imei').last().focus();
}
function searchIMEI($row) {
    const imei = $row.find('.item-imei').val();
    if (!imei) return;
    $.ajax({
        url: `/admin/fetchstockonimei/${imei}`,
        type: 'GET',
        success: function(data) {
            if (data.count > 0 && data.purchase) {
                $row.find('.imei-error').hide();
                $row.find('.item-id').val(data.purchase.id);
                let desc = `Model: ${data.purchase.model}\n`;
                if (data.purchase.color) desc += `Color: ${data.purchase.color}\n`;
                if (data.purchase.storage) desc += `Storage: ${data.purchase.storage}\n`;
                if (data.purchase.imei) desc += `IMEI: ${data.purchase.imei}\n`;
                $row.find('.item-description').val(desc);
                $row.find('.item-price').val(data.purchase.purchase_price).trigger('input');
            } else {
                $row.find('.imei-error').show();
                $row.find('.item-id').val('');
            }
        },
        error: function() {
            $row.find('.imei-error').show();
        }
    });
}
function calculateItemTotal($row) {
    const qty = parseFloat($row.find('.item-quantity').val()) || 1;
    const price = parseFloat($row.find('.item-price').val()) || 0;
    const total = qty * price;
    $row.find('.item-total').val(total.toFixed(2));
    calculateTotals();
}
function calculateTotals() {
    let total = 0;
    $('.item-total').each(function() {
        total += parseFloat($(this).val()) || 0;
    });
    $('#totalAmountInput').val(total.toFixed(2));
    $('#totalAmountDisplay').text(total.toFixed(2));
    calculateNetAmount();
}
function calculateNetAmount() {
    const total = parseFloat($('#totalAmountInput').val()) || 0;
    const cgst = parseFloat($('#cgst').val()) || 0;
    const sgst = parseFloat($('#sgst').val()) || 0;
    const igst = parseFloat($('#igst').val()) || 0;
    const discount = parseFloat($('#discAmount').val()) || 0;
    const cgstAmt = (cgst / 100) * total;
    const sgstAmt = (sgst / 100) * total;
    const igstAmt = (igst / 100) * total;
    const tax = cgstAmt + sgstAmt + igstAmt;
    const net = total + tax - discount;
    $('#cgstDisplay').text(cgstAmt.toFixed(2));
    $('#sgstDisplay').text(sgstAmt.toFixed(2));
    $('#igstDisplay').text(igstAmt.toFixed(2));
    $('#cgstAmount').val(cgstAmt.toFixed(2));
    $('#sgstAmount').val(sgstAmt.toFixed(2));
    $('#igstAmount').val(igstAmt.toFixed(2));
    $('#taxDisplay').text(tax.toFixed(2));
    $('#taxAmount').val(tax.toFixed(2));
    $('#discountAmount').text(discount.toFixed(2));
    $('#netAmountDisplay').text(net.toFixed(2));
    $('#netAmount').val(net.toFixed(2));
}
</script>
@endsection