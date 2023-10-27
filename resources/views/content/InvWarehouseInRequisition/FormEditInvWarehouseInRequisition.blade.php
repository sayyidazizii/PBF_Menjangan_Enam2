@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />  

@section('js')
<script>
    $("#province_id").change(function(){
        var province_id 	= $("#province_id").val();
            $.ajax({
                type: "POST",
                url : "{{route('purchase-order-city')}}",
                dataType: "html",
                data: {
                    'province_id'	: province_id,
                    '_token'        : '{{csrf_token()}}',
                },
                success: function(return_data){ 
                    $('#city_id').html(return_data);
                    console.log(return_data);
                },
                error: function(data)
                {
                    console.log(data);

                }
            });

    });

    function addLocation(){
        var warehouse_location_code 	= $("#warehouse_location_code").val();
        var province_id 	            = $("#province_id").val();
        var city_id 	                = $("#city_id").val();
        $.ajax({
            type: "POST",
            url : "{{route('warehouse-add-location')}}",
            dataType: "html",
            data: {
                'warehouse_location_code'	: warehouse_location_code,
                'province_id'	            : province_id,
                'city_id'	                : city_id,
                '_token'                    : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                $('#location_id').html(return_data);
                $('#cancel_btn_location').click();
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }
</script>
@stop

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('warehouse') }}">Daftar Gudang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Gudang</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    Form Edit Gudang
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
            <button onclick="location.href='{{ url('warehouse') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-edit-warehouse')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Kode Gudang<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="warehouse_code" id="warehouse_code" value="{{$warehouse['warehouse_code']}}"/>
                        <input class="form-control input-bb" type="hidden" name="warehouse_id" id="warehouse_id" value="{{$warehouse['warehouse_id']}}"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Gudang<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="warehouse_name" id="warehouse_name" value="{{$warehouse['warehouse_name']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Alamat<a class='red'> *</a></a>
                        <textarea rows="3" type="text" class="form-control input-bb" name="warehouse_address" onChange="function_elements_add(this.name, this.value);" id="warehouse_address" >{{$warehouse['warehouse_address']}}</textarea>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-5">
                    <a class="text-dark">Lokasi<a class='red'> *</a></a>
                    {!! Form::select('location_id',  $location, $warehouse['warehouse_location_id'], ['class' => 'selection-search-clear select-form', 'id' => 'location_id']) !!}
                </div>
                <div class="col-md-1">
                    <a class="text-dark"></a>
                    <a href='#addlocation' data-toggle='modal' name="Find" class="btn btn-success add-btn" title="Add Data">Tambah</a>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No Telp</a>
                        <input class="form-control input-bb" type="text" name="warehouse_phone" id="warehouse_phone" value="{{$warehouse['warehouse_phone']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Keterangan</a>
                        <textarea rows="3" type="text" class="form-control input-bb" name="warehouse_remark" onChange="function_elements_add(this.name, this.value);" id="warehouse_remark" >{{$warehouse['warehouse_remark']}}</textarea>
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



<div class="modal fade bs-modal-lg" id="addlocation" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"  style='text-align:left !important'>
                <h4>Form Tambah Lokasi </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Nama Lokasi </a>
                            <input class="form-control input-bb" type="text" name="warehouse_location_code" id="warehouse_location_code" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Provinsi<a class='red'> *</a></a>
                            {!! Form::select('province_id',  $province, 0, ['class' => 'selection-search-clear select-form', 'id' => 'province_id']) !!}
                        </div>
                    </div>
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Kota<a class='red'> *</a></a>
                            {!! Form::select('city_id',  $city, 0, ['class' => 'selection-search-clear select-form', 'id' => 'city_id']) !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal" id='cancel_btn_location'>Batal</button>
                <a class="btn btn-primary" onClick="addLocation()">Simpan</a>
            </div>
        </div>
    </div>
</div>

@stop

@section('footer')
    
@stop

@section('css')
    
@stop