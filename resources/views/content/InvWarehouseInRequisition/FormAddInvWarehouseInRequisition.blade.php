@inject('InvWarehouseInRequisition', 'App\Http\Controllers\InvWarehouseInRequisitionController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>

    function elements_add(name, value){
        $.ajax({
            type: "POST",
            url : "{{route('elements-add-warehouse-in-requisition')}}",
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

	$(document).ready(function(){
        $("#item_stock_id").select2("val", "0");
        $("#item_unit_id").select2("val", "0");

        var elements = {!! json_encode($warehouseinelements) !!};

        if(!elements || elements==''){
            elements = [];
        }

        if(!elements['warehouse_id']){
            $("#warehouse_id").select2("val", "0");
        }

        if(!elements['warehouse_in_type_id']){
            $("#warehouse_in_type_id").select2("val", "0");
        }
        

        $("#item_stock_id").change(function(){
            var item_stock_id 	= $("#item_stock_id").val();

            $.ajax({
                type: "POST",
                url : "{{route('warehouse-in-item-stock-detail')}}",
                dataType: "html",
                data: {
                    'item_stock_id'	    : item_stock_id,
                    '_token'            : '{{csrf_token()}}',
                },
                success: function(return_data){ 
                    return_data = JSON.parse(return_data);
                    return_data = JSON.parse(return_data);
                    $('#default_quantity').val(return_data['item_total']);
                    $('#item_name').val(return_data['item_name']);
                    $('#item_unit_name').val(return_data['item_unit_name']);
                },
                error: function(data)
                {
                    console.log(data);
                }
            });
        });
    });
    
	function processAddArrayWarehouseInRequisitionItem(){
		var item_stock_id	= document.getElementById("item_stock_id").value;
		var quantity		= document.getElementById("quantity").value;
		var item_unit_id	= document.getElementById("item_unit_id").value;
		
        $.ajax({
        type: "POST",
        url : "{{route('warehouse-in-requisition-add-array')}}",
        data: {
            'item_stock_id'		: item_stock_id,
            'quantity' 	        : quantity, 
            'item_unit_id' 	    : item_unit_id, 
            '_token'            : '{{csrf_token()}}'
        },
        success: function(msg){
            location.reload();
        }
    });
	}
</script>
@stop

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('warehouse-in-requisition') }}">Daftar Permintaan Penambahan Gudang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Permintaan Penambahan Gudang</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    Form Tambah Permintaan Penambahan Gudang
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
            <button onclick="location.href='{{ url('warehouse-in-requisition') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-add-warehouse-in-requisition')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Gudang<a class='red'> *</a></a>
                        {!! Form::select('warehouse_id',  $invwarehouse, $warehouseinelements == null ? '' : $warehouseinelements['warehouse_id'], ['class' => 'selection-search-clear select-form', 'id' => 'warehouse_id', 'onchange' => 'elements_add(this.name, this.value)']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tipe Penambahan Gudang<a class='red'> *</a></a>
                        {!! Form::select('warehouse_in_type_id',  $invwarehouseintype, $warehouseinelements == null ? '' : $warehouseinelements['warehouse_in_type_id'], ['class' => 'selection-search-clear select-form', 'id' => 'warehouse_in_type_id', 'onchange' => 'elements_add(this.name, this.value)']) !!}
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group form-md-line-input">
                        <section class="control-label">Tanggal
                            <span class="required text-danger">
                                *
                            </span>
                        </section>
                        <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="warehouse_in_requisition_date" id="date" onChange="elements_add(this.name, this.value);" value="{{$warehouseinelements == null ? '' : $warehouseinelements['warehouse_in_requisition_date']}}" style="width: 15rem;"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Keterangan</a>
                        <textarea rows="3" type="text" class="form-control input-bb" name="warehouse_in_remark" onChange="elements_add(this.name, this.value);" id="warehouse_in_remark" >{{$warehouseinelements == null ? '' : $warehouseinelements['warehouse_in_remark']}}</textarea>
                    </div>
                </div>
            </div>
            <div class="row">
                <h5 class="form-section"><b>Tambah Barang</b></h5>
            </div>
            <hr style="margin:0;">
            <br/>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Barang</a>
                        <br/>
                        {{-- {!! Form::select('item_stock_id',  $invitemstock, 0, ['class' => 'selection-search-clear select-form', 'id' => 'item_stock_id']) !!} --}}
                        <select class="selection-search-clear" name="item_stock_id" id="item_stock_id" style="width: 100% !important">
                            @foreach($invitemstock as $item)
                                <option value="{{$item['item_stock_id']}}">{{$item['item_batch_number']}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Barang</a>
                        <input class="form-control input-bb" type="text" name="item_name" id="item_name" value="" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Jumlah</a>
                        <input class="form-control input-bb" type="text" name="quantity" id="quantity" value=""/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Jumlah Tersedia</a>
                        <input class="form-control input-bb" type="text" name="default_quantity" id="default_quantity" value="" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Satuan Barang</a>
                        <br/>
                        {!! Form::select('item_unit_id',  $invitemunit, 0, ['class' => 'selection-search-clear select-form', 'id' => 'item_unit_id']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Satuan Default</a>
                        <input class="form-control input-bb" type="text" name="item_unit_name" id="item_unit_name" value="" readonly/>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <a type="button" name="Save" class="btn btn-primary btn-sm" title="Save" onClick='processAddArrayWarehouseInRequisitionItem()'>Tambah</a>
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
                                <th style='text-align:center'>Nama Barang</th>
                                <th style='text-align:center'>Jumlah</th>
                                <th style='text-align:center'>Satuan Barang</th>
                                <th style='text-align:center'>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if(!is_array($dataarray)){
                                    echo "<tr><th colspan='6' style='text-align  : center !important;'>Data Kosong</th></tr>";
                                } else {
                                    $no =1;
                                    foreach ($dataarray AS $key => $val){
                                        echo"
                                            <tr>
                                                <td style='text-align  : center'>".$no."</td>
                                                <td style='text-align  : left !important;'>".$InvWarehouseInRequisition->getItemName($val['item_stock_id'])."</td>
                                                <td style='text-align  : right !important;'>".$val['quantity']."</td>
                                                <td style='text-align  : left !important;'>".$InvWarehouseInRequisition->getItemUnitName($val['item_unit_id'])."</td>";
                                                ?>
                                                
                                                <td style='text-align  : center'>
                                                    <a href="{{route('warehouse-in-requisition-delete-array', ['record_id' => $key])}}" name='Reset' class='btn btn-danger btn-sm' onClick='javascript:return confirm(\"apakah yakin ingin dihapus ?\")'></i> Hapus</a>
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
        </div>
        
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <a href="{{route('reset-array-warehouse-in-requisition')}}" name='Reset' class='btn btn-danger btn-sm' onClick='javascript:return confirm(\"apakah yakin ingin dihapus ?\")'><i class="fa fa-times"></i> Reset</a>
                <button type="submit" name="Save" class="btn btn-primary btn-sm" title="Save"><i class="fa fa-check"></i> Simpan</button>
            </div>
        </div>
    </div>
    </form>
<br/>

@stop

@section('footer')
    
@stop

@section('css')
    
@stop