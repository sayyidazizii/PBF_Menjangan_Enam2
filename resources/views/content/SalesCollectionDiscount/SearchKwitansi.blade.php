@inject('SalesCollection', 'App\Http\Controllers\SalesCollectionController')
@inject('Kwitansi', 'App\Http\Controllers\KwitansiController')


@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('sales-discount-collection') }}">Daftar Pelunasan Piutang Diskon</a></li>
      <li class="breadcrumb-item active" aria-current="page">Daftar Kwitansi</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Kwitansi</b> <small>Mengelola Kwitansi</small>
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
        <button onclick="location.href='{{ url('sales-discount-collection') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
    </div>
  </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No</th>
                        <th width="30%" style='text-align:center'>Customer</th>
                        <th width="40%" style='text-align:center'>Tanggal Kwitansi</th>
                        <th width="20%" style='text-align:center'>Periode</th>
                        <th width="20%" style='text-align:center'>No Kwitansi</th>
                        <th width="20%" style='text-align:center'>No Tagihan</th>
                        <th width="8%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($kwitansi as $item)
                    <tr>
                        <td style='text-align:center'>{{$no}}</td>
                        <td>{{ $Kwitansi->getCustomerName($item['customer_id'])}}</td>
                        <td>{{$item['sales_kwitansi_date']}}</td>
                        <td>{{$item['start_date'] }} S/D {{$item['end_date'] }}</td>
                        <td>{{ $item['sales_kwitansi_no']}}</td>
                        <td>{{ $item['sales_tagihan_no']}}</td>
                        <td style='text-align:center'>
                          <a href="/sales-discount-collection/add/{{ $item['sales_kwitansi_id'] }}" class="btn btn-outline-primary"><i class="fa fa-plus"></i></a>
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