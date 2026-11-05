@extends('layout.app')

@section('title')
    Manage Bills
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                <div class="page-title-box">
                    <h2 class="page-title font-weight-bold text-uppercase">Manage Bills</h2>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            @if (count($allBills))
                                <table class="table table-centered mb-0" id="bill-table">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="font-weight-bold">Sr No</th>
                                            <th class="font-weight-bold">Bill No.</th>
                                            <th class="font-weight-bold">Dealer Detail</th>
                                            <th class="font-weight-bold">Billing Info</th>
                                            <th class="font-weight-bold">Bill Date</th>
                                            <th class="font-weight-bold">Payment Type</th>
                                            <th class="font-weight-bold">Action</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($allBills as $key => $bill)
                                            <tr>
                                                <td><a href="{{route('bill-detail', $bill->id)}}"><b>{{ $key + 1 }}</b></a></td>
                                                <td>
                                                    <a href="{{route('bill-detail', $bill->id)}}"><b>#{{ $bill->bill_no }}</b></a>
                                                </td>
                                                <td>
                                                    <ul class="list-unstyled">
                                                        <li><b>Name:</b> {{ ucfirst($bill->dealer->name ?? 'N/A') }} </li>
                                                        @if($bill->dealer && $bill->dealer->contact_number)
                                                            <li>
                                                                <b>Contact:</b>
                                                                <a href="tel:{{$bill->dealer->contact_number}}">{{$bill->dealer->contact_number}}</a>
                                                            </li>
                                                        @endif
                                                        @if($bill->dealer && $bill->dealer->address)
                                                            <li><b>Address:</b> {{ $bill->dealer->address }} </li>
                                                        @endif
                                                        @foreach($bill->items as $item)
                                                            @if($item->purchase && $item->purchase->imei)
                                                                <li><b>IMEI:</b> {{ $item->purchase->imei }} </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                </td>
                                                <td>
                                                    <ul class="list-unstyled">
                                                        @if($bill->total_amount)
                                                            <li><b>Total Amount:</b> <span>₹{{ $bill->total_amount }}</span></li>
                                                        @endif
                                                        @if($bill->tax_amount)
                                                            <li><b>TAX:</b> <span>₹{{ $bill->tax_amount }}</span></li>
                                                        @endif
                                                        @if($bill->discount)
                                                            <li><b>Discount:</b> <span>₹{{ $bill->discount }}</span></li>
                                                        @endif
                                                        @if($bill->net_amount)
                                                            <li><b>Net Amount:</b> <span>₹{{ $bill->net_amount }}</span></li>
                                                        @endif
                                                    </ul>
                                                </td>
                                                <td>{{ date("d-m-Y", strtotime($bill->bill_date)) }}</td>
                                                <td>
                                                    <span class="badge badge-{{ $bill->payment_type === 'cash' ? 'success' : 'warning' }}">
                                                        {{ ucfirst($bill->payment_type) }}
                                                    </span>
                                                    @if($bill->payment_type === 'credit')
                                                        <br><small>Credit: ₹{{ $bill->credit_amount }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{route('bill-edit', $bill->id)}}"
                                                        class="btn  btn-blue waves-effect waves-light" title="Update">
                                                        <i class="mdi mdi-pencil"></i>
                                                    </a>
                                                    <a href="{{route('bill-detail', $bill->id)}}"
                                                        class="btn  btn-blue waves-effect waves-light" title="View">
                                                        <i class="mdi mdi-eye"></i>
                                                    </a>
                                                    <a href="{{ route('print-bill', $bill->id) }}"
                                                        class="btn btn-success waves-effect waves-light" title="print"><i
                                                            class="mdi mdi-printer"></i>
                                                    </a>
                                                    <button type="button"
                                                        class="btn btn-danger waves-effect waves-light delete-bill"
                                                        data-id="{{ $bill->id }}" title="Delete">
                                                        <i class="mdi mdi-delete"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <h4>No Bills Found</h4>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endSection

@section('scripts')
    <script>
        $(document).ready(function () {
            // Handle delete button click
            $('.delete-bill').on('click', function () {
                var billId = $(this).data('id');
                var deleteUrl = '/admin/bill/delete/' + billId;

                // Show confirmation dialog
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create a form and submit it
                        var form = $('<form method="POST" action="' + deleteUrl + '"></form>');
                        form.append('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
                        $('body').append(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@endsection

