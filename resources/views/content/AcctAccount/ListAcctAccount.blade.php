@inject('AcctAccount', 'App\Http\Controllers\AcctAccountController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
      <li class="breadcrumb-item active" aria-current="page">Daftar Perkiraan</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Perkiraan</b> <small>Kelola Perkiraan </small>
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
            <button onclick="location.href='{{ url('account/add') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Perkiraan Baru</button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="5%" style='text-align:center'>No</th>
                        <th width="25%" style='text-align:center'>No Perkiraan</th>
                        <th width="25%" style='text-align:center'>Nama Perkiraan</th>
                        <th width="25%" style='text-align:center'>D/K</th>
                        <th width="10%" style='text-align:center'>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no =1; ?>
                    @foreach($acctaccount as $account)
                    <tr>
                        <td style='text-align:center'>{{$no}}</td>
                        <td>{{$account['account_code']}}</td>
                        <td>{{$account['account_name']}}</td>
                        <td>{{$AcctAccount->getDefaultStatus($account['account_default_status'])}}</td>
                        <td style='text-align:center'>
                            <a type="button" class="btn btn-outline-warning btn-sm"  href="{{ url('/account/edit/'.$account['account_id']) }}">Edit</a>
                            <a type="button" class="btn btn-outline-danger btn-sm"  href="{{ url('/account/delete-account/'.$account['account_id']) }}">Hapus</a>
                        </td>
                    </tr>
                    <?php $no++?>
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