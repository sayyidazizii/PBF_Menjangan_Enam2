@inject('Grading', 'App\Http\Controllers\GradingController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
      <li class="breadcrumb-item active" aria-current="page">Daftar Barang No Grade</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Barang No Grade</b>
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
        <button onclick="location.href='{{ url('grading') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
    </div>
  </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No</th>
                        <th width="13%" style='text-align:center'>Kategori Barang</th>
                        <th width="15%" style='text-align:center'>Tipe Barang</th>
                        <th width="10%" style='text-align:center'>Gudang</th>
                        <th width="8%" style='text-align:center'>Qty</th>
                        <th width="10%" style='text-align:center'>Unit</th>
                        <th width="15%" style='text-align:center'>Tanggal Datang</th>
                        <th width="15%" style='text-align:center'>Tanggal Kadaluarsa</th>
                        <th width="12%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($invitemstock as $item)
                    <tr>
                        <td style='text-align:center'>{{$no}}</td>
                        <td>{{$Grading->getInvItemCategoryName($item['item_category_id'])}}</td>
                        <td>{{$Grading->getInvItemTypeName($item['item_type_id'])}}</td>
                        <td>{{$Grading->getInvWarehouseName($item['warehouse_id'])}}</td>
                        <td style='text-align:right'>{{$item['item_total']}}</td>
                        <td>{{$Grading->getInvItemUnitName($item['item_unit_id'])}}</td>
                        <td>{{date('Y-m-d', strtotime($item['item_stock_date']))}}</td>
                        <td>{{date('Y-m-d', strtotime($item['item_stock_expired_date']))}}</td>
                        <td style='text-align:center'>
                            <a type="button" class="btn btn-outline-primary btn-sm" href="{{ url('/grading/add/'.$item['item_stock_id']) }}"><i class="fa fa-plus"></i></a>
                        </td>
                    </tr>
                    <?php $no++; ?>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
  </div>
</div>

@stop

@section('footer')
    
@stop

@section('css')
    
@stop

@section('js')
    
@stop