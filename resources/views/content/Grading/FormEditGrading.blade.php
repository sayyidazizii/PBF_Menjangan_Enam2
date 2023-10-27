@inject('Grading', 'App\Http\Controllers\GradingController')
@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>
	$(document).ready(function(){
        $("#package_id").select2("val", "0");
        $("#modal_item_category_id").select2("val", "0");
        $("#grade_id").select2("val", "0");
        $("#item_package_unit_id").select2("val", "0");
        $("#modal_item_unit_id").select2("val", "0");

        var elements = {!! json_encode($gradingelements) !!};

        if(!elements || elements==''){
            elements = [];
        }

        if(!elements['item_id']){
            $("#item_id").select2("val", "0");
        }

        if(!elements['item_stock_unit_id']){
            $("#item_stock_unit_id").select2("val", "0");
        }

        $("#modal_item_category_id").change(function(){
			var item_category_id 	= $("#modal_item_category_id").val();
                $.ajax({
                    type: "POST",
                    url : "{{route('inv-item-type')}}",
                    dataType: "html",
                    data: {
                        'item_category_id'			    : item_category_id,
                        '_token'                        : '{{csrf_token()}}',
                    },
                    success: function(return_data){ 
					    $('#modal_item_type_id').html(return_data);
                        // console.log(return_data);
                    },
                    error: function(data)
                    {
                        console.log(data);

                    }
                });

		});
        $("#package_id").change(function(){
			var package_id 	= $("#package_id").val();
                $.ajax({
                    type: "POST",
                    url : "{{route('package-detail-grading')}}",
                    dataType: "html",
                    data: {
                        'package_id'	: package_id,
                        '_token'        : '{{csrf_token()}}',
                    },
                    success: function(return_data){ 
                    return_data = JSON.parse(return_data);
                    return_data = JSON.parse(return_data);
                    $('#package_name').val(return_data['package_name']);
                    },
                    error: function(data)
                    {
                        console.log(data);

                    }
                });

		});
	});
    
	function processAddArrayInvItemStock(){
		var item_id				    = document.getElementById("item_id").value;
		var item_quantity		    = document.getElementById("item_quantity").value;
		var item_stock_unit_id		= document.getElementById("item_stock_unit_id").value;
		
        $.ajax({
        type: "POST",
        url : "{{route('add-grading-array')}}",
        data: {
            'item_id'			    : item_id,
            'item_quantity' 	    : item_quantity, 
            'item_stock_unit_id' 	: item_stock_unit_id, 
            '_token'                : '{{csrf_token()}}'
        },
        success: function(msg){
            location.reload();
        }
    });
	}
    
	function processAddArrayInvItemStockPackage(){
		var package_id		        = document.getElementById("package_id").value;
		var item_package_quantity	= document.getElementById("item_package_quantity").value;
		var item_package_unit_id	= document.getElementById("item_package_unit_id").value;
		
        $.ajax({
        type: "POST",
        url : "{{route('add-grading-package-array')}}",
        data: {
            'package_id' 	        : package_id, 
            'item_package_quantity' : item_package_quantity, 
            'item_package_unit_id' 	: item_package_unit_id, 
            '_token'                : '{{csrf_token()}}'
        },
        success: function(msg){
            location.reload();
        }
    });
	}

    function elements_add(name, value){
        $.ajax({
            type: "POST",
            url : "{{route('elements-add-grading')}}",
            dataType: "html",
            data: {
                'name'      : name,
                'value'	    : value,
                '_token'    : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                console.log(return_data);
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }

    function addItem(){
        var item_type_id                    = $("#modal_item_type_id").val();
        var item_category_id                = $("#modal_item_category_id").val();
        var grade_id                        = $("#grade_id").val();
        var item_unit_id                    = $("#modal_item_unit_id").val();
        var item_remark                     = $("#item_remark").val();
        var item_barcode                    = $("#item_barcode").val();
        var purchase_account_id             = $("#purchase_account_id").val();
        var purchase_return_account_id      = $("#purchase_return_account_id").val();
        var purchase_discount_account_id    = $("#purchase_discount_account_id").val();
        var sales_account_id                = $("#sales_account_id").val();
        var sales_return_account_id         = $("#sales_return_account_id").val();
        var sales_discount_account_id       = $("#sales_discount_account_id").val();
        var item_stock_id                   = $("#item_stock_id").val();
        $.ajax({
            type: "POST",
            url : "{{route('add-item-grading')}}",
            dataType: "html",
            data: {
                'item_type_id'	                : item_type_id,
                'item_category_id'	            : item_category_id,
                'grade_id'	                    : grade_id,
                'item_unit_id'	                : item_unit_id,
                'item_remark'	                : item_remark,
                'item_barcode'	                : item_barcode,
                'purchase_account_id'	        : purchase_account_id,
                'purchase_return_account_id'	: purchase_return_account_id,
                'purchase_discount_account_id'	: purchase_discount_account_id,
                'sales_account_id'	            : sales_account_id,
                'sales_return_account_id'	    : sales_return_account_id,
                'sales_discount_account_id'	    : sales_discount_account_id,
                'item_stock_id'	                : item_stock_id,
                '_token'                        : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                $('#item_id').html(return_data);
                $('#cancel_btn_item').click();
                // return_date = JSON.parse(return_data);
                // console.log(return_data);
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }

    function addItemUnit(){
        var item_unit_code              = $("#item_unit_code").val();
        var item_unit_name              = $("#modal_item_unit_name").val();
        var item_unit_default_quantity  = $("#item_unit_default_quantity").val();
        var item_unit_remark            = $("#item_unit_remark").val();
        $.ajax({
            type: "POST",
            url : "{{route('add-item-unit-grading')}}",
            dataType: "html",
            data: {
                'item_unit_code'	            : item_unit_code,
                'item_unit_name'	            : item_unit_name,
                'item_unit_default_quantity'	: item_unit_default_quantity,
                'item_unit_remark'	            : item_unit_remark,
                '_token'                        : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                $('#item_stock_unit_id').html(return_data);
                $('#item_package_unit_id').html(return_data);
                $('#cancel_btn_item_unit').click();
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
        <li class="breadcrumb-item"><a href="{{ url('grading') }}">Daftar Grading</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Grading</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    Form Edit Grade
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
            <button onclick="location.href='{{ url('grading/search-item-stock') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-edit-grading')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row">
                <h5 class="form-section"><b>Detail</b></h5>
            </div>
            <hr style="margin:0;">
            <br/>
            <div class="row form-group">
                <div class="col-md-3">
                    <div class="form-group">
                        <a class="text-dark">Kategori Barang</a>
                        <input class="form-control input-bb" type="text" name="item_category_name" id="item_category_name" value="{{$Grading->getInvItemCategoryName($invitemstock['item_category_id'])}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="item_category_id" id="item_category_id" value="{{$invitemstock['item_category_id']}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="item_stock_id" id="item_stock_id" value="{{$item_stock_id}}" readonly/>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <a class="text-dark">Tipe Barang</a>
                        <input class="form-control input-bb" type="text" name="item_type_name" id="item_type_name" value="{{$Grading->getInvItemTypeName($invitemstock['item_type_id'])}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="item_type_id" id="item_type_id" value="{{$invitemstock['item_type_id']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <a class="text-dark">Qty</a>
                        <input class="form-control input-bb" type="text" name="item_total" id="item_total" value="{{$invitemstock['item_total']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <a class="text-dark">Unit</a>
                        <input class="form-control input-bb" type="text" name="item_unit_name" id="item_unit_name" value="{{$Grading->getInvItemUnitName($invitemstock['item_unit_id'])}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="item_unit_id" id="item_unit_id" value="{{$invitemstock['item_unit_id']}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-3">
                    <div class="form-group">
                        <a class="text-dark">Grade</a>
                        <input class="form-control input-bb" type="text" name="grade_name" id="grade_name" value="{{$Grading->getCoreGradeName($invitemstock['item_id'])}}" readonly/>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <a class="text-dark">Gudang</a>
                        <input class="form-control input-bb" type="text" name="warehouse_name" id="warehouse_name" value="{{$Grading->getInvWarehouseName($invitemstock['warehouse_id'])}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="warehouse_id" id="warehouse_id" value="{{$invitemstock['warehouse_id']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Datang</a>
                        <input class="form-control input-bb" type="text" name="item_stock_date" id="item_stock_date" value="{{date('Y-m-d', strtotime($invitemstock['item_stock_date']))}}" readonly/>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Kadaluarsa</a>
                        <input class="form-control input-bb" type="text" name="item_stock_expired_date" id="item_stock_expired_date" value="{{date('Y-m-d', strtotime($invitemstock['item_stock_expired_date']))}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row">
                <h5 class="form-section"><b>Grading</b></h5>
            </div>
            <hr style="margin:0;">
            <br/>
            <div class="row form-group">
                <div class="col-md-5">
                    <div class="form-group">
                        <a class="text-dark">Grade Barang<a class='red'> *</a></a>
                        <br/>
                        {!! Form::select('item_id',  $invitem, $gradingelements['item_id'], ['class' => 'selection-search-clear select-form', 'id' => 'item_id', 'onchange' => 'elements_add(this.name, this.value)']) !!}
                    </div>
                </div>
                <div class="col-md-1">
                    <a class="text-dark"></a>
                    <a href='#additem' data-toggle='modal' name="Find" class="btn btn-success add-btn" title="Add Data">Tambah</a>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Jumlah<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="item_quantity" id="item_quantity" value="{{$gradingelements['item_quantity']}}"  onChange="elements_add(this.name, this.value);"/>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <a class="text-dark">Satuan<a class='red'> *</a></a>
                        <br/>
                        {!! Form::select('item_stock_unit_id',  $invitemunit, $gradingelements['item_stock_unit_id'], ['class' => 'selection-search-clear select-form', 'id' => 'item_stock_unit_id', 'onchange' => 'elements_add(this.name, this.value)']) !!}
                    </div>
                </div>
                <div class="col-md-1">
                    <a class="text-dark"></a>
                    <a href='#additemunit' data-toggle='modal' name="Find" class="btn btn-success add-btn" title="Add Data">Tambah</a>
                </div>
            </div>
            <div class="row">
                <h5 class="form-section"><a>Package</a></h5>
            </div>
            <br/>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Package Barang<a class='red'> *</a></a>
                        <br/>
                        {!! Form::select('package_id',  $package, 0, ['class' => 'selection-search-clear select-form', 'id' => 'package_id']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Package Barang<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="package_name" id="package_name" value="" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Jumlah<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="item_package_quantity" id="item_package_quantity" value=""/>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <a class="text-dark">Satuan<a class='red'> *</a></a>
                        <br/>
                        {!! Form::select('item_package_unit_id',  $invitemunit, 0, ['class' => 'selection-search-clear select-form', 'id' => 'item_package_unit_id']) !!}
                    </div>
                </div>
                <div class="col-md-1">
                    <a class="text-dark"></a>
                    <a href='#additemunit' data-toggle='modal' name="Find" class="btn btn-success add-btn" title="Add Data">Tambah</a>
                </div>
            </div>


            <div class="form-actions float-right">
                <a type="button" name="Save" class="btn btn-primary" title="Save" onClick='processAddArrayInvItemStockPackage()'> + Package</a>
            </div>
            <br/>
            <br/>
            <br/>

            <div class="table-responsive">
                <table class="table table-bordered table-advance table-hover">
                    <thead class="thead-light">
                        <tr>
                            <th style='text-align:center'>No.</th>
                            <th style='text-align:center'>Batch Number</th>
                            <th style='text-align:center'>Nama Package</th>
                            <th style='text-align:center'>Jumlah</th>
                            <th style='text-align:center'>Satuan</th>
                            <th style='text-align:center'>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if(!is_array($dataarraypackage)){
                                echo "<tr><th colspan='6' style='text-align  : center !important;'>Data Kosong</th></tr>";
                            } else {
                                $no =1;
                                foreach ($dataarraypackage AS $key => $val){
                                    echo"
                                        <tr>
                                            <td style='text-align  : center'>".$no."</td>
                                            <td style='text-align  : left !important;'>".$Grading->getInvItemStockBatchNumber($val['package_id'])."</td>
                                            <td style='text-align  : left !important;'>".$Grading->getPackageName($val['package_id'])."</td>
                                            <td style='text-align  : right !important;'>".$val['item_package_quantity']."</td>
                                            <td style='text-align  : left !important;'>".$Grading->getInvItemUnitName($val['item_package_unit_id'])."</td>
                                            ";?>
                                            <td style='text-align  : center'>
                                                <a href="{{route('delete-grading-package-array', ['record_id' => $key, 'item_stock_id' => $item_stock_id])}}" name='Reset' class='btn btn-danger btn-sm' onClick='javascript:return confirm(\"apakah yakin ingin dihapus ?\")'></i> Hapus</a>
                                            </td>
                                            <?php
                                            echo"
                                        </tr>
                                    ";
                                    $no++;
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>

        </div>
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <a type="button" name="Save" class="btn btn-primary" title="Save" onClick='processAddArrayInvItemStock()'>Tambah</a>
            </div>
        </div>
    </div>
    </div>
    <br/>
    <div class="card border border-dark">
        <div class="card-header border-dark bg-dark">
            <h5 class="mb-0 float-left">
                Daftar
            </h5>
        </div>
    
        <div class="card-body">
            <div class="form-body form">
                <div class="table-responsive">
                    <table class="table table-bordered table-advance table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style='text-align:center'>No.</th>
                                <th style='text-align:center'>Grade Barang</th>
                                <th style='text-align:center'>Jumlah Barang</th>
                                <th style='text-align:center'>Satuan Barang</th>
                                <th style='text-align:center'>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $item_quantity_total = 0;
                                if(!is_array($dataarray)){
                                    echo "<tr><th colspan='8' style='text-align  : center !important;'>Data Kosong</th></tr>";
                                } else {
                                    $no =1;
                                    foreach ($dataarray AS $key => $val){
                                        echo"
                                            <tr>
                                                <td style='text-align  : center'>".$no."</td>
                                                <td style='text-align  : left !important;'>".$Grading->getItemName($val['item_id'])."</td>
                                                <td style='text-align  : right !important;'>".$val['item_quantity']."</td>
                                                <td style='text-align  : left !important;'>".$Grading->getInvItemUnitName($val['item_stock_unit_id'])."</td>
                                                ";?>
                                                <td style='text-align  : center'>
                                                    <a href="{{route('delete-grading-array', ['record_id' => $key, 'item_stock_id' => $item_stock_id])}}" name='Reset' class='btn btn-danger btn-sm' onClick='javascript:return confirm(\"apakah yakin ingin dihapus ?\")'></i> Hapus</a>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="5">

<div class="portlet-body">
    <div class="panel-group accordion scrollable" id="accordion2">
        <div class="panel panel-default">
            <div class="panel-heading accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapse_<?php echo $no;?>">
                <b>Detail Package</b>
            </div>
            <div id="collapse_<?php echo $no;?>" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class = "row">
                        <div class = "col-md-12">
                            <div class="table-responsive">
                                <table class="table table-bordered table-advance table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%" style='text-align:center'>No </th>
                                            <th width="50%" style='text-align:center'>Batch Number</th>
                                            <th width="50%" style='text-align:center'>Nama Package</th>
                                            <th width="50%" style='text-align:center'>Jumlah</th>
                                            <th width="45%" style='text-align:center'>Satuan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                            $noPackage 		= 1;
                                            $total_package	= 0;

                                            if(!is_array($val['package'])){
                                                echo "<tr><th colspan='3'>Data Kosong</th></tr>";
                                            } else {
                                                foreach ($val['package'] as $keyItemPackage => $valItemPackage) {
                                                    echo "
                                                        <tr>
                                                            <td style='text-align  : center'>".$noPackage."</td>
                                                            <td>".$Grading->getInvItemStockBatchNumber($valItemPackage['package_id'])."</td>
                                                            <td>".$Grading->getPackageName($valItemPackage['package_id'])."</td>
                                                            <td style='text-align  : right'>".$valItemPackage['item_package_quantity']."</td>
                                                            <td>".$Grading->getInvItemUnitName($valItemPackage['item_package_unit_id'])."</td>
                                                        </tr>
                                                    ";

                                                    $noPackage++;
                                                }
                                            }

                                        ?>	
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

                                                <?php
                                                echo"
                                                </td>
                                            </tr>
                                        ";
                                        $item_quantity_total += $val['item_quantity'];
                                        $no++;
                                    }
                                }
                            ?>
                            <tr>
                                <td style='text-align  : center !important;' colspan='2'><b>Total</b>
                                </td>
                                <td style='text-align  : right !important;'>
                                    <b>{{$item_quantity_total}}</b>
                                    <input class="form-control input-bb" type="hidden" name="item_quantity_total" id="item_quantity_total" value="{{$item_quantity_total}}"/>
                                </td>
                                <td colspan='5'></td>
                            </tr>	
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <a href="{{route('reset-grading-array', ['item_stock_id' => $item_stock_id])}}" name='Reset' class='btn btn-danger' onClick='javascript:return confirm(\"apakah yakin ingin dihapus ?\")'><i class="fa fa-times"></i> Reset</a>
                <button type="submit" name="Save" class="btn btn-primary" title="Save"><i class="fa fa-check"></i> Simpan</button>
            </div>
        </div>
    </div>
    </form>
<br/>

<div class="modal fade bs-modal-lg" id="additem" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"  style='text-align:left !important'>
                <h4>Form Tambah Barang</h4>
            </div>
            <div class="modal-body">
                <h4 class="text-dark">Data Barang</h4>
                <hr/>
                <div class="row form-group">
                    <div class="col-md-6">
                        <a class="text-dark">Kategori<a class='red'> *</a></a>
                        {!! Form::select('modal_item_category_id',  $invitemcategory, 0, ['class' => 'selection-search-clear select-form', 'id' => 'modal_item_category_id']) !!}
                    </div>
                    <div class="col-md-6">
                        <a class="text-dark">Tipe<a class='red'> *</a></a>
                        <select class="selection-search-clear" name="modal_item_type_id" id="modal_item_type_id" style="width: 100% !important">
                        </select>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-6">
                        <a class="text-dark">Grade<a class='red'> *</a></a>
                        {!! Form::select('grade_id',  $invgrade, 0, ['class' => 'selection-search-clear select-form', 'id' => 'grade_id']) !!}
                    </div>
                    <div class="col-md-6">
                        <a class="text-dark">Satuan<a class='red'> *</a></a>
                        {!! Form::select('modal_item_unit_id',  $invitemunit, 0, ['class' => 'selection-search-clear select-form', 'id' => 'modal_item_unit_id']) !!}
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-6">
                        <div class="form-group">
                            <a class="text-dark">Barcode Barang</a>
                            <input class="form-control input-bb" type="text" name="item_barcode" id="item_barcode" value=""/>
                        </div>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-12 ">
                        <a class="text-dark">Keterangan</a>
                        <div class="">
                            <textarea rows="3" type="text" class="form-control input-bb" name="item_remark" onChange="function_elements_add(this.name, this.value);" id="item_remark" ></textarea>
                        </div>
                    </div>
                </div>
            
                <br/>
                <h4 class="text-dark">COA Barang</h4>
                <hr/>
                <div class="row form-group">
                    <div class="col-md-6">
                        <a class="text-dark">COA Pembelian</a>
                        {!! Form::select('purchase_account_id',  $acctaccountcode, 0, ['class' => 'selection-search-clear select-form', 'id' => 'purchase_account_id']) !!}
                    </div>
                    <div class="col-md-6">
                        <a class="text-dark">COA Retur Pembelian</a>
                        {!! Form::select('purchase_return_account_id',  $acctaccountcode, 0, ['class' => 'selection-search-clear select-form', 'id' => 'purchase_return_account_id']) !!}
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-6">
                        <a class="text-dark">COA Diskon Pembelian</a>
                        {!! Form::select('purchase_discount_account_id',  $acctaccountcode, 0, ['class' => 'selection-search-clear select-form', 'id' => 'purchase_discount_account_id']) !!}
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-6">
                        <a class="text-dark">COA Penjualan</a>
                        {!! Form::select('sales_account_id',  $acctaccountcode, 0, ['class' => 'selection-search-clear select-form', 'id' => 'sales_account_id']) !!}
                    </div>
                    <div class="col-md-6">
                        <a class="text-dark">COA Retur Penjualan</a>
                        {!! Form::select('sales_return_account_id',  $acctaccountcode, 0, ['class' => 'selection-search-clear select-form', 'id' => 'sales_return_account_id']) !!}
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-6">
                        <a class="text-dark">COA Diskon Penjualan</a>
                        {!! Form::select('sales_discount_account_id',  $acctaccountcode, 0, ['class' => 'selection-search-clear select-form', 'id' => 'sales_discount_account_id']) !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal" id='cancel_btn_item'>Batal</button>
                <a class="btn btn-primary" onClick="addItem()">Simpan</a>
            </div>
        </div>
    </div>
</div>

<div class="modal fade bs-modal-lg" id="additemunit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"  style='text-align:left !important'>
                <h4>Form Tambah Satuan Barang</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Kode Barang Satuan</a>
                            <input class="form-control input-bb" type="text" name="item_unit_code" id="item_unit_code" value=""/>
                        </div>
                    </div>
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Nama Barang Satuan</a>
                            <input class="form-control input-bb" type="text" name="modal_item_unit_name" id="modal_item_unit_name" value=""/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Default Quantity</a>
                            <input class="form-control input-bb" type="text" name="item_unit_default_quantity" id="item_unit_default_quantity" value=""/>
                        </div>
                    </div>
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Keterangan</a>
                            <input class="form-control input-bb" type="text" name="item_unit_remark" id="item_unit_remark" value=""/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal" id='cancel_btn_item_unit'>Batal</button>
                <a class="btn btn-primary" onClick="addItemUnit()">Simpan</a>
            </div>
        </div>
    </div>
</div>
@stop

@section('footer')
    
@stop

@section('css')
    
@stop

@section('js')
    
@stop