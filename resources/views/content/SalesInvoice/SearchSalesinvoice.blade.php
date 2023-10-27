@inject('SalesOrderReturn', 'App\Http\Controllers\SalesOrderReturnController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>
function checkboxSalesOrderChange (sales_order_item_id) {
    $.ajax({
        type: "POST",
        url : "{{route('add-checkbox-sales-delivery-order')}}",
        dataType: "html",
        data: {
            'sales_order_item_id'   : sales_order_item_id,
            '_token'                : '{{csrf_token()}}',
        },
        success: function(return_data){ 
            console.log(return_data);
        },
        error: function(data)
        {
            console.log(data);

        }
    });
}
</script>
@stop

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('sales-order-return') }}">Daftar Return Penjualan</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Sales Invoice</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Sales Invoice</b>
</h3>
<br/>

@if(session('msg'))
<div class="alert alert-info" role="alert">
    {{session('msg')}}
</div>
@endif 
<div class="card border border-dark">
    <div class="card-header bg-dark clearfix">
        <h5 class="mb-0 float-left">
            Daftar
        </h5>
        <div class="float-right">
            <button onclick="location.href='{{ url('sales-order-return') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

<form method="post" action="/sales-delivery-order/add" enctype="multipart/form-data">
    @csrf
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No.</th>
                        <th width="20%" style='text-align:center'>Nama Pembeli</th>
                        <th width="20%" style='text-align:center'>No Invoice Penjualan</th>
                        <th width="20%" style='text-align:center'>Tanggal Invoice</th>
                        <th width="15%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($salesInvoice as $item)
                    <tr>
                        <td style='text-align:center'>{{$no}}</td>
                        <td>{{$SalesOrderReturn->getCustomerNameSalesOrderId($item['sales_order_id'])}}</td>
                        <td>{{$item['sales_invoice_no']}}</td>
                        <td>{{$item['sales_invoice_date']}}</td>
                        <td style='text-align:center'>
                            <a type="button" class="btn btn-outline-primary btn-sm" href="{{ url('/sales-order-return/add/'.$item['sales_invoice_id']) }}"><i class="fa fa-plus"></i></a>
                        </td>
                    </tr>
                    <?php $no++; ?>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
        
    {{-- <div class="card-footer text-muted">
        <div class="form-actions float-right">
            <button type="submit" name="Save" class="btn btn-primary" title="Save">Tambah</button>
        </div>
    </div> --}}
</div>
</div>
</form>
<br>
<br>
<br>

@include('footer')
    
@stop

@section('css')
    
@stop