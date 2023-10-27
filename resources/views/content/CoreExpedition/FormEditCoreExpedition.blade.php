@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />


@section('js')
<script>
	$(document).ready(function(){
	});
</script>
@stop

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('expedition') }}">Daftar Expedition</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Expedition</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Form Edit Expedition</b>
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
            Form Edit
        </h5>
        <div class="float-right">
            <button onclick="location.href='{{ url('expedition') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-edit-expedition')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Kode Expedition<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_code" id="expedition_code" value="{{$expedition['expedition_code']}}"/>
                        <input class="form-control input-bb" type="hidden" name="expedition_id" id="expedition_id" value="{{$expedition['expedition_id']}}"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Expedition<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_name" id="expedition_name" value="{{$expedition['expedition_name']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Rute<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_route" id="expedition_route" value="{{$expedition['expedition_route']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Alamat<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_address" id="expedition_address" value="{{$expedition['expedition_address']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Kota :</a>
                        <br/>
                        {!! Form::select('city_id',  $city, $expedition['expedition_city'], ['class' => 'selection-search-clear select-form', 'id' => 'city_id']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nomor Telepon<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_home_phone" id="expedition_home_phone" value="{{$expedition['expedition_home_phone']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nomor Handphone 1<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_mobile_phone1" id="expedition_mobile_phone1" value="{{$expedition['expedition_mobile_phone1']}}"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nomor Handphone 2<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_mobile_phone2" id="expedition_mobile_phone2" value="{{$expedition['expedition_mobile_phone2']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nomor Fax<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_fax_number" id="expedition_fax_number" value="{{$expedition['expedition_fax_number']}}"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Email<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_email" id="expedition_email" value="{{$expedition['expedition_email']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Person in Charge<a class='red'>*</a> :</a>
                        <input class="form-control input-bb" type="text" name="expedition_person_in_charge" id="expedition_person_in_charge" value="{{$expedition['expedition_person_in_charge']}}"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Status :</a>
                        <br/>
                        {!! Form::select('status_id',  $status, $expedition['expedition_status'], ['class' => 'selection-search-clear select-form', 'id' => 'status_id']) !!}
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Keterangan<a class='red'>*</a> :</a>
                        <textarea class="form-control input-bb" type="text" name="expedition_remark" id="expedition_remark" value="{{$expedition['expedition_remark']}}"/></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <button type="reset" name="Reset" class="btn btn-danger btn-sm"  onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
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