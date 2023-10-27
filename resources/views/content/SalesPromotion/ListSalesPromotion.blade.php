@inject('SalesPromotion', 'App\Http\Controllers\SalesPromotionController')

@extends('adminlte::page')

@section('js')
<script>
	$(document).ready(function(){
        var customer_id    = {!! json_encode($customer_id) !!};
        
        if(customer_id == null){
            $("#customer_id").select2("val", "0");
        }
    });
</script>
@stop

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Potongan Promosi</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Potongan Promosi</b> <small>Mengelola Potongan Invoice Penjualan</small>
</h3>
<br/>
<div id="accordion">
    <form  method="post" action="{{route('filter-sales-promotion')}}" enctype="multipart/form-data">
    @csrf
        <div class="card border border-dark">
        <div class="card-header bg-dark" id="headingOne" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            <h5 class="mb-0">
                Filter
            </h5>
        </div>
    
        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
            <div class="card-body">
                <div class = "row">
                    <div class = "col-md-4">
                        <div class="form-group form-md-line-input">
                            <section class="control-label">Tanggal Mulai
                                <span class="required text-danger">
                                    *
                                </span>
                            </section>
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="start_date" id="date" onChange="function_elements_add(this.name, this.value);" value="{{$start_date}}"/>
                        </div>
                    </div>

                    <div class = "col-md-4">
                        <div class="form-group form-md-line-input">
                            <section class="control-label">Tanggal Akhir
                                <span class="required text-danger">
                                    *
                                </span>
                            </section>
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="end_date" id="date" onChange="function_elements_add(this.name, this.value);" value="{{$end_date}}"/>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <a class="text-dark">Pembeli</a>
                        <br/>
                        {!! Form::select('customer_id',  $customer, $customer_id, ['class' => 'selection-search-clear select-form', 'id' => 'customer_id']) !!}
                    </div>
                </div>
            </div>
            <div class="card-footer text-muted">
                <div class="form-actions float-right">
                    <a href="{{route('filter-reset-sales-collection-piece')}}" type="reset" name="Reset" class="btn btn-danger btn-sm" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</a>
                    <button type="submit" name="Find" class="btn btn-primary btn-sm" title="Search Data"><i class="fa fa-search"></i> Cari</button>
                    <a href="{{ url('sales-promotion/export') }}"name="Find" class="btn btn-sm btn-info" title="Export Excel"><i class="fa fa-print"></i>Export</a>
                </div>
            </div>
        </div>
        </div>
    </form>
</div>
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
        {{-- <div class="form-actions float-right">
            <button onclick="location.href='{{ url('sales-collection-piece/search-sales-collection-piece') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Potongan Promosi Invoice Baru</button>
        </div> --}}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No.</th>
                        <th width="20%" style='text-align:center'>Nama Pembeli</th>
                        <th width="20%" style='text-align:center'>No.Invoice</th>
                        <th width="20%" style='text-align:center'>No.Promosi</th>
                        <th width="20%" style='text-align:center'>No.Memo</th>
                        <th width="20%" style='text-align:center'>Tanggal Invoice</th>
                        <th width="20%" style='text-align:center'>Tanggal Claim</th>
                        <th width="20%" style='text-align:center'>Total Invoice</th>
                        <th width="20%" style='text-align:center'>Potongan</th>
                        <th width="20%" style='text-align:center'>Setelah Potongan</th>
                        <th width="20%" style='text-align:center'>Jenis</th>
                        
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($salescolectionpiece as $item)
                    <tr>
                        <td style='text-align:center'>{{$no}}.</td>
                        <td>{{$SalesPromotion->getCustomerName($item['customer_id'])}}</td>
                        <td>{{$item['sales_invoice_no']}}</td>
                        <td>{{$item['promotion_no']}}</td>
                        <td>{{$item['memo_no']}}</td>
                        <td>{{$item['sales_invoice_date']}}</td>
                        <td>{{$item['claim_date']}}</td>
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
                    </tr>
                    <?php $no++; ?>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
<br>
<br>
<br>

@include('footer')

@stop

@section('css')
    
@stop