@inject('CoreCustomer', 'App\Http\Controllers\CoreCustomerController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Agensi</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Agensi</b> <small>Mengelola Agensi</small>
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
            <button onclick="location.href='{{ url('agency/add') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Agensi Baru</button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No.</th>
                        <th width="10%" style='text-align:center'>Kode Agensi</th>
                        <th width="15%" style='text-align:center'>Nama Agensi</th>
                        <th width="20%" style='text-align:center'>Alamat Agensi</th>
                        <th width="15%" style='text-align:center'>Contact Person</th>
                        <th width="15%" style='text-align:center'>No. Telp</th>
                        <th width="13%" style='text-align:center'>Presentasi Bagi Hasil</th>
                        <th width="10%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($coreagency as $agency)
                    <tr>
                        <td style='text-align:center'>{{$no}}.</td>
                        <td>{{$agency['agency_code']}}</td>
                        <td>{{$agency['agency_name']}}</td>
                        <td>{{$agency['agency_address']}}</td>
                        <td>{{$agency['agency_contact_person']}}</td>
                        <td>{{$agency['agency_phone_number']}}</td>
                        <td>{{$agency['agency_profit_sharing_percentage']}}</td>
                        <td class="" style='text-align:center'>
                            <a type="button" class="btn btn-outline-warning btn-sm" href="{{ url('/agency/edit/'.$agency['agency_id']) }}">Edit</a>
                            <a type="button" class="btn btn-outline-danger btn-sm" href="{{ url('/agency/delete-agency/'.$agency['agency_id']) }}">Hapus</a>
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
<br/>
<br/>
<br/>

@include('footer')

@stop

@section('css')
    
@stop

@section('js')
    
@stop