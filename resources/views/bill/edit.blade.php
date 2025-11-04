@extends('layout.app')

@section('title')
    Edit Bill
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
        .form-control-sm, .form-select-sm {
            font-size: 13px;
            border-radius: 6px;
        }
        #itemsContainer .item-row {
            transition: all 0.2s ease-in-out;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title font-weight-bold"> EDIT BILL : #{{ $bill->bill_no }}</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        @include('include.alert')

                        <h4 class="text-uppercase fw-semibold mb-3 border-bottom pb-2 text-primary">
                            <i class="mdi mdi-file-document-edit-outline me-1"></i> Edit Bill
                        </h4>

                        <form action="{{ route('bill-update', $bill->id) }}" method="POST"
                            class="@if(count($errors)) was-validated @endif">
                            @csrf
                            <input type="hidden" name="bill_no" value="{{ $bill->bill_no }}" />

                            <!-- Dealer Info -->
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Dealer <span class="text-danger">*</span></label>
                                    <select name="dealer_id" required class="form-control form-control-sm @error('dealer_id') parsley-error @enderror" id="dealer_id">
                                        <option value="">Select Dealer</option>
                                        @foreach($dealers as $dealer)
                                            <option value="{{ $dealer->id }}" {{ $bill->dealer_id == $dealer->id ? 'selected' : '' }}>
                                                {{ $dealer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Bill Date</label>
                                    <input type="text" name="bill_date" class="form-control form-control-sm"
                                        id="billdate" placeholder="dd/mm/yyyy"
                                        value="{{ date('d/m/Y', strtotime($bill->bill_date)) }}">
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Payment Mode <span class="text-danger">*</span></label>
                                    <select name="payment_type" class="form-control form-control-sm @error('payment_type') parsley-error @enderror" id="payment_type" required>
                                        <option value="">Select</option>
                                        <option value="cash" {{ $bill->payment_type == 'cash' ? 'selected' : '' }}>Cash</option>
                                        <option value="credit" {{ $bill->payment_type == 'credit' ? 'selected' : '' }}>Credit</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Dealer Name</label>
                                    <input type="text" readonly class="form-control form-control-sm" id="dealer_name" 
                                        value="{{ $bill->dealer->name ?? '' }}">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Contact Number</label>
                                    <input type="text" readonly class="form-control form-control-sm" id="dealer_contact" 
                                        value="{{ $bill->dealer->contact_number ?? '' }}">
                                </div>

                            </div>

                            <div class="row g-3 mt-2">
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold">Address</label>
                                    <input type="text" readonly class="form-control form-control-sm" id="dealer_address" 
                                        value="{{ $bill->dealer->address ?? '' }}">
                                </div>
                            </div>

                            <!-- Items Section -->
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

                            <!-- Tax & Summary -->
                            <div class="row mt-2 align-items-end">
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">CGST (%)</label>
                                    <input type="text" class="form-control form-control-sm" name="cgst_rate" id="cgst" value="{{ $bill->cgst_rate ?? 0 }}">
                                    <input type="hidden" id="cgstAmount" name="cgst_amount" value="{{ $bill->cgst_amount ?? 0 }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">SGST (%)</label>
                                    <input type="text" class="form-control form-control-sm" name="sgst_rate" id="sgst" value="{{ $bill->sgst_rate ?? 0 }}">
                                    <input type="hidden" id="sgstAmount" name="sgst_amount" value="{{ $bill->sgst_amount ?? 0 }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">IGST (%)</label>
                                    <input type="text" class="form-control form-control-sm" name="igst_rate" id="igst" value="{{ $bill->igst_rate ?? 0 }}">
                                    <input type="hidden" id="igstAmount" name="igst_amount" value="{{ $bill->igst_amount ?? 0 }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Discount Amount</label>
                                    <input type="number" class="form-control form-control-sm" name="discount_amount" id="discAmount" min="0" value="{{ $bill->discount ?? 0 }}">
                                </div>
                                <div class="col-md-4">
                                    <div class="bg-light p-3 mt-2 rounded shadow-sm">
                                        <ul class="mb-0 list-unstyled small">
                                            <li><b>Total Amount:</b> ₹ <span id="totalAmountDisplay">{{ $bill->total_amount ?? 0 }}</span></li>
                                            <li><b>Discount:</b> ₹ <span id="discountAmountDisplay">{{ $bill->discount ?? 0 }}</span></li>
                                            <li><b>Tax:</b> ₹ <span id="taxDisplay">{{ $bill->tax_amount ?? 0 }}</span></li>
                                            <li class="mt-1 border-top pt-1"><b>Net Amount:</b> ₹ <span id="netAmountDisplay" class="fw-bold text-success">{{ $bill->net_amount ?? 0 }}</span></li>
                                        </ul>
                                        <input type="hidden" name="total_amount" id="totalAmountInput" value="{{ $bill->total_amount ?? 0 }}">
                                        <input type="hidden" name="tax_amount" id="taxAmount" value="{{ $bill->tax_amount ?? 0 }}">
                                        <input type="hidden" name="net_amount" id="netAmount" value="{{ $bill->net_amount ?? 0 }}">
                                    </div>
                                </div>
                            </div>

                            <!-- Declaration -->
                            <div class="mt-4">
                                <label class="form-label fw-semibold">Declaration</label>
                                <input type="text" name="declaration" class="form-control form-control-sm"
                                    placeholder="Add any declaration or note" value="{{ $bill->declaration }}">
                            </div>

                            <!-- Actions -->
                            <div class="d-flex justify-content-end mt-4">
                                <a href="{{ route('allbills') }}" class="btn btn-outline-danger btn-sm me-2">
                                    <i class="mdi mdi-keyboard-backspace"></i> Back
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm ml-2">
                                    <i class="mdi mdi-check-circle-outline me-1"></i> Update
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
        const existingItems = @json($bill->items ?? []);
        
        $(document).ready(function () {
            $("#billdate").datepicker({ dateFormat: 'dd/mm/yy' });

            // Populate existing items
            if (existingItems && existingItems.length > 0) {
                existingItems.forEach(function(item) {
                    addItemRow();
                    const $row = $('#itemsContainer .item-row').last();
                    if (item.item_id) $row.find('.item-id').val(item.item_id);
                    if (item.item_description) $row.find('.item-description').val(item.item_description);
                    if (item.quantity) $row.find('.item-quantity').val(item.quantity);
                    if (item.unit_price) $row.find('.item-price').val(item.unit_price);
                    if (item.warranty_expiry_date) {
                        const date = new Date(item.warranty_expiry_date);
                        $row.find('.item-warranty').val(date.toLocaleDateString('en-GB'));
                    }
                    if (item.purchase && item.purchase.imei) {
                        $row.find('.item-imei').val(item.purchase.imei);
                    }
                    calculateItemTotal($row);
                });
            } else {
                addItemRow();
            }

            // Handle dealer selection
            $('#dealer_id').on('change', function() {
                const dealerId = $(this).val();
                if (dealerId) {
                    $.ajax({
                        url: `/admin/bill/fetch-dealer/${dealerId}`,
                        type: 'GET',
                        success: function (data) {
                            if (data.dealer) {
                                $('#dealer_name').val(data.dealer.name);
                                $('#dealer_contact').val(data.dealer.contact_number);
                                $('#dealer_address').val(data.dealer.address || '');
                            }
                        }
                    });
                }
            });


            $(document).on('click', '#addItemBtn', addItemRow);
            $(document).on('click', '.remove-item', function () {
                if ($('.item-row').length <= 1) {
                    alert('At least one item is required');
                    return;
                }
                $(this).closest('.item-row').remove();
                calculateTotals();
            });

            $(document).on('blur', '.item-imei', function () {
                searchIMEI($(this).closest('.item-row'));
            });

            $(document).on('input keyup change', '.item-price, .item-quantity', function () {
                calculateItemTotal($(this).closest('.item-row'));
            });

            $(document).on('input keyup change', '#cgst, #sgst, #igst, #discAmount', function () {
                calculateNetAmount();
            });

            calculateTotals();
        });

        function addItemRow() {
            itemCounter++;
            const rowHtml = `
                <div class="item-row border rounded p-2 mb-2 shadow-sm bg-light" data-index="${itemCounter}">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label mb-1 fw-semibold">IMEI <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm item-imei" placeholder="Search IMEI..">
                            <input type="hidden" class="item-id" name="items[${itemCounter}][item_id]">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1 fw-semibold">Warranty Expiry</label>
                            <input type="text" class="form-control form-control-sm item-warranty"
                                name="items[${itemCounter}][warranty_expiry_date]" placeholder="dd/mm/yyyy">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1 fw-semibold">Qty <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm item-quantity"
                                name="items[${itemCounter}][quantity]" value="1" min="1">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1 fw-semibold">Unit Price <span class="text-danger">*</span></label>
                            <input type="number" class="form-control form-control-sm item-price"
                                name="items[${itemCounter}][unit_price]" step="0.01" min="0" placeholder="0.00">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label mb-1 fw-semibold">Total</label>
                            <input type="text" class="form-control form-control-sm item-total" readonly value="0.00">
                        </div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-md-10">
                            <label class="form-label mb-1 fw-semibold">Description</label>
                            <textarea class="form-control form-control-sm item-description"
                                    name="items[${itemCounter}][item_description]" rows="4"></textarea>
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

            $.ajax({
                url: `/admin/bill/fetch-model/${imei}`,
                type: 'GET',
                success: function (data) {
                    if (data.purchase) {
                        $row.find('.item-id').val(data.purchase.id);
                        let desc = `Model: ${data.purchase.model}\n`;
                        if (data.purchase.color) desc += `Color: ${data.purchase.color}\n`;
                        if (data.purchase.storage) desc += `Storage: ${data.purchase.storage}\n`;
                        $row.find('.item-description').val(desc);
                        $row.find('.item-imei').val(data.purchase.imei);
                    }
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
            $('#cgstAmount').val(cgstAmt.toFixed(2));
            $('#sgstAmount').val(sgstAmt.toFixed(2));
            $('#igstAmount').val(igstAmt.toFixed(2));
            $('#taxDisplay').text(tax.toFixed(2));
            $('#taxAmount').val(tax.toFixed(2));
            $('#discountAmountDisplay').text(discount.toFixed(2));
            $('#netAmountDisplay').text(net.toFixed(2));
            $('#netAmount').val(net.toFixed(2));
        }
    </script>
@endsection

