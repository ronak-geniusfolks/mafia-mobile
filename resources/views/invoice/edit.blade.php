@extends('layout.app')

@section('title')
    Edit Invoice
@endsection

@section('css')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/ui-lightness/jquery-ui.css">
    <style>
        .card {
            border-radius: 10px;
        }

        .form-label {
            color: #2c3e50;
            font-weight: 600;
            font-size: 13px;
        }

        .form-control-sm,
        .form-select-sm {
            font-size: 13px;
            border-radius: 6px;
        }

        input.form-control-sm,
        select.form-select-sm {
            border: 1px solid #dee2e6;
        }

        input.form-control-sm:focus,
        select.form-select-sm:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.15rem rgba(0, 123, 255, 0.1);
        }

        #itemsContainer .item-row {
            transition: all 0.2s ease-in-out;
        }

        #itemsContainer .item-row:hover {
            border-color: #007bff40;
            box-shadow: 0 2px 6px rgba(0, 123, 255, 0.1);
        }

        .btn-sm {
            font-size: 13px;
            border-radius: 6px;
        }
    </style>
@endsection

@section('content')

    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-6">
                <div class="page-title-box">
                    <h4 class="page-title font-weight-bold"> EDIT INVOICE : #{{ $invoice->invoice_no }}</h4>
                </div>
            </div>
            <div class="col-6 d-flex justify-content-end align-items-center">
                <a href="{{ route('allinvoices') }}" class="btn btn-sm btn-success"><i class="mdi mdi-keyboard-backspace"></i> Back</a>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        @include('include.alert')

                        <h4 class="text-uppercase fw-semibold mb-3 border-bottom pb-2 text-primary">
                            <i class="mdi mdi-file-document-edit-outline me-1"></i> Edit Invoice
                        </h4>

                        <form action="{{ route('invoice-update', $invoice->id) }}" method="POST"
                            class="@if(count($errors)) was-validated @endif">
                            @csrf
                            <input type="hidden" name="invoice_no" value="{{ $invoice->invoice_no }}" />

                            <!-- Customer Info -->
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Customer Name <span class="text-danger">*</span></label>
                                    <input type="text" required placeholder="Customer Name" name="customer_name"
                                        class="form-control form-control-sm @error('customer_name') parsley-error @enderror"
                                        id="customer_name" value="{{ $invoice->customer_name }}">
                                    @error('customer_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Contact No <span class="text-danger">*</span></label>
                                    <input type="text" name="customer_no" class="form-control form-control-sm"
                                        id="customer_no" maxlength="10" placeholder="Customer mobile"
                                        value="{{ $invoice->customer_no }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Invoice Date</label>
                                    <input type="text" name="invoice_date" class="form-control form-control-sm"
                                        id="invoicedate" placeholder="dd/mm/yyyy"
                                        value="{{ date('d/m/Y', strtotime($invoice->invoice_date)) }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                                    <select name="payment_type"
                                        class="form-control form-control-sm @error('payment_type') parsley-error @enderror"
                                        id="payment_type" required>
                                        <option value="">Select</option>
                                        <option value="Cash" @selected($invoice->payment_type == 'Cash')>Cash</option>
                                        <option value="Online" @selected($invoice->payment_type == 'Online')>Online/UPI
                                        </option>
                                        <option value="Credit Card" @selected($invoice->payment_type == 'Credit Card')>Credit
                                            Card</option>
                                    </select>
                                </div>

                                <div class="col-md-12 mt-2">
                                    <label class="form-label fw-semibold">Address</label>
                                    <input type="text" name="customer_address" class="form-control form-control-sm"
                                        id="customer_address" placeholder="Customer Address"
                                        value="{{ $invoice->customer_address }}">
                                </div>
                            </div>

                            <!-- Item Details -->
                            <div class="mt-4">
                                <h5 class="text-uppercase fw-semibold mb-2 border-bottom pb-1 text-secondary">
                                    <i class="mdi mdi-package-variant-closed me-1"></i> Item Details
                                </h5>

                                <div id="itemsContainer" class="mb-3"></div>

                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-sm btn-outline-success" id="addItemBtn">
                                        <i class="mdi mdi-plus-circle-outline me-1"></i> Add Item
                                    </button>
                                </div>
                            </div>

                            <!-- Taxes and Summary -->
                            <div class="row mt-2 align-items-end">
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">CGST (%)</label>
                                    <input type="text" class="form-control form-control-sm" placeholder="CGST Rate"
                                        name="cgst_rate" id="cgst" value="{{ $invoice->cgst_rate ?? 0 }}">
                                    <input type="hidden" id="cgstAmount" name="cgst_amount"
                                        value="{{ $invoice->cgst_amount ?? 0 }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">SGST (%)</label>
                                    <input type="text" class="form-control form-control-sm" placeholder="SGST Rate"
                                        name="sgst_rate" id="sgst" value="{{ $invoice->sgst_rate ?? 0 }}">
                                    <input type="hidden" id="sgstAmount" name="sgst_amount"
                                        value="{{ $invoice->sgst_amount ?? 0 }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">IGST (%)</label>
                                    <input type="text" class="form-control form-control-sm" placeholder="IGST Rate"
                                        name="igst_rate" id="igst" value="{{ $invoice->igst_rate ?? 0 }}">
                                    <input type="hidden" id="igstAmount" name="igst_amount"
                                        value="{{ $invoice->igst_amount ?? 0 }}">
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Discount Amount</label>
                                    <input type="number" class="form-control form-control-sm" placeholder="Discount"
                                        name="discount_amount" id="discAmount" min="0"
                                        value="{{ $invoice->discount ?? 0 }}">
                                </div>

                                <div class="col-md-4">
                                    <div class="bg-light p-3 rounded shadow-sm">
                                        <ul class="mb-0 list-unstyled small">
                                            <li><b>Total Amount:</b> ₹ <span
                                                    id="totalAmountDisplay">{{ $invoice->total_amount ?? 0 }}</span></li>
                                            <li><b>Discount:</b> ₹ <span
                                                    id="discountAmount">{{ $invoice->discount ?? 0 }}</span></li>
                                            <li><b>Tax:</b> ₹ <span
                                                    id="taxDisplay">{{ ($invoice->cgst_amount ?? 0) + ($invoice->sgst_amount ?? 0) + ($invoice->igst_amount ?? 0) }}</span>
                                            </li>
                                            <li class="mt-1 border-top pt-1">
                                                <b>Net Amount:</b> ₹
                                                <span id="netAmountDisplay"
                                                    class="fw-bold text-success">{{ $invoice->net_amount ?? 0 }}</span>
                                            </li>
                                        </ul>
                                        <input type="hidden" name="total_amount" id="totalAmountInput"
                                            value="{{ $invoice->total_amount ?? 0 }}">
                                        <input type="hidden" name="tax_amount" id="taxAmount"
                                            value="{{ ($invoice->cgst_amount ?? 0) + ($invoice->sgst_amount ?? 0) + ($invoice->igst_amount ?? 0) }}">
                                        <input type="hidden" name="net_amount" id="netAmount"
                                            value="{{ $invoice->net_amount ?? 0 }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Declaration -->
                            <div class="mt-4">
                                <label class="form-label fw-semibold">Declaration</label>
                                <input type="text" name="declaration" class="form-control form-control-sm"
                                    placeholder="Add any declaration or note" value="{{ $invoice->declaration ?? '' }}">
                            </div>

                            <!-- Actions -->
                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('allinvoices') }}" class="btn btn-outline-danger btn-sm me-2">
                                    <i class="mdi mdi-keyboard-backspace"></i> Back
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm ml-2">
                                    <i class="mdi mdi-check-circle-outline me-1"></i> Update Invoice
                                </button>
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

        $(document).ready(function () {
            $("#invoicedate").datepicker({ dateFormat: 'dd/mm/yy' });

            // Load existing items
            existingItems.forEach(function (item) {
                addItemRow(item);
            });

            // If no items exist, add one empty row
            if (existingItems.length === 0) {
                addItemRow();
            }

            $(document).on('click', '#addItemBtn', function () {
                addItemRow();
            });

            $(document).on('click', '.remove-item', function () {
                if ($('.item-row').length <= 1) {
                    alert('At least one item is required in the invoice');
                    return;
                }
                $(this).closest('.item-row').remove();
                calculateTotals();
            });

            $('form').on('submit', function (e) {
                if ($('.item-row').length === 0) {
                    e.preventDefault();
                    alert('Please add at least one item to the invoice');
                    return false;
                }
            });

            $(document).on('blur', '.item-imei', function () {
                searchIMEI($(this).closest('.item-row'));
            });

            $(document).on('input', '.item-imei', function () {
                const $row = $(this).closest('.item-row');
                const imei = $(this).val();

                $row.find('.imei-error').hide();

                let duplicateFound = false;
                $('.item-row').each(function () {
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

            $(document).on('keydown', '.item-imei', function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    searchIMEI($(this).closest('.item-row'));
                }
            });

            $(document).on('input keyup change', '.item-price, .item-quantity', function () {
                calculateItemTotal($(this).closest('.item-row'));
            });

            $(document).on('input keyup change', '#cgst, #sgst, #igst, #discAmount', function () {
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

            const rowHtml = `
            <div class="item-row border rounded p-2 mb-2 shadow-sm bg-light" data-index="${itemCounter}" style="font-size: 14px;">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label mb-1 fw-semibold">IMEI <span class="text-danger">*</span> <small class="text-danger imei-error" style="display:none;"></small></label>
                        <input type="text" class="form-control form-control-sm item-imei" placeholder="Search IMEI.." value="${imei}">
                        <input type="hidden" class="item-id" name="items[${itemCounter}][item_id]" value="${itemId}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1 fw-semibold">Warranty Expiry</label>
                        <input type="text" class="form-control form-control-sm item-warranty"
                            name="items[${itemCounter}][warranty_expiry_date]" placeholder="dd/mm/yyyy" value="${warrantyDate}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1 fw-semibold">Qty <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm item-quantity"
                            name="items[${itemCounter}][quantity]" min="1" value="${quantity}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1 fw-semibold">Unit Price <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-sm item-price"
                            name="items[${itemCounter}][unit_price]" step="0.01" min="0" value="${unitPrice}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label mb-1 fw-semibold">Total</label>
                        <input type="text" class="form-control form-control-sm item-total" readonly value="${total}">
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-md-10">
                        <label class="form-label mb-1 fw-semibold">Description</label>
                        <textarea class="form-control form-control-sm item-description"
                                name="items[${itemCounter}][item_description]" rows="4"
                                placeholder="Enter item description...">${description}</textarea>
                    </div>
                    <div class="col-md-2 d-flex align-items-end justify-content-end mt-2">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                            <i class="mdi mdi-delete"></i> Remove
                        </button>
                    </div>
                </div>
            </div>`;

            $('#itemsContainer').append(rowHtml);
            $('.item-warranty').last().datepicker({ dateFormat: 'dd/mm/yy' });
        }

        function searchIMEI($row) {
            const imei = $row.find('.item-imei').val();
            if (!imei) return;

            let duplicateFound = false;
            $('.item-row').each(function () {
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
                success: function (data) {
                    if (data.purchase) {
                        $row.find('.imei-error').hide();
                        $row.find('.item-id').val(data.purchase.id);
                        let desc = `Model: ${data.purchase.model}\n`;
                        if (data.purchase.color) desc += `Color: ${data.purchase.color}\n`;
                        if (data.purchase.storage) desc += `Storage: ${data.purchase.storage}\n`;
                        if (data.purchase.imei) desc += `IMEI: ${data.purchase.imei}\n`;
                        $row.find('.item-description').val(desc);
                        $row.find('.item-price').val(data.purchase.purchase_price).trigger('input');
                    } else {
                        $row.find('.imei-error').text(data.error).show();
                        setTimeout(() => {
                            $row.find('.item-imei').val('');
                            $row.find('.imei-error').hide();
                        }, 3000);
                        $row.find('.item-id').val('');
                    }
                },
                error: function () {
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
            $('.item-total').each(function () {
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