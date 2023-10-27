@inject('CoreCustomer', 'App\Http\Controllers\CoreCustomerController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Pelanggan</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Pelanggan</b> <small>Mengelola Pelanggan</small>
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
            <button onclick="location.href='{{ url('customer/add') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Pelanggan Baru</button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No.</th>
                        {{-- <th width="20%" style='text-align:center'>Kode Pelanggan</th> --}}
                        <th width="20%" style='text-align:center'>Nama Pelanggan</th>
                        <th width="20%" style='text-align:center'>Alamat Pelanggan</th>
                        <th width="20%" style='text-align:center'>Contact Person</th>
                        <th width="10%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($corecustomer as $customer)
                    <tr>
                        <td style='text-align:center'>{{$no}}.</td>
                        {{-- <td>{{$customer['customer_code']}}</td> --}}
                        <td>{{$customer['customer_name']}}</td>
                        <td>{{$customer['customer_address']}}</td>
                        <td>{{$customer['customer_contact_person']}}</td>
                        <td class="" style='text-align:center'>
                            <a type="button" class="btn btn-outline-warning btn-sm" href="{{ url('/customer/edit/'.$customer['customer_id']) }}">Edit</a>
                            <a type="button" class="btn btn-outline-danger btn-sm" href="{{ url('/customer/delete-customer/'.$customer['customer_id']) }}">Hapus</a>
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