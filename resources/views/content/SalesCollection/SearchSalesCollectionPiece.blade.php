@inject('SalesCollectionPiece', 'App\Http\Controllers\SalesCollectionPieceController')

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
        <li class="breadcrumb-item"><a href="{{ url('sales-collection-piece') }}">Daftar Claim Potongan</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Potongan Belum Di Claim</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Claim Potongan</b>
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
            <button onclick="location.href='{{ url('sales-collection-piece') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
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
                        <th hidden width="2%" style='text-align:center'>id</th>
                        <th width="20%" style='text-align:center'>Nama Pembeli</th>
                        <th width="20%" style='text-align:center'>No.Invoice</th>
                        <th width="20%" style='text-align:center'>No.Promosi</th>
                        <th width="20%" style='text-align:center'>No.Memo</th>
                        <th width="20%" style='text-align:center'>Tanggal Invoice</th>
                        <th width="20%" style='text-align:center'>Total Invoice</th>
                        <th width="20%" style='text-align:center'>Potongan</th>
                        <th width="20%" style='text-align:center'>Setelah Potongan</th>
                        <th width="20%" style='text-align:center'>Jenis</th>
                        <th width="15%" style='text-align:center'>Klaim</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($salescolectionpiece as $item)
                    <tr>
                        <td style='text-align:center'>{{$no}}.</td>
                        <td hidden>{{$item['sales_invoice_id']}}</td>
                        <td>{{$SalesCollectionPiece->getCustomerName($item['customer_id'])}}</td>
                        <td>{{$item['sales_invoice_no']}}</td>
                        <td>{{$item['promotion_no']}}</td>
                        <td>{{$item['memo_no']}}</td>
                        <td>{{$item['sales_invoice_date']}}</td>
                        <td>{{$item['total_amount']}}</td>
                        <td>{{$item['piece_amount']}}</td>
                        <td>{{$item['total_amount_after_piece']}}</td>
                        @php
                        if($item->sales_collection_piece_type_id == 1){
                          $claim = 'Promosi';
                        }
                        if($item->sales_collection_piece_type_id == 2){
                          $claim = 'biasa';
                        }
                    @endphp   
                    <td>{{ $claim }}</td>
                        <td style='text-align:center'>
                            <a type="button" class="btn btn-outline-primary btn-sm" href="{{ url('/sales-collection-piece/claim-sales-collection-piece/'.$item['sales_collection_piece_id']) }}"><i class="fas fa-check"></i></a>
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