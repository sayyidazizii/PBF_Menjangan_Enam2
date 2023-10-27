@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('preference-company') }}">Daftar Preferensi Perusahaan</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Preferensi Perusahaan</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    Form Edit Preferensi Perusahaan
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
            <button onclick="location.href='{{ url('preference-company') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-edit-preference-company')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Nama Perusahaan<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="company_name" id="company_name" value="{{$preferencecompany['company_name']}}"/>
                        <input class="form-control input-bb" type="hidden" name="company_id" id="company_id" value="{{$company_id}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Alamat Perusahaan</a>
                        <textarea rows="3" type="text" class="form-control input-bb" name="company_address" onChange="function_elements_add(this.name, this.value);" id="company_address" >{{$preferencecompany['company_address']}}</textarea>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nomor Telepon<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="company_phone_number" id="company_phone_number" value="{{$preferencecompany['company_phone_number']}}"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nomor HP<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="company_mobile_number" id="company_mobile_number" value="{{$preferencecompany['company_mobile_number']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Email<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="company_email" id="company_email" value="{{$preferencecompany['company_email']}}"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Website<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="company_website" id="company_website" value="{{$preferencecompany['company_website']}}"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No izin Apoteker<a class='red'></a></a>
                        <input class="form-control input-bb" type="text" name="pharmacist_license_no" id="pharmacist_license_no" value="{{$preferencecompany['pharmacist_license_no']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">COA Hutang</a>
                    {!! Form::select('account_payable_id',  $acctaccount, $preferencecompany['account_payable_id'], ['class' => 'selection-search-clear select-form', 'id' => 'account_payable_id']) !!}
                </div>
                <div class="col-md-6">
                    <a class="text-dark">COA Piutang</a>
                    {!! Form::select('account_receivable_id',  $acctaccount, $preferencecompany['account_receivable_id'], ['class' => 'selection-search-clear select-form', 'id' => 'account_receivable_id']) !!}
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">COA Pembulatan</a>
                    {!! Form::select('account_shortover_id',  $acctaccount, $preferencecompany['account_shortover_id'], ['class' => 'selection-search-clear select-form', 'id' => 'account_shortover_id']) !!}
                </div>
                <div class="col-md-6">
                    <a class="text-dark">COA Biaya Delivery</a>
                    {!! Form::select('account_delivery_id',  $acctaccount, $preferencecompany['account_delivery_id'], ['class' => 'selection-search-clear select-form', 'id' => 'account_delivery_id']) !!}
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">COA Kas</a>
                    {!! Form::select('account_cash_id',  $acctaccount, $preferencecompany['account_cash_id'], ['class' => 'selection-search-clear select-form', 'id' => 'account_cash_id']) !!}
                </div>
                <div class="col-md-6">
                    <a class="text-dark">COA Kas Dalam Perjalanan</a>
                    {!! Form::select('account_cash_on_way_id',  $acctaccount, $preferencecompany['account_cash_on_way_id'], ['class' => 'selection-search-clear select-form', 'id' => 'account_cash_on_way_id']) !!}
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
<br>
<br>
<br>

@include('footer')
    
@stop

@section('css')
    
@stop

@section('js')
    
@stop