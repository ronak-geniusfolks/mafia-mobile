@extends('layout.app')

@section('title')
    Purchase Report
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Purchases Report</h4>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-sm-12 col-md-12">
            <form action="{{ route('buy-export') }}" method="GET" id="purchaseexportreport" class="mb-3">
                <div class="form-row mb-1">
                    <select name="period" class="form-control col-10 col-md-4" id="purchasedownloadperiod">
                        <option value="alls" @selected($timePeriod == 'alls')>All Purchase</option>
                        <option value="thismonth" @selected($timePeriod == 'thismonth')>This Month</option>
                        <option value="lastmonth" @selected($timePeriod == 'lastmonth')>Last Month</option>
                        <option value="thisyear" @selected($timePeriod == 'thisyear')>This Year</option>
                        <option value="custom" @selected($timePeriod == 'custom')>Custom</option>
                    </select>
                    <input type="date" id="fromdate" name="fromdate" class="form-control col-md-3 ml-1" value="" style="display:none;">
                    <input type="date" id="todate" name="todate" class="form-control col-md-3 ml-1" value="" style="display:none;">
                </div>
                <input type="submit" value="Download Purchase Data" class="btn btn-primary waves-effect waves-light">
            </form>
        </div>
    </div>
    {{-- <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Purchases Report</h4>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-sm-12 col-md-3">
            <a href="{{ route('buy-export')}}" class="btn btn-primary"><i class="mdi mdi-file-excel-box"></i> Download Buy Excel</a>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-12 col-sm-12 col-md-3">
            <div class="card card-pricing card-pricing-recommended">
                <div class="card-body text-center" style="padding: 0.5rem;">
                    <p class="card-pricing-plan-name font-weight-bold text-uppercase" style="padding-bottom:0;">Total Purchase - {{$timePeriod}}</p>
                    <h3 class="text-white">{{$totalPurchaseAmount}}</h3>
                </div>
            </div>
        </div>
    </div> --}}
@endsection