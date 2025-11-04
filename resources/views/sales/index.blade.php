@extends('layout.app')

@section('title')
    Manage Sales
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col">
            <div class="page-title-box">
                <h2 class="page-title font-weight-bold text-uppercase">Manage Sales</h2>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card-box">
                <a href="{{ route('allinvoices') }}" class="btn btn-sm btn-blue waves-effect waves-light float-right">
                    <i class="mdi mdi-plus-circle"></i> Add Sale
                </a>

                <h4 class="header-title mb-4">Manage Sales [{{$totalItems}}]</h4>
                <form action="{{ route('allsales') }}" method="GET" id="filtersales">
                    <div class="form-row">
                        <div class="form-group col-md-4 input-group input-group-merge">
                            <input type="text" class="form-control" placeholder="Search ..." name="search"
                                value="{{ request()->input('search') }}" id="searchsales">
                            <div class="input-group-append">
                                <button class="btn btn-dark waves-effect waves-light" type="submit">Search</button>
                                <!-- <button class="btn btn-danger waves-effect waves-light" type="reset">Reset</button> -->
                            </div>
                        </div>

                        <div class="form-group col-md-2">
                            <select name="year" class="form-control right" id="selectyear">
                                <option value="">-- Year --</option>
                                <option value="2025" @selected($year == 2025)>2025</option>
                                <option value="2024" @selected($year == 2024)>2024</option>
                                <option value="2023" @selected($year == 2023)>2023</option>
                                <option value="2022" @selected($year == 2022)>2022</option>
                            </select>
                        </div>
                        <div class="form-group col-md-2">
                            <select name="paymentType" class="form-control right" id="payments">
                                <option value="">-- Payment Type --</option>
                                <option value="Online" @selected($paymentType == 'Online')>Online</option>
                                <option value="Credit Card" @selected($paymentType == 'Credit Card')>Credit Card</option>
                                <option value="Cash" @selected($paymentType == 'Cash')>Cash</option>
                            </select>
                        </div>
                    </div>
                </form>
                @if (count($allSales))
                    <table class="table table-hover m-0 table-centered dt-responsive nowrap w-100" id="tickets-table">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ route('allsales', ['direction' => $sortDirection == 'asc' ? 'desc' : 'asc']) }}">
                                    Sr.No
                                    @if ($sortDirection == 'asc')
                                        <i class="fa fa-arrow-up"></i>
                                    @else
                                        <i class="fa fa-arrow-down"></i>
                                    @endif
                                    </a>
                                </th>
                                <th>Item Detail</th>
                                <th>Customer</th>
                                <th>Payment Mode</th>
                                <th>Sale Date</th>
                                <th>Net Amount</th>
                                <th class="hidden-sm">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($allSales as $key => $sale)
                                <tr>
                                    <td><a href="{{route('saledetail', $sale->id)}}"><b>#{{ $sale->id }}</b></a></td>
                                    <td>
                                        @if($sale->items->isEmpty())
                                            <em>No items</em>
                                        @else
                                            @foreach ($sale->items as $idx => $item)
                                                @php
                                                    $p = $item->purchase; // can be null if the record was detached later
                                                @endphp
                                                <div class="mb-2">
                                                    <div><strong>Model:</strong> {{ $p->model ?? '-' }}</div>
                                                    <div><strong>Color:</strong> {{ $p->color ?? '-' }}</div>
                                                    <div><strong>Storage:</strong> {{ $p->storage ?? '-' }}</div>
                                                    <div><strong>IMEI:</strong> {{ $p->imei ?? '-' }}</div>
                                                    <div><strong>Qty:</strong> {{ $item->qty ?? 1 }}</div>
                                                    @if($idx < $sale->items->count() - 1)
                                                        <hr class="my-2">
                                                    @endif
                                                </div>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        <ul class="list-unstyled">
                                            <li><b>Name:</b> <span> {{ $sale->customer_name }}</span></li>
                                            @if($sale->customer_no) <li><b>Contact Number:</b> <span><a href="tel:{{ $sale->customer_no }}"> {{ $sale->customer_no }}</a></span></li> @endif
                                        </ul>
                                    </td>
                                    <td>
                                        @if($sale->payment_type == 'Cash')
                                            <span class="badge badge-primary text-uppercase">{{ $sale->payment_type }}</span>
                                        @elseif($sale->payment_type == 'Online')
                                            <span class="badge badge-info text-uppercase">{{ $sale->payment_type }}</span>
                                        @elseif($sale->payment_type == 'Credit Card')
                                            <span class="badge badge-warning text-uppercase">{{ $sale->payment_type }}</span>
                                        @endif
                                    </td>
                                    <td>{{ Carbon\Carbon::parse($sale->created_at)->format('d/m/Y') }}</td>
                                    <td>₹{{ $sale->net_amount }}</td>
                                    <td>
                                        <a href="{{route('saledetail', $sale->id)}}" title="View">
                                            <i class="mdi mdi-eye mr-2 text-muted font-18 vertical-middle"></i>
                                        </a>
                                        <form method="post" action="{{route('delete-sale', $sale->id)}}" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger" onclick="sureToDelete(event)" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="pagination-links">
                        {{ $allSales->appends(request()->query())->links('vendor.pagination.bootstrap-5') }}
                    </div>
                @else
                    <h4 class="header-title mb-4">No Sales Found</h4>
                @endif
            </div>
        </div><!-- end col -->
    </div>
</div>

<script>
    function sureToDelete(e){
        if(confirm('Are You sure you want to delete this?')){
            return true;
        }else{
            e.preventDefault();
        }
    }
</script>
@endSection