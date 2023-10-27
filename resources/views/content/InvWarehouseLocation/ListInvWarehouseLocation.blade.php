@inject('InvWarehouseLocation', 'App\Http\Controllers\InvWarehouseLocationController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Lokasi Gudang</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Lokasi Gudang</b> <small>Mengelola Lokasi Gudang</small>
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
        <div class="form-actions float-right">
            <button onclick="location.href='{{ url('warehouse-location/add') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Lokasi Gudang Baru</button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No.</th>
                        <th width="20%" style='text-align:center'>Kode Lokasi Gudang</th>
                        <th width="20%" style='text-align:center'>Provinsi Lokasi Gudang</th>
                        <th width="20%" style='text-align:center'>Kota Lokasi Gudang</th>
                        <th width="10%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($invwarehouselocation as $warehouselocaiton)
                    <tr>
                        <td style='text-align:center'>{{$no}}.</td>
                        <td>{{$warehouselocaiton['warehouse_location_code']}}</td>
                        <td>{{$InvWarehouseLocation->getProvinceName($warehouselocaiton['province_id'])}}</td>
                        <td>{{$InvWarehouseLocation->getCityName($warehouselocaiton['city_id'])}}</td>
                        <td class="" style='text-align:center'>
                            <a type="button" class="btn btn-outline-warning btn-sm" href="{{ url('/warehouse-location/edit/'.$warehouselocaiton['warehouse_location_id']) }}">Edit</a>
                            <a type="button" class="btn btn-outline-danger btn-sm" href="{{ url('/warehouse-location/delete-warehouse-location/'.$warehouselocaiton['warehouse_location_id']) }}">Hapus</a>
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
<br>
<br>

@include('footer')

@stop

@section('css')
    
@stop

@section('js')
    
@stop