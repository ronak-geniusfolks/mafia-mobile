@extends('layout.app')

@section('title')
    Multiple Stock Entry
@endsection

@section('content')
    <!-- Start Content-->
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-8">
                <div class="page-title-box">
                    <h4 class="page-title">Multiple Stock Entry</h4>
                </div>
            </div>
            <div class="col-4">
                <div class="page-title-box text-right">
                    <h4 class="page-title">
                        <a href="{{ route('allpurchases') }}" class="btn btn-sm btn-success">
                            <i class="mdi mdi-keyboard-backspace"></i> Back
                        </a>
                    </h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                @include('include.alert')
                <div class="card shadow-lg rounded-3 border-0">
                    <div class="card-body p-4">
                        <h4 class="mb-3 fw-bold text-primary">Multiple Stock Entry</h4>
                        <form class="form-horizontal" method="post" enctype="multipart/form-data" action="{{ route('purchase.store')}}" id="purchaseForm">
                            @csrf
                            <input type="hidden" name="entry_type" value="multiple">

                            {{-- Common Fields Section --}}
                            <div class="border rounded p-3 mb-4 bg-light">
                                <h5 class="mb-3 fw-bold text-secondary">Common Information</h5>
                                
                                {{-- Row 1: Device Type + Model --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="devicetype" class="form-label fw-semibold">Device Type</label>
                                        <select class="form-select form-control" id="devicetype" name="device_type" required>
                                            <option value="Phone" @selected(old('device_type') == 'Phone')>Phone</option>
                                            <option value="Tablet" @selected(old('device_type') == 'Tablet')>Tablet</option>
                                            <option value="Laptop" @selected(old('device_type') == 'Laptop')>Laptop</option>
                                            <option value="Accessories" @selected(old('device_type') == 'Accessories')>Accessories</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="model" class="form-label fw-semibold">Model <span class="text-danger">*</span></label>
                                        <input type="text" id="model" class="form-control form-control" name="model" value="{{ old('model') }}" required>
                                    </div>
                                </div>

                                {{-- Row 2: Warranty + Party Info --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="warrenty" class="form-label fw-semibold">Brand Warranty <span class="text-danger">*</span></label>
                                        <select class="form-select form-control" id="warrenty" name="warrenty" value="{{ old('warrenty') ?? 'Non warrenty' }}" required>
                                            <option value="Non warrenty" @selected(old('warrenty') == 'Non warrenty')>Non Warranty</option>
                                            <option value="Warrenty" @selected(old('warrenty') == 'Warrenty')>Warranty</option>
                                        </select>
                                        <div class="warrentydate" style="display:none;">
                                            <input type="date" id="warrentydate" class="form-control form-control mt-2" name="warrentydate"
                                                value="{{ old('warrentydate') }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="partyname" class="form-label fw-semibold">Party Name</label>
                                        <input type="text" id="partyname" class="form-control form-control" name="purchase_from"
                                            value="{{ old('purchase_from') }}">
                                    </div>
                                </div>

                                {{-- Row 3: Contact + Purchase Date --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="contactno" class="form-label fw-semibold">Party Contact No</label>
                                        <input type="text" id="contactno" class="form-control form-control" name="contactno"
                                            value="{{ old('contactno') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="purchasedate" class="form-label fw-semibold">Purchase Date <span class="text-danger">*</span></label>
                                        <input type="date" id="purchasedate" class="form-control form-control" name="purchase_date"
                                            value="{{ old('purchase_date') }}" required>
                                    </div>
                                </div>

                                {{-- Row 4: Repairing Charge + Remark --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="repairingcharge" class="form-label fw-semibold">Repairing Charge</label>
                                        <input type="number" id="repairingcharge" class="form-control form-control" name="repairing_charge"
                                            value="{{ old('repairing_charge') }}" placeholder="Enter Repairing Charge">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="remark" class="form-label fw-semibold">Remark</label>
                                        <textarea id="remark" class="form-control" name="remark"
                                            placeholder="Enter Remark">{{ old('remark') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            {{-- Stock Items Section --}}
                            <div class="border-top pt-4 mt-4">
                                <h5 class="mb-3 fw-bold text-secondary">Stock Items</h5>
                                <div id="stockItems">
                                    <div class="stock-item border rounded p-3 mb-3" data-index="0">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0 text-primary">Stock Item #1</h6>
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-stock-item" style="display: none;">
                                                <i class="fe-minus"></i>
                                            </button>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">IMEI <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="stock_items[0][imei]" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Storage (GB) <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="stock_items[0][storage]" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Color <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="stock_items[0][color]" required>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Buying Cost <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" name="stock_items[0][purchase_cost]" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="button" class="btn btn-outline-success" id="addStockItem">
                                        <i class="fe-plus me-1"></i> Add Another Stock Item
                                    </button>
                                </div>
                            </div>

                            {{-- Buttons --}}
                            <div class="text-right mt-4">
                                <button type="reset" class="btn btn-outline-danger">
                                    <i class="fe-x me-1"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-outline-success mx-2">
                                    <i class="fe-check-circle me-1"></i> Save All Stock Items
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->
    </div> <!-- container -->

@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            let stockItemIndex = 0;

            // Add stock item
            $('#addStockItem').on('click', function () {
                stockItemIndex++;
                const newItem = `
                    <div class="stock-item border rounded p-3 mb-3" data-index="${stockItemIndex}">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="mb-0 text-primary">Stock Item #${stockItemIndex + 1}</h6>
                            <button type="button" class="btn btn-sm btn-outline-danger remove-stock-item">
                                <i class="fe-minus"></i>
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">IMEI <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="stock_items[${stockItemIndex}][imei]" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Storage (GB) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="stock_items[${stockItemIndex}][storage]" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Color <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="stock_items[${stockItemIndex}][color]" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Buying Cost <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="stock_items[${stockItemIndex}][purchase_cost]" required>
                            </div>
                        </div>
                    </div>
                `;
                $('#stockItems').append(newItem);
                updateRemoveButtons();
            });

            // Remove stock item
            $(document).on('click', '.remove-stock-item', function () {
                $(this).closest('.stock-item').remove();
                updateRemoveButtons();
                updateStockItemNumbers();
            });

            // Update remove buttons visibility
            function updateRemoveButtons() {
                const stockItems = $('.stock-item');
                if (stockItems.length > 1) {
                    $('.remove-stock-item').show();
                } else {
                    $('.remove-stock-item').hide();
                }
            }

            // Update stock item numbers
            function updateStockItemNumbers() {
                $('.stock-item').each(function(index) {
                    $(this).find('h6').text(`Stock Item #${index + 1}`);
                    $(this).attr('data-index', index);
                    // Update input names
                    $(this).find('input[name*="imei"]').attr('name', `stock_items[${index}][imei]`);
                    $(this).find('input[name*="storage"]').attr('name', `stock_items[${index}][storage]`);
                    $(this).find('input[name*="color"]').attr('name', `stock_items[${index}][color]`);
                    $(this).find('input[name*="purchase_cost"]').attr('name', `stock_items[${index}][purchase_cost]`);
                });
            }

            // Warranty toggle
            $('#warrenty').on('change', function () {
                if ($(this).val() === 'Warrenty') {
                    $('.warrentydate').show();
                } else {
                    $('#warrentydate').hide();
                }
            });
        });
    </script>
@endsection
