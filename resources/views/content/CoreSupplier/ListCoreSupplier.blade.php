@inject('CoreSupplier', 'App\Http\Controllers\CoreSupplierController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Pemasok</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Pemasok</b> <small>Mengelola Pemasok</small>
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
            <button onclick="location.href='{{ url('supplier/add') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Pemasok Baru</button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No.</th>
                        {{-- <th width="20%" style='text-align:center'>Kode Pemasok</th> --}}
                        <th width="20%" style='text-align:center'>Nama Pemasok</th>
                        <th width="20%" style='text-align:center'>Alamat Pemasok</th>
                        <th width="20%" style='text-align:center'>Contact Person</th>
                        <th width="20%" style='text-align:center'>No NPWP</th>
                        <th width="20%" style='text-align:center'>Nama Akun</th>
                        <th width="20%" style='text-align:center'>Nomor Rekening</th>
                        <th width="10%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($coresupplier as $supplier)
                    <tr>
                        <td style='text-align:center'>{{$no}}.</td>
                        {{-- <td>{{$supplier['supplier_code']}}</td> --}}
                        <td>{{$supplier['supplier_name']}}</td>
                        <td>{{$supplier['supplier_address']}}</td>
                        <td>{{$supplier['supplier_contact_person']}}</td>
                        <td>{{$supplier['supplier_npwp_no']}}</td>
                        <td>{{$supplier['supplier_bank_acct_name']}}</td>
                        <td>{{$supplier['supplier_bank_acct_no']}}</td>
                        <td class="" style='text-align:center'>
                            <a type="button" class="btn btn-outline-warning btn-sm" href="{{ url('/supplier/edit/'.$supplier['supplier_id']) }}">Edit</a>
                            <a type="button" class="btn btn-outline-danger btn-sm" href="{{ url('/supplier/delete-supplier/'.$supplier['supplier_id']) }}">Hapus</a>
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