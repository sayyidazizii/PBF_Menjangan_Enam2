@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>
    $(document).ready(function(){
        // $("#item_category_id").select2("val", "0");
        
        var item_unit_1 = {!! json_encode($invitemtype['item_unit_1']) !!};
        var item_unit_2 = {!! json_encode($invitemtype['item_unit_2']) !!};
        var item_unit_3 = {!! json_encode($invitemtype['item_unit_3']) !!};
        
        if(item_unit_1 == null){
            $("#item_unit_1").select2("val", "0");
        }

        if(item_unit_2== null){
            $("#item_unit_2").select2("val", "0");
        }

        if(item_unit_3 == null){
            $("#item_unit_3").select2("val", "0");
        }
    
    });

    function addCategory(){
        var item_category_name 	= $("#item_category_name").val();
        $.ajax({
            type: "POST",
            url : "{{route('inv-item-type-add-category')}}",
            dataType: "html",
            data: {
                'item_category_name'	: item_category_name,
                '_token'                : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                $('#item_category_id').html(return_data);
                $('#cancel-btn-category').click();
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
        <li class="breadcrumb-item"><a href="{{ url('inv-item-type') }}">Daftar Barang </a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Barang </li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Form Edit Barang </b>
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
            <button onclick="location.href='{{ url('inv-item-type') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-edit-inv-item-type')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Barang <a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="item_type_name" id="item_type_name" value="{{$invitemtype['item_type_name']}}"/>
                        <input class="form-control input-bb" type="hidden" name="item_type_id" id="item_type_id" value="{{$item_type_id}}"/>
                    </div>
                </div>
                <div class="col-md-5">
                    <a class="text-dark">Kategori Barang<a class='red'> *</a></a>
                    <br/>
                    {!! Form::select('item_category_id',  $invitemcategory, $invitemtype['item_category_id'], ['class' => 'selection-search-clear select-form', 'id' => 'item_category_id']) !!}
                </div>
                <div class="col-md-1" style="margin-top: 0.3%">
                    <a class="text-dark"></a>
                    <a href='#addcategory' data-toggle='modal' name="Find" class="btn btn-success add-btn btn-sm" title="Add Data">Tambah</a>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Satuan 1<a class='red'> </a></a>
                        {!! Form::select('item_unit_1',  $inv_unit, $invitemtype['item_unit_1'], ['class' => 'selection-search-clear select-form', 'id' => 'item_unit_1']) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Qty Default 1<a class='red'> </a></a>
                        <input class="form-control input-bb" type="text" name="item_quantity_default_1" id="item_quantity_default_1" value="{{$invitemtype['item_quantity_default_1']}}"/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Berat 1 (Kg)<a class='red'> </a></a>
                        <input class="form-control input-bb" type="text" name="item_weight_1" id="item_weight_1" value="{{$invitemtype['item_weight_1']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Satuan 2<a class='red'> </a></a>
                        {!! Form::select('item_unit_2',  $inv_unit, $invitemtype == null ? 0 :  $invitemtype['item_unit_2'], ['class' => 'selection-search-clear select-form', 'id' => 'item_unit_2']) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Qty Default 2<a class='red'> </a></a>
                        <input class="form-control input-bb" type="text" name="item_quantity_default_2" id="item_quantity_default_2" value="{{$invitemtype['item_quantity_default_2']}}"/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Berat 2 (Kg)<a class='red'> </a></a>
                        <input class="form-control input-bb" type="text" name="item_weight_2" id="item_weight_2" value="{{$invitemtype['item_weight_2']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Satuan 3<a class='red'> </a></a>
                        {!! Form::select('item_unit_3',  $inv_unit, $invitemtype == null ? 0 : $invitemtype['item_unit_3'], ['class' => 'selection-search-clear select-form', 'id' => 'item_unit_3']) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Qty Default 3<a class='red'> </a></a>
                        <input class="form-control input-bb" type="text" name="item_quantity_default_3" id="item_quantity_default_3" value="{{$invitemtype['item_quantity_default_3']}}"/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Berat 3 (Kg)<a class='red'> </a></a>
                        <input class="form-control input-bb" type="text" name="item_weight_3" id="item_weight_3" value="{{$invitemtype['item_weight_3']}}"/>
                    </div>
                </div>
            </div>
            {{-- <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Waktu Kadaluarsa (hari)<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="item_type_expired_time" id="item_type_expired_time" value="{{$invitemtype['item_type_expired_time']}}"/>
                    </div>
                </div>
            </div> --}}
            <div class="card-footer text-muted">
                <div class="form-actions float-right">
                    <button type="reset" name="Reset" class="btn btn-danger btn-sm" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
                    <button type="submit" name="Save" class="btn btn-primary btn-sm" title="Save"><i class="fa fa-check"></i> Simpan</button>
                </div>
            </div>
        </div>
</form>

<div class="modal fade bs-modal-lg" id="addcategory" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"  style='text-align:left !important'>
                <h4>Form Tambah Kategori Barang</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">		
                        <a class="text-dark">Kategori Barang</a>
                        <input class="form-control input-bb" type="text" name="item_category_name" id="item_category_name" value=""/>
                    </div>	
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" id='cancel-btn-category'>Batal</button>
                <a class="btn btn-primary btn-sm" onClick="addCategory()">Simpan</a>
            </div>
        </div>
    </div>
</div>
<br>
<br>

@include('footer')

@stop

@section('css')
    
@stop