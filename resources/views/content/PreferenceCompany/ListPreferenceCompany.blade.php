@inject('SystemUser', 'App\Http\Controllers\SystemUserController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Preferensi Perusahaan</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Preferensi Perusahaan</b> <small>Mengelola Preferensi Perusahaan </small>
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
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No</th>
                        <th width="20%" style='text-align:center'>Nama Perusahaan</th>
                        <th width="20%" style='text-align:center'>Alamat</th>
                        <th width="10%" style='text-align:center'>No Telepon</th>
                        <th width="10%" style='text-align:center'>No HP</th>
                        <th width="15%" style='text-align:center'>Email</th>
                        <th width="15%" style='text-align:center'>Website</th>
                        <th width="15%" style='text-align:center'>No Izin Apoteker</th>
                        <th width="8%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($preferencecompany as $company)
                    <tr>
                        <td style='text-align:center'><?php echo $no ?></td>
                        <td>{{$company['company_name']}}</td>
                        <td>{{$company['company_address']}}</td>
                        <td>{{$company['company_phone_number']}}</td>
                        <td>{{$company['company_mobile_number']}}</td>
                        <td>{{$company['company_email']}}</td>
                        <td>{{$company['company_website']}}</td>
                        <td>{{$company['pharmacist_license_no']}}</td>
                        <td style='text-align:center'>
                            <a type="button" class="btn btn-outline-warning btn-sm" href="{{ url('/preference-company/edit/'.$company['company_id']) }}">Edit</a>
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
<br>

@include('footer')
    
@stop

@section('css')
    
@stop

@section('js')
    
@stop