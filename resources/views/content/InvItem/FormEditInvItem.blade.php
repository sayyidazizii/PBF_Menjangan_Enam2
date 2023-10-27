@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />
@section('js')
<script>
	$(document).ready(function(){
        $("#item_category_id").change(function(){
			var item_category_id 	= $("#item_category_id").val();
                $.ajax({
                    type: "POST",
                    url : "{{route('inv-item-item-type')}}",
                    dataType: "html",
                    data: {
                        'item_category_id'			    : item_category_id,
                        '_token'                        : '{{csrf_token()}}',
                    },
                    success: function(return_data){ 
					$('#item_type_id').html(return_data);
                        // console.log(return_data);
                    },
                    error: function(data)
                    {
                        console.log(data);

                    }
                });

		});
	});

    function addCategory(){
        var item_category_name 	= $("#item_category_name").val();
        $.ajax({
            type: "POST",
            url : "{{route('inv-item-add-category')}}",
            dataType: "html",
            data: {
                'item_category_name'	: item_category_name,
                '_token'                : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                $('#item_category_id').html(return_data);
                $('#modal_item_category_id').html(return_data);
                $('#cancel-btn-category').click();
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }

    function addType(){
        var item_type_name 	        = $("#item_type_name").val();
        var item_category_id 	    = $("#modal_item_category_id").val();
        var item_type_expired_time 	= $("#item_type_expired_time").val();
        $.ajax({
            type: "POST",
            url : "{{route('inv-item-add-type')}}",
            dataType: "html",
            data: {
                'item_type_name'	        : item_type_name,
                'item_category_id'	        : item_category_id,
                'item_type_expired_time'	: item_type_expired_time,
                '_token'                    : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                $('#item_type_id').html(return_data);
                $('#cancel-btn-type').click();
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }

    function addGrade(){
        var grade_name = $("#grade_name").val();
        $.ajax({
            type: "POST",
            url : "{{route('inv-item-add-grade')}}",
            dataType: "html",
            data: {
                'grade_name'	 : grade_name,
                '_token'         : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                $('#grade_id').html(return_data);
                $('#cancel-btn-grade').click();
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }

    function addUnit(){
        var item_unit_code              = $("#item_unit_code").val();
        var item_unit_name              = $("#item_unit_name").val();
        var item_unit_default_quantity  = $("#item_unit_default_quantity").val();
        var item_unit_remark            = $("#item_unit_remark").val();
        $.ajax({
            type: "POST",
            url : "{{route('inv-item-add-unit')}}",
            dataType: "html",
            data: {
                'item_unit_code'	            : item_unit_code,
                'item_unit_name'	            : item_unit_name,
                'item_unit_default_quantity'	: item_unit_default_quantity,
                'item_unit_remark'	            : item_unit_remark,
                '_token'                        : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                $('#item_unit_id').html(return_data);
                $('#cancel-btn-unit').click();
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
        <li class="breadcrumb-item"><a href="{{ url('inv-item') }}">Daftar Barang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Barang</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    Form Edit Barang
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
            <button onclick="location.href='{{ url('inv-item') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-edit-inv-item')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">

            <h4 class="text-dark">Data Barang</h4>
            <hr/>
            <div class="row form-group">
                <div class="col-md-5">
                    <a class="text-dark">Kategori<a class='red'> *</a></a>
                    <br/>
                    {!! Form::select('item_category_id',  $invitemcategory, $invitem['item_category_id'], ['class' => 'selection-search-clear select-form',  'id' => 'item_category_id']) !!}
                </div>
                <div class="col-md-1" style="margin-top: 0.35%">
                    <a class="text-dark"></a>
                    <a href='#addcategory' data-toggle='modal' name="Find" class="btn btn-success add-btn btn-sm" title="Add Data">Tambah</a>
                </div>
                <div class="col-md-5">
                    <a class="text-dark">Tipe<a class='red'> *</a></a>
                    <br/>
                    {!! Form::select('item_type_id',  $invitemtype, $invitem['item_type_id'], ['class' => 'selection-search-clear select-form', 'id' => 'item_type_id']) !!}
                </div>
                <div class="col-md-1" style="margin-top: 0.35%">
                    <a class="text-dark"></a>
                    <a href='#addtype' data-toggle='modal' name="Find" class="btn btn-success add-btn btn-sm" title="Add Data">Tambah</a>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-5">
                    <a class="text-dark">Satuan<a class='red'> *</a></a>
                    <br/>
                    {!! Form::select('item_unit_id',  $invitemunit, $invitem['item_unit_id'], ['class' => 'selection-search-clear select-form', 'id' => 'item_unit_id']) !!}
                </div>
                <div class="col-md-1" style="margin-top: 0.35%">
                    <a class="text-dark"></a>
                    <a href='#addunit' data-toggle='modal' name="Find" class="btn btn-success add-btn btn-sm" title="Add Data">Tambah</a>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Barcode Barang</a>
                        <input class="form-control input-bb" type="text" name="item_barcode" id="item_barcode" value="{{$invitem['item_barcode']}}"/>
                        <input class="form-control input-bb" type="hidden" name="item_id" id="item_id" value="{{$invitem['item_id']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12 ">
                    <a class="text-dark">Keterangan</a>
                    <div class="">
                        <textarea rows="3" type="text" class="form-control input-bb" name="item_remark" onChange="function_elements_add(this.name, this.value);" id="item_remark" >{{$invitem['item_remark']}}</textarea>
                    </div>
                </div>
            </div>
            
            <br/>
            <h4 class="text-dark">COA Barang</h4>
            <hr/>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">COA Pembelian</a>
                    {!! Form::select('purchase_account_id',  $acctaccountcode, $invitem['purchase_account_id'], ['class' => 'selection-search-clear select-form', 'id' => 'purchase_account_id']) !!}
                </div>
                <div class="col-md-6">
                    <a class="text-dark">COA Retur Pembelian</a>
                    {!! Form::select('purchase_return_account_id',  $acctaccountcode, $invitem['purchase_return_account_id'], ['class' => 'selection-search-clear select-form', 'id' => 'purchase_return_account_id']) !!}
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">COA Diskon Pembelian</a>
                    {!! Form::select('purchase_discount_account_id',  $acctaccountcode, $invitem['purchase_discount_account_id'], ['class' => 'selection-search-clear select-form', 'id' => 'purchase_discount_account_id']) !!}
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">COA Penjualan</a>
                    {!! Form::select('sales_account_id',  $acctaccountcode, $invitem['sales_account_id'], ['class' => 'selection-search-clear select-form', 'id' => 'sales_account_id']) !!}
                </div>
                <div class="col-md-6">
                    <a class="text-dark">COA Retur Penjualan</a>
                    {!! Form::select('sales_return_account_id',  $acctaccountcode, $invitem['sales_return_account_id'], ['class' => 'selection-search-clear select-form', 'id' => 'sales_return_account_id']) !!}
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">COA Diskon Penjualan</a>
                    {!! Form::select('sales_discount_account_id',  $acctaccountcode, $invitem['sales_discount_account_id'], ['class' => 'selection-search-clear select-form', 'id' => 'sales_discount_account_id']) !!}
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">COA Persediaan</a>
                    {!! Form::select('inv_account_id',  $acctaccountcode, $invitem['inv_account_id'], ['class' => 'selection-search-clear select-form', 'id' => 'inv_account_id']) !!}
                </div>
                <div class="col-md-6">
                    <a class="text-dark">COA Retur Persediaan</a>
                    {!! Form::select('inv_return_account_id',  $acctaccountcode, $invitem['inv_return_account_id'], ['class' => 'selection-search-clear select-form', 'id' => 'inv_return_account_id']) !!}
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">COA Diskon Persediaan</a>
                    {!! Form::select('inv_discount_account_id',  $acctaccountcode, $invitem['inv_discount_account_id'], ['class' => 'selection-search-clear select-form', 'id' => 'inv_discount_account_id']) !!}
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">COA HPP</a>
                    {!! Form::select('hpp_account_id',  $acctaccountcode, $invitem['hpp_account_id'], ['class' => 'selection-search-clear select-form', 'id' => 'hpp_account_id']) !!}
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


<div class="modal fade bs-modal-lg" id="addtype" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"  style='text-align:left !important'>
                <h4>Form Tambah Tipe Barang</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Nama Tipe Barang</a>
                            <input class="form-control input-bb" type="text" name="item_type_name" id="item_type_name" value=""/>
                        </div>
                    </div>	
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Kategori<a class='red'> *</a></a>
                            {!! Form::select('modal_item_category_id',  $invitemcategory, 0, ['class' => 'selection-search-clear select-form', 'id' => 'modal_item_category_id']) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Waktu Kadaluarsa (hari)</a>
                            <input class="form-control input-bb" type="text" name="item_type_expired_time" id="item_type_expired_time" value=""/>
                        </div>
                    </div>	
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" id='cancel-btn-type'>Batal</button>
                <a class="btn btn-primary btn-sm" onClick="addType()">Simpan</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bs-modal-lg" id="addunit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"  style='text-align:left !important'>
                <h4>Form Tambah Satuan Barang</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Kode Satuan Barang</a>
                            <input class="form-control input-bb" type="text" name="item_unit_code" id="item_unit_code" value=""/>
                        </div>
                    </div>	
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Nama Satuan Barang</a>
                            <input class="form-control input-bb" type="text" name="item_unit_name" id="item_unit_name" value=""/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Default Quantity</a>
                            <input class="form-control input-bb" type="text" name="item_unit_default_quantity" id="item_unit_default_quantity" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Keterangan</a>
                            <input class="form-control input-bb" type="text" name="item_unit_remark" id="item_unit_remark" value=""/>
                        </div>
                    </div>		
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" id='cancel-btn-unit'>Batal</button>
                <a class="btn btn-primary btn-sm" onClick="addUnit()">Simpan</a>
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