@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />


@section('js')
<script>
	$(document).ready(function(){
        $("#city_id").select2("val", "0");
        $("#status_id").select2("val", "0");
	});
</script>
@stop

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('expedition') }}">Daftar Expedition</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Expedition</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Form Tambah Expedition</b>
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
            <button onclick="location.href='{{ url('expedition') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-add-expedition')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Kode Expedition<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_code" id="expedition_code" value=""/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Expedition<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_name" id="expedition_name" value=""/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Rute<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_route" id="expedition_route" value=""/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Alamat<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_address" id="expedition_address" value=""/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Kota :</a>
                        <br/>
                        {!! Form::select('city_id',  $city, 0, ['class' => 'selection-search-clear select-form', 'id' => 'city_id']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nomor Telepon<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_home_phone" id="expedition_home_phone" value=""/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nomor Handphone 1<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_mobile_phone1" id="expedition_mobile_phone1" value=""/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nomor Handphone 2<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_mobile_phone2" id="expedition_mobile_phone2" value=""/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nomor Fax<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_fax_number" id="expedition_fax_number" value=""/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Email<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_email" id="expedition_email" value=""/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Person in Charge<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_person_in_charge" id="expedition_person_in_charge" value=""/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Status :</a>
                        <br/>
                        {!! Form::select('status_id',  $status, 0, ['class' => 'selection-search-clear select-form', 'id' => 'status_id']) !!}
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Keterangan<a class='red'>*</a> :</a>
                        <textarea class="form-control input-bb" type="text" name="expedition_remark" id="expedition_remark" value="" ></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <button type="reset" name="Reset" class="btn btn-danger btn-sm" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
                <button type="submit" name="Save" class="btn btn-primary btn-sm" title="Save"><i class="fa fa-check"></i> Simpan</button>
            </div>
        </div>
    </div>
    </div>
</form>
<br/>
<br>
<br>

@include('footer')
    
@stop

@section('css')
    
@stop