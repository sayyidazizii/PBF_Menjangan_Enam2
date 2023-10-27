@inject('InvItemUnit', 'App\Http\Controllers\InvItemUnitController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Satuan Barang</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Satuan Barang</b> <small>Mengelola Satuan Barang </small>
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
            <button onclick="location.href='{{ url('inv-item-unit/add') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Satuan Barang Baru</button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No.</th>
                        <th width="20%" style='text-align:center'>Kode Barang Satuan</th>
                        <th width="20%" style='text-align:center'>Nama Barang Satuan</th>
                        {{-- <th width="20%" style='text-align:center'>Default Quantity</th> --}}
                        <th width="10%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($invitemunit as $unit)
                    <tr>
                        <td style='text-align:center'>{{$no}}.</td>
                        <td>{{$unit['item_unit_code']}}</td>
                        <td>{{$unit['item_unit_name']}}</td>
                        {{-- <td>{{$unit['item_unit_default_quantity']}}</td> --}}
                        <td class="" style="text-align: center">
                            <a type="button" class="btn btn-outline-warning btn-sm" href="{{ url('/inv-item-unit/edit/'.$unit['item_unit_id']) }}">Edit</a>
                            <a type="button" class="btn btn-outline-danger btn-sm" href="{{ url('/inv-item-unit/delete-inv-item-unit/'.$unit['item_unit_id']) }}">Hapus</a>
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