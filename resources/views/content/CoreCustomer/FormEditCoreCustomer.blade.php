@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />    
@section('js')
<script>
	$(document).ready(function(){
        $("#province_id").change(function(){
			var province_id 	= $("#province_id").val();
                $.ajax({
                    type: "POST",
                    url : "{{route('customer-city')}}",
                    dataType: "html",
                    data: {
                        'province_id'	: province_id,
                        '_token'        : '{{csrf_token()}}',
                    },
                    success: function(return_data){ 
					$('#city_id').html(return_data);
                        // console.log(return_data);
                    },
                    error: function(data)
                    {
                        console.log(data);

                    }
                });

		});
	});
</script>
@stop

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('inv-item') }}">Daftar Pelanggan</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Pelanggan</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Form Edit Pelanggan</b>
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
            <button onclick="location.href='{{ url('customer') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-edit-customer')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Pelanggan<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="customer_name" id="customer_name" value="{{$customer['customer_name']}}"/>
                        <input class="form-control input-bb" type="hidden" name="customer_id" id="customer_id" value="{{$customer['customer_id']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">Provinsi<a class='red'> *</a></a>
                    {!! Form::select('province_id',  $province, $customer['province_id'], ['class' => 'selection-search-clear select-form', 'id' => 'province_id']) !!}
                </div>
                <div class="col-md-6">
                    <a class="text-dark">Kota<a class='red'> *</a></a>
                    {!! Form::select('city_id',  $city, $customer['city_id'], ['class' => 'selection-search-clear select-form', 'id' => 'city_id']) !!}
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Alamat Pelanggan</a>
                        <textarea rows="3" type="text" class="form-control input-bb" name="customer_address" onChange="function_elements_add(this.name, this.value);" id="customer_address" >{{$customer['customer_address']}}</textarea>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Telp. Rumah</a>
                        <input class="form-control input-bb" type="text" name="customer_home_phone" id="customer_home_phone" value="{{$customer['customer_home_phone']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No. HP 1</a>
                        <input class="form-control input-bb" type="text" name="customer_mobile_phone1" id="customer_mobile_phone1" value="{{$customer['customer_mobile_phone1']}}"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No. HP 2</a>
                        <input class="form-control input-bb" type="text" name="customer_mobile_phone2" id="customer_mobile_phone2" value="{{$customer['customer_mobile_phone2']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No. Fax</a>
                        <input class="form-control input-bb" type="text" name="customer_fax_number" id="customer_fax_number" value="{{$customer['customer_fax_number']}}"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Email</a>
                        <input class="form-control input-bb" type="text" name="customer_email" id="customer_email" value="{{$customer['customer_email']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Contact Person</a>
                        <input class="form-control input-bb" type="text" name="customer_contact_person" id="customer_contact_person" value="{{$customer['customer_contact_person']}}"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No. Tax</a>
                        <input class="form-control input-bb" type="text" name="customer_tax_no" id="customer_tax_no" value="{{$customer['customer_tax_no']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Syarat Pembayaran</a>
                        <input class="form-control input-bb" type="text" name="customer_payment_terms" id="customer_payment_terms" value="{{$customer['customer_payment_terms']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Keterangan</a>
                        <textarea rows="3" type="text" class="form-control input-bb" name="customer_remark" onChange="function_elements_add(this.name, this.value);" id="customer_remark" >{{$customer['customer_remark']}}</textarea>
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
<br/>
<br/>

@include('footer')

@stop

@section('css')
    
@stop