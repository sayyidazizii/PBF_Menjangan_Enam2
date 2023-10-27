@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>
	$(document).ready(function(){
        $("#account_type_id").select2("val", "0");
        $("#account_default_status").select2("val", "3");
        $("#parent_account_id").select2("val", "0");
    });
</script>
@stop

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('account') }}">Daftar Perkiraan</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Perkiraan</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    Form Tambah No Perkiraan
</h3>
<br/>
@if(session('msg'))
<div class="alert alert-info" role="alert">
    {{session('msg')}}
</div>
@endif
@if(count($errors) > 0)
<div class="alert alert-danger" role="alert">
    @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
    @endforeach
</div>
@endif
    <div class="card border border-dark">
    <div class="card-header border-dark bg-dark">
        <h5 class="mb-0 float-left">
            Form Tambah
        </h5>
        <div class="float-right">
            <button onclick="location.href='{{ url('account') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-add-account')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No Perkiraan</a>
                        <input class="form-control input-bb" type="text" name="account_code" id="account_code" value=""/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Perkiraan</a>
                        <input class="form-control input-bb" type="text" name="account_name" id="account_name" value=""/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">Tipe Perkiraan</a>
                    <br/>
                    {!! Form::select('account_type_id',  $acctaccounttype, 0, ['class' => 'selection-search-clear select-form', 'id' => 'account_type_id']) !!}
                </div>
                <div class="col-md-6">
                    <a class="text-dark">Saldo Normal</a>
                    <br/>
                    {!! Form::select('account_default_status',  $acctaccountsettingstatus, 0, ['class' => 'selection-search-clear select-form', 'id' => 'account_default_status']) !!}
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">Parent</a>
                    <br/>
                    {!! Form::select('parent_account_id',  $acctaccountcode, 0, ['class' => 'selection-search-clear select-form', 'id' => 'parent_account_id']) !!}
                </div>
            </div>	
            <div class = "row form-group">
                <div class="col-md-12 ">
                    <a class="text-dark">Keterangan</a>
                    <div class="">
                        <textarea rows="3" type="text" class="form-control input-bb" name="account_remark" onChange="function_elements_add(this.name, this.value);" id="account_remark" ></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <button type="reset" name="Reset" class="btn btn-danger" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
                <button type="submit" name="Save" class="btn btn-primary" title="Save"><i class="fa fa-check"></i> Simpan</button>
            </div>
        </div>
    </div>
    </div>
</form>
<br>
<br>
<br>

@include('footer')
    
@stop

@section('css')
    
@stop