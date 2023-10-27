@inject('InvItemType', 'App\Http\Controllers\InvItemTypeController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Barang</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Barang</b> <small>Mengelola Barang </small>
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
            <button onclick="location.href='{{ url('inv-item-type/add') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Barang Baru</button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No.</th>
                        <th width="29%" style='text-align:center'>Nama Barang</th>
                        <th width="29%" style='text-align:center'>Kategori Barang</th>
                        {{-- <th width="20%" style='text-align:center'>Waktu Kadaluarsa</th> --}}
                        <th width="20%" style='text-align:center'>Satuan 1</th>
                        <th width="20%" style='text-align:center'>Qty Default 1</th>
                        <th width="20%" style='text-align:center'>Berat 1 (Kg)</th>
                        <th width="20%" style='text-align:center'>Satuan 2</th>
                        <th width="20%" style='text-align:center'>Qty Default 2</th>
                        <th width="20%" style='text-align:center'>Berat 2 (Kg)</th>
                        <th width="20%" style='text-align:center'>Satuan 3</th>
                        <th width="20%" style='text-align:center'>Qty Default 3</th>
                        <th width="30%" style='text-align:center'>Berat 3 (Kg)</th>
                        <th width="20%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($invitemtype as $itemtype)
                    <tr>
                        <td style='text-align:center'>{{$no}}.</td>
                        <td>{{$itemtype['item_type_name']}}</td>
                        <td>{{$InvItemType->getCategoryName($itemtype['item_category_id'])}}</td>
                        <td>{{$InvItemType->getUnitName($itemtype['item_unit_1'])}}</td>
                        <td style='text-align:right'>{{$itemtype['item_quantity_default_1']}}</td>
                        <td style='text-align:right'>{{$itemtype['item_weight_1']}}</td>
                        <td>{{$InvItemType->getUnitName($itemtype['item_unit_2'])}}</td>
                        <td style='text-align:right'>{{$itemtype['item_quantity_default_2']}}</td>
                        <td style='text-align:right'>{{$itemtype['item_weight_2']}}</td>
                        <td>{{$InvItemType->getUnitName($itemtype['item_unit_3'])}}</td>
                        <td style='text-align:right'>{{$itemtype['item_quantity_default_3']}}</td>
                        <td style='text-align:right'>{{$itemtype['item_weight_3']}}</td>
                        {{-- <td>{{$itemtype['item_type_expired_time']}} hari</td> --}}
                        <td style='text-align:center'>
                            <a type="button" class="btn btn-outline-warning btn-sm" href="{{ url('/inv-item-type/edit/'.$itemtype['item_type_id']) }}">Edit</a>
                            <a type="button" class="btn btn-outline-danger btn-sm" href="{{ url('/inv-item-type/delete-inv-item-type/'.$itemtype['item_type_id']) }}">Hapus</a>
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