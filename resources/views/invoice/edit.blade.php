@extends('layout.app')

@section('title')
    Edit Invoice
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
                <h4 class="page-title font-weight-bold"> EDIT INVOICE : #{{ $invoice->invoice_no }}</h4>
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
                    <form action="{{ route('invoice-update', $invoice->id) }}" method="POST" class="@if(count($errors)) was-validated @endif">
                        @csrf
                        <input type="hidden" name="invoice_no" value="{{ $invoice->invoice_no }}"/>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="customer_name">Customer Name*</label>
                                    <input type="text" required placeholder="Customer Name" name="customer_name" class="form-control border-bottom @error('customer_name') parsley-error @enderror" id="customer_name" value="{{ $invoice->customer_name }}">
                                    @error('customer_name')
                                        <div class="invalid-feedback">{{ $message}} </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label for="invoicedate">Invoice Date</label>
                                    <input type="text" name="invoice_date" class="form-control border-bottom" id="invoicedate" placeholder="dd/mm/yyyy" value="{{ date('d/m/Y', strtotime($invoice->invoice_date)) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="customer_no">Contact No:*</label>
                                    <input type="text" name="customer_no" class="form-control border-bottom" id="customer_no" maxlength="10" tabindex="3" placeholder="Customer mobile" value="{{ $invoice->customer_no }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label for="customer_address">Address:</label>
                                    <input type="text" name="customer_address" class="form-control border-bottom" id="customer_address" tabindex="4" placeholder="Customer Address" value="{{ $invoice->customer_address }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="payment_type">Payment Mode:*</label>
                                    <select name="payment_type" class="form-control border-bottom @error('payment_type') parsley-error @enderror" id="payment_type" tabindex="5" required>
                                        <option value="">- Payment Type -</option>
                                        <option value="Cash" @selected($invoice->payment_type == 'Cash')>Cash</option>
                                        <option value="Online" @selected($invoice->payment_type == 'Online')>Online/UPI</option>
                                        <option value="Credit Card" @selected($invoice->payment_type == 'Credit Card')>Credit Card</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <h4 class="header-title text-uppercase">Item Details</h4>
                                <hr>
                                <button type="button" class="btn btn-sm btn-success mb-3" id="addItemBtn">
                                    <i class="mdi mdi-plus"></i> Add Item
                                </button>
                            </div>
                        </div>

                        <div id="itemsContainer" class="mb-3"></div>

                        <div class="row mt-0">
                            <div class="col-md-2">
                                <label>CGST (%)</label>
                                <input type="text" class="form-control border-bottom" placeholder="CGST Rate" name="cgst_rate" id="cgst" tabindex="9" value="{{ $invoice->cgst_rate ?? 0 }}">
                                <span class="float-right gststyle" id="cgstDisplay">{{ $invoice->cgst_amount ?? 0 }}</span>
                                <input type="hidden" id="cgstAmount" name="cgst_amount" value="{{ $invoice->cgst_amount ?? 0 }}">
                            </div>

                            <div class="col-md-2">
                                <label>SGST (%)</label>
                                <input type="text" class="form-control border-bottom" placeholder="SGST Rate" name="sgst_rate" id="sgst" tabindex="10" value="{{ $invoice->sgst_rate ?? 0 }}">
                                <span class="float-right gststyle" id="sgstDisplay">{{ $invoice->sgst_amount ?? 0 }}</span>
                                <input type="hidden" id="sgstAmount" name="sgst_amount" value="{{ $invoice->sgst_amount ?? 0 }}">
                            </div>

                            <div class="col-md-2">
                                <label>IGST (%)</label>
                                <input type="text" class="form-control border-bottom" placeholder="IGST Rate" name="igst_rate" id="igst" tabindex="11" value="{{ $invoice->igst_rate ?? 0 }}">
                                <span class="float-right gststyle" id="igstDisplay">{{ $invoice->igst_amount ?? 0 }}</span>
                                <input type="hidden" id="igstAmount" name="igst_amount" value="{{ $invoice->igst_amount ?? 0 }}">
                            </div>
                            <div class="col-md-2">
                                <label>Discount Amount</label>
                                <input type="number" class="form-control border-bottom" placeholder="Discount" name="discount_amount" id="discAmount" min="0" tabindex="12" value="{{ $invoice->discount ?? 0 }}">
                            </div>

                            <div class="col-md-4">
                                <ul style="list-style: none;float: right;">
                                    <li>
                                        <b>Total Amount:</b> ₹ <span type="text" id="totalAmountDisplay">{{ $invoice->total_amount ?? 0 }}</span>
                                        <input type="hidden" name="total_amount" id="totalAmountInput" value="{{ $invoice->total_amount ?? 0 }}">
                                    </li>
                                    <li>
                                        <b>Discount:</b> ₹ <span type="text" id="discountAmount">{{ $invoice->discount ?? 0 }}</span>
                                    </li>
                                    <li>
                                        <b>Tax:</b> ₹ <span type="text" id="taxDisplay">{{ ($invoice->cgst_amount ?? 0) + ($invoice->sgst_amount ?? 0) + ($invoice->igst_amount ?? 0) }}</span>
                                        <input type="hidden" value="{{ ($invoice->cgst_amount ?? 0) + ($invoice->sgst_amount ?? 0) + ($invoice->igst_amount ?? 0) }}" name="tax_amount" id="taxAmount">
                                    </li>
                                    <li>
                                        <b>Net Amount:</b> ₹ <span type="text" id="netAmountDisplay">{{ $invoice->net_amount ?? 0 }}</span>
                                        <input type="hidden" value="{{ $invoice->net_amount ?? 0 }}" name="net_amount" id="netAmount">
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <input type="text" name="declaration" class="form-control border-bottom" id="validationCustom05" placeholder="Declaration" value="{{ $invoice->declaration ?? '' }}">
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
const existingItems = @json($invoice->items);

$(document).ready(function() {
    $("#invoicedate").datepicker({dateFormat: 'dd/mm/yy'});
    
    // Load existing items
    existingItems.forEach(function(item) {
        addItemRow(item);
    });
    
    // If no items exist, add one empty row
    if (existingItems.length === 0) {
        addItemRow();
    }
    
    $(document).on('click', '#addItemBtn', function() {
        addItemRow();
    });
    
    $(document).on('click', '.remove-item', function() {
        if ($('.item-row').length <= 1) {
            alert('At least one item is required in the invoice');
            return;
        }
        $(this).closest('.item-row').remove();
        calculateTotals();
    });
    
    $('form').on('submit', function(e) {
        if ($('.item-row').length === 0) {
            e.preventDefault();
            alert('Please add at least one item to the invoice');
            return false;
        }
    });
    
    $(document).on('blur', '.item-imei', function() {
        searchIMEI($(this).closest('.item-row'));
    });
    
    $(document).on('input', '.item-imei', function() {
        const $row = $(this).closest('.item-row');
        const imei = $(this).val();
        
        $row.find('.imei-error').hide();
        
        let duplicateFound = false;
        $('.item-row').each(function() {
            const currentRow = $(this);
            if (currentRow[0] === $row[0]) return;
            
            const otherImei = currentRow.find('.item-imei').val();
            if (otherImei && otherImei === imei) {
                duplicateFound = true;
                return false;
            }
        });
        
        if (duplicateFound && imei) {
            $row.find('.imei-error').text('This IMEI is already added in another item').show();
        }
    });
    
    $(document).on('keydown', '.item-imei', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            searchIMEI($(this).closest('.item-row'));
        }
    });
    
    $(document).on('input keyup change', '.item-price, .item-quantity', function() {
        calculateItemTotal($(this).closest('.item-row'));
    });
    
    $(document).on('input keyup change', '#cgst, #sgst, #igst, #discAmount', function() {
        calculateNetAmount();
    });
    
    // Initialize calculations
    calculateTotals();
});

function addItemRow(item = null) {
    itemCounter++;
    const imei = item && item.purchase ? item.purchase.imei : '';
    const itemId = item ? item.item_id : '';
    let warrantyDate = '';
    if (item && item.warranty_expiry_date) {
        const date = new Date(item.warranty_expiry_date);
        warrantyDate = String(date.getDate()).padStart(2, '0') + '/' + String(date.getMonth() + 1).padStart(2, '0') + '/' + date.getFullYear();
    }
    const quantity = item ? item.quantity : 1;
    const unitPrice = item ? item.unit_price : '';
    const total = item ? item.total_amount : 0;
    const description = item ? item.item_description : '';
    
    const rowHtml = `<div class="item-row border p-3 mb-3 rounded" data-index="${itemCounter}">
        <div class="row mb-2">
            <div class="col-md-6">
                <label>IMEI*:</label>
                <input type="text" class="form-control item-imei" placeholder="Search IMEI.." value="${imei}">
                <input type="hidden" class="item-id" name="items[${itemCounter}][item_id]" value="${itemId}">
                <small class="text-danger imei-error" style="display:none;">IMEI Not Found</small>
            </div>
            <div class="col-md-2 align-self-end">
                <button type="button" class="btn btn-sm btn-danger remove-item mt-0" style="width: 100%;">
                    <i class="mdi mdi-delete"></i> Remove
                </button>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-3">
                <label>Warranty Expiry Date:</label>
                <input type="text" class="form-control item-warranty" name="items[${itemCounter}][warranty_expiry_date]" placeholder="dd/mm/yyyy" value="${warrantyDate}">
            </div>
            <div class="col-md-2">
                <label>Quantity*:</label>
                <input type="number" class="form-control item-quantity" name="items[${itemCounter}][quantity]" value="${quantity}" min="1">
            </div>
            <div class="col-md-3">
                <label>Unit Price*:</label>
                <input type="number" class="form-control item-price" name="items[${itemCounter}][unit_price]" step="0.01" min="0" placeholder="0.00" value="${unitPrice}">
            </div>
            <div class="col-md-4">
                <label>Total:</label>
                <input type="text" class="form-control item-total" readonly value="${total}">
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-12">
                <label>Description:</label>
                <textarea class="form-control item-description" name="items[${itemCounter}][item_description]" rows="2">${description}</textarea>
            </div>
        </div>
    </div>`;
    
    $('#itemsContainer').append(rowHtml);
    $('.item-warranty').last().datepicker({dateFormat: 'dd/mm/yy'});
}

function searchIMEI($row) {
    const imei = $row.find('.item-imei').val();
    if (!imei) return;
    
    let duplicateFound = false;
    $('.item-row').each(function() {
        const currentRow = $(this);
        if (currentRow[0] === $row[0]) return;
        
        const otherImei = currentRow.find('.item-imei').val();
        if (otherImei && otherImei === imei) {
            duplicateFound = true;
            return false;
        }
    });
    
    if (duplicateFound) {
        $row.find('.imei-error').text('This IMEI is already added in another item').show();
        $row.find('.item-id').val('');
        $row.find('.item-description').val('');
        $row.find('.item-price').val('0.00');
        return;
    }
    
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
                $row.find('.imei-error').text('IMEI Not Found').show();
                $row.find('.item-id').val('');
            }
        },
        error: function() {
            $row.find('.imei-error').text('IMEI Not Found').show();
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
