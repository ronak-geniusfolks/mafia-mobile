@extends('layout.app')

@section('title')
    {{ isset($purchase) ? 'Edit Stock' : 'New Stock' }}
@endsection

@section('content')
    <!-- Start Content-->
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-8">
                <div class="page-title-box">
                    <h4 class="page-title">{{ isset($purchase) ? 'Edit Stock' : 'New Stock' }} Information</h4>
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
                        <h4 class="mb-3 fw-bold text-primary">Single Stock Entry</h4>
                        <form class="form-horizontal" method="post" enctype="multipart/form-data" action="{{ route('purchase.store')}}" id="purchaseForm">
                            @csrf
                            <input type="hidden" name="id" value="{{ isset($purchase) ? $purchase->id : '' }}">
                            <input type="hidden" name="entry_type" value="single">

                            {{-- Row 1: Device Type + Model --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="devicetype" class="form-label fw-semibold">Device Type</label>
                                    <select class="form-select form-control" id="devicetype" name="device_type" required>
                                        <option value="Phone" @selected(old('device_type',$purchase->device_type) == 'Phone')>Phone</option>
                                        <option value="Tablet" @selected(old('device_type',$purchase->device_type) == 'Tablet')>Tablet</option>
                                        <option value="Laptop" @selected(old('device_type',$purchase->device_type) == 'Laptop')>Laptop</option>
                                        <option value="Accessories" @selected(old('device_type',$purchase->device_type) == 'Accessories')>Accessories</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="model" class="form-label fw-semibold">Model <span class="text-danger">*</span></label>
                                    <input type="text" id="model" class="form-control form-control" name="model" value="{{ old('model',$purchase->model) }}" required>
                                </div>
                            </div>

                            {{-- Row 2: IMEI + Storage --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="imei" class="form-label fw-semibold">IMEI <span class="text-danger">*</span></label>
                                    <input type="text" id="imei" class="form-control form-control @error('imei') is-invalid @enderror" name="imei"
                                        value="{{ old('imei',$purchase->imei) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="storage" class="form-label fw-semibold">Storage (GB) <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="storage" class="form-control form-control @error('storage') is-invalid @enderror"
                                        name="storage" value="{{ old('storage',$purchase->storage) }}" required>
                                </div>
                            </div>

                            {{-- Row 3: Warranty + Color --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="warrenty" class="form-label fw-semibold">Brand Warranty <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select form-control" id="warrenty" name="warrenty" value="{{ old('warrenty',$purchase->warrenty) ?? 'Non warrenty' }}" required>
                                        <option value="Non warrenty" @selected(old('warrenty',$purchase->warrenty) == 'Non warrenty')>Non Warranty</option>
                                        <option value="Warrenty" @selected(old('warrenty',$purchase->warrenty) == 'Warrenty')>Warranty</option>
                                    </select>
                                    <div class="warrentydate" style="display:none;">
                                        <input type="date" id="warrentydate" class="form-control form-control" name="warrentydate"
                                            value="{{ old('warrentydate',$purchase->warrentydate) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="color" class="form-label fw-semibold">Color <span class="text-danger">*</span></label>
                                    <input type="text" id="color" class="form-control form-control" name="color" value="{{ old('color',$purchase->color) }}" required>
                                </div>
                            </div>

                            {{-- Row 4: Party Name + Contact --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="partyname" class="form-label fw-semibold">Party Name</label>
                                    <input type="text" id="partyname" class="form-control form-control" name="purchase_from"
                                        value="{{ old('purchase_from',$purchase->purchase_from) }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="contactno" class="form-label fw-semibold">Party Contact No</label>
                                    <input type="text" id="contactno" class="form-control form-control" name="contactno"
                                        value="{{ old('contactno',$purchase->contactno) }}">
                                </div>
                            </div>

                            {{-- Row 5: Purchase Date + Purchase Cost --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="purchasedate" class="form-label fw-semibold">Purchase Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" id="purchasedate" class="form-control form-control" name="purchase_date"
                                        value="{{ old('purchase_date',$purchase->purchase_date) }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="purchasecost" class="form-label fw-semibold">Purchase Cost <span
                                            class="text-danger">*</span></label>
                                    <input type="number" id="purchasecost" class="form-control form-control" name="purchase_cost"
                                        value="{{ old('purchase_cost',$purchase->purchase_cost) }}" required placeholder="Enter Purchase Cost">
                                </div>
                            </div>

                            {{-- Row 6: Repairing Charge + Remark --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="repairingcharge" class="form-label fw-semibold">Repairing Charge</label>
                                    <input type="number" id="repairingcharge" class="form-control form-control" name="repairing_charge"
                                        value="{{ old('repairing_charge',$purchase->repairing_charge) }}" placeholder="Enter Repairing Charge">
                                </div>
                                <div class="col-md-6">
                                    <label for="remark" class="form-label fw-semibold">Remark</label>
                                    <textarea id="remark" class="form-control" name="remark"
                                        placeholder="Enter Remark">{{ old('remark',$purchase->remark) }}</textarea>
                                </div>
                            </div>

                            {{-- Row 7: Documents (full width) --}}
                            {{-- <div class="mb-3">
                                <label for="document" class="form-label fw-semibold">Documents</label>
                                <input name="document[]" class="form-control" type="file" id="document" multiple>
                                <div id="document-preview" class="row mt-3 g-2"></div>
                            </div> --}}


                            {{-- Buttons --}}
                            <div class="text-right">
                                <button type="reset" class="btn btn-outline-danger">
                                    <i class="fe-x me-1"></i> Cancel
                                </button>
                                <button type="submit" class="btn btn-outline-success mx-2">
                                    <i class="fe-check-circle me-1"></i> {{ isset($purchase->id) ? 'Update' : 'Save' }}
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
            // Warranty toggle
            $('#warrenty').on('change', function () {
                if ($(this).val() === 'Warrenty') {
                    $('.warrentydate').show();
                } else {
                    $('#warrentydate').hide();
                }
            });

            // Multiple documents preview
            $('#document').on('change', function () {
                $('#document-preview').empty(); // clear old previews
                const files = this.files;

                if (!files.length) return;

                Array.from(files).forEach(file => {
                    const reader = new FileReader();
                    reader.onload = function (e) {
                        let preview;
                        if (file.type.startsWith('image/')) {
                            preview = `<div class="mr-2">
                                      <div class="border rounded shadow-sm p-1">
                                          <img src="${e.target.result}" class="img-fluid rounded" style="height:120px; object-fit:cover;">
                                      </div>
                                   </div>`;
                        } else if (file.type === 'application/pdf') {
                            preview = `<div class="mr-2">
                                      <div class="border rounded shadow-sm p-3 text-center bg-light">
                                          <i class="fe-file-text text-danger fs-3"></i>
                                          <p class="small mt-2">${file.name}</p>
                                      </div>
                                   </div>`;
                        } else {
                            preview = `<div class="mr-2">
                                      <div class="border rounded shadow-sm p-3 text-center bg-light">
                                          <i class="fe-file text-secondary fs-3"></i>
                                          <p class="small mt-2">${file.name}</p>
                                      </div>
                                   </div>`;
                        }
                        $('#document-preview').append(preview);
                    };
                    reader.readAsDataURL(file);
                });
            });
        });
    </script>
@endsection