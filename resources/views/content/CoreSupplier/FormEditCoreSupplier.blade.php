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
                    url : "{{route('supplier-city')}}",
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
        <li class="breadcrumb-item"><a href="{{ url('inv-item') }}">Daftar Pemasok</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Pemasok</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Form Edit Pemasok</b>
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
            <button onclick="location.href='{{ url('supplier') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-edit-supplier')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Pemasok<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="supplier_name" id="supplier_name" value="{{$supplier['supplier_name']}}"/>
                        <input class="form-control input-bb" type="hidden" name="supplier_id" id="supplier_id" value="{{$supplier['supplier_id']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">Provinsi<a class='red'> *</a></a>
                    {!! Form::select('province_id',  $province, $supplier['province_id'], ['class' => 'selection-search-clear select-form', 'id' => 'province_id']) !!}
                </div>
                <div class="col-md-6">
                    <a class="text-dark">Kota<a class='red'> *</a></a>
                    {!! Form::select('city_id',  $city, $supplier['city_id'], ['class' => 'selection-search-clear select-form', 'id' => 'city_id']) !!}
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Alamat Pemasok</a>
                        <textarea rows="3" type="text" class="form-control input-bb" name="supplier_address" onChange="function_elements_add(this.name, this.value);" id="supplier_address" >{{$supplier['supplier_address']}}</textarea>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Telp. Rumah</a>
                        <input class="form-control input-bb" type="text" name="supplier_home_phone" id="supplier_home_phone" value="{{$supplier['supplier_home_phone']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No. HP 1</a>
                        <input class="form-control input-bb" type="text" name="supplier_mobile_phone1" id="supplier_mobile_phone1" value="{{$supplier['supplier_mobile_phone1']}}"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No. HP 2</a>
                        <input class="form-control input-bb" type="text" name="supplier_mobile_phone2" id="supplier_mobile_phone2" value="{{$supplier['supplier_mobile_phone2']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No. Fax</a>
                        <input class="form-control input-bb" type="text" name="supplier_fax_number" id="supplier_fax_number" value="{{$supplier['supplier_fax_number']}}"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Email</a>
                        <input class="form-control input-bb" type="text" name="supplier_email" id="supplier_email" value="{{$supplier['supplier_email']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Contact Person</a>
                        <input class="form-control input-bb" type="text" name="supplier_contact_person" id="supplier_contact_person" value="{{$supplier['supplier_contact_person']}}"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nomor ID</a>
                        <input class="form-control input-bb" type="text" name="supplier_id_number" id="supplier_id_number" value="{{$supplier['supplier_id_number']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No. Tax</a>
                        <input class="form-control input-bb" type="text" name="supplier_tax_no" id="supplier_tax_no" value="{{$supplier['supplier_tax_no']}}"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Syarat Pembayaran</a>
                        <input class="form-control input-bb" type="text" name="supplier_payment_terms" id="supplier_payment_terms" value="{{$supplier['supplier_payment_terms']}}"/>
                    </div>
                </div>
            </div>

            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No. NPWP</a>
                        <input class="form-control input-bb" type="text" name="supplier_npwp_no" id="supplier_npwp_no" value=""/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Alamat NPWP</a>
                        <input class="form-control input-bb" type="text" name="supplier_npwp_address" id="supplier_npwp_address" value=""/>
                    </div>
                </div>
            </div>

            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Keterangan</a>
                        <textarea rows="3" type="text" class="form-control input-bb" name="supplier_remark" onChange="function_elements_add(this.name, this.value);" id="supplier_remark" >{{$supplier['supplier_remark']}}</textarea>
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
<br>
<br>

@include('footer')

@stop

@section('css')
    
@stop