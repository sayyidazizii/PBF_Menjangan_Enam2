@inject('InvItemStock', 'App\Http\Controllers\InvItemStockController')
@inject('Grading', 'App\Http\Controllers\GradingController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />
@section('js')
<script>
	$(document).ready(function(){
        var item_category_id    = {!! json_encode($item_category_id) !!};
        var item_type_id        = {!! json_encode($item_type_id) !!};
        var grade_id            = {!! json_encode($grade_id) !!};
        var warehouse_id        = {!! json_encode($warehouse_id) !!};
        
        if(item_category_id == null){
            $("#item_category_id").select2("val", "0");
        }
        if(item_type_id == null){
            $("#item_type_id").select2("val", "0");
        }
        if(grade_id == null){
            $("#grade_id").select2("val", "0");
        }
        if(warehouse_id == null){
            $("#warehouse_id").select2("val", "0");
        }

        
        $("#item_category_id").change(function(){
			var item_category_id 	= $("#item_category_id").val();
                $.ajax({
                    type: "POST",
                    url : "{{route('item-stock-type')}}",
                    dataType: "html",
                    data: {
                        'item_category_id'	  : item_category_id,
                        '_token'              : '{{csrf_token()}}',
                    },
                    success: function(return_data){ 
                        console.log(return_data);
					$('#item_type_id').html(return_data);
                    },
                    error: function(data)
                    {
                        console.log(data);

                    }
                });

		});
        
        $("#item_type_id").change(function(){
            var item_type_id 	= $("#item_type_id").val();
                $.ajax({
                    type: "POST",
                    url : "{{route('item-stock-grade')}}",
                    dataType: "html",
                    data: {
                        'item_type_id'	  : item_type_id,
                        '_token'              : '{{csrf_token()}}',
                    },
                    success: function(return_data){ 
                        $('#grade_id').html(return_data);
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
        <li class="breadcrumb-item active" aria-current="page">Daftar Stock Barang</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Stock Barang</b> <small>Mengelola Stock Barang </small>
</h3>
<br/>
<div id="accordion">
    <form  method="post" action="{{route('filter-item-stock')}}" enctype="multipart/form-data">
    @csrf
        <div class="card border border-dark">
        <div class="card-header bg-dark" id="headingOne" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            <h5 class="mb-0">
                Filter
            </h5>
        </div>
    
        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
            <div class="card-body">
                <div class = "row form-group">
                    <div class = "col-md-4">
                        <a class="text-dark">Kategori</a>
                        <br/>
                        {!! Form::select('item_category_id',  $invitemcategory, $item_category_id, ['class' => 'selection-search-clear select-form', 'id' => 'item_category_id']) !!}
                    </div>
                    <div class = "col-md-4">
                        <a class="text-dark">Nama Barang</a>
                        <br/>
                        {!! Form::select('item_type_id',  $invitemtype, $item_type_id, ['class' => 'selection-search-clear select-form', 'id' => 'item_type_id']) !!}
                    </div>
                    <div class = "col-md-4">
                        <a class="text-dark">Gudang</a>
                        <br/>
                        {!! Form::select('warehouse_id',  $invwarehouse, $warehouse_id, ['class' => 'selection-search-clear select-form', 'id' => 'warehouse_id']) !!}
                    </div>
                </div>
            </div>
            <div class="card-footer text-muted">
                <div class="form-actions float-right">
                    <button type="reset" name="Reset" class="btn btn-danger btn-sm" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
                    <button type="submit" name="Find" class="btn btn-primary btn-sm" title="Search Data"><i class="fa fa-search"></i> Cari</button>
                </div>
            </div>
        </div>
        </div>
    </form>
</div>
<br/>
@if(session('msg'))
<div class="alert alert-info" role="alert">
    {{session('msg')}}
</div>
@endif 
<div class="card border border-dark">
    <div class="card-header bg-dark clearfix">
        <h5 class="mb-0 float-left">
            Daftar
        </h5>
        <div class="form-actions float-right">
            <button onclick="location.href='{{ url('item-stock/export') }}'" name="Find" class="btn btn-sm btn-info" title="Export Excel"><i class="fa fa-print"></i> Export</button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No</th>
                        <th width="7%" style='text-align:center'>Batch Number</th>
                        <th width="13%" style='text-align:center'>Kategori Barang</th>
                        <th width="15%" style='text-align:center'>Nama Barang</th>
                        {{-- <th width="10%" style='text-align:center'>Grade</th> --}}
                        <th width="10%" style='text-align:center'>Qty</th>
                        <th width="10%" style='text-align:center'>Unit</th>
                        <th width="10%" style='text-align:center'>Gudang</th>
                        <th width="10%" style='text-align:center'>PO Customer</th>
                        <th width="10%" style='text-align:center'>No.Retur Barang</th>
                        <th width="10%" style='text-align:center'>Nota Retur Pajak</th>
                        <th width="10%" style='text-align:center'>Tanggal Datang</th>
                        <th width="5%" style='text-align:center'>Harga</th>
                        <th width="10%" style='text-align:center'>Tanggal Kadaluarsa</th>
                        {{-- <th width="8%" style='text-align:center'>Aksi</th> --}}
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($invitemstock as $stock)
                    <tr>
                        <td style='text-align:center'>{{$no}}</td>
                        <td>{{$stock['item_batch_number']}}</td>
                        <td>{{$Grading->getInvItemCategoryName($stock['item_category_id'])}}</td>
                        <td>{{$Grading->getInvItemTypeName($stock['item_type_id'])}}</td>
                        {{-- <?php if($stock['item_id']==0) {?>
                            <td>No Grade</td>
                        <?php } else {?>
                            <td>{{$InvItemStock->getCoreGradeName($stock['item_id'])}}</td>
                        <?php } ?> --}}
                        <td style='text-align:right'>{{$stock['quantity_unit']}}</td>
                        <td>{{$Grading->getInvItemUnitName($stock['item_unit_id'])}}</td>
                        <td>{{$Grading->getInvWarehouseName($stock['warehouse_id'])}}</td>



                        <?php if($stock['purchase_order_no']=='0') {?>
                        <td style='text-align:right'>-</td>
                        <?php } else {?>
                        <td style='text-align:right'>{{$stock['purchase_order_no']}}</td>
                        <?php } ?> 


                        <?php if($stock['no_retur_barang']=='0') {?>
                            <td style='text-align:right'>-</td>
                        <?php } else {?>
                            <td style='text-align:right'>{{$stock['no_retur_barang']}}</td>
                        <?php } ?> 

                        <?php if($stock['nota_retur_pajak']=='0') {?>
                            <td style='text-align:right'>-</td>
                        <?php } else {?>
                            <td style='text-align:right'>{{$stock['nota_retur_pajak']}}</td>
                        <?php } ?> 

                        <td>{{date('d/m/Y', strtotime($stock['item_stock_date']))}}</td>
                        <td>{{$stock['item_unit_cost']}}</td>
                        <?php if($stock['item_stock_expired_date']=='0000-00-00') {?>
                            <td>-</td>
                        <?php } else {?>
                            <td>{{date('d/m/Y', strtotime($stock['item_stock_expired_date']))}}</td>
                        <?php } ?> 
                        {{-- <td class="">
                            <a type="button" class="btn btn-outline-warning btn-sm" href="{{ url('/inv-item-stock/edit/'.$stock['item_stock_id']) }}">Edit</a>
                            <a type="button" class="btn btn-outline-danger btn-sm" href="{{ url('/inv-item-stock/delete-inv-item-stock/'.$stock['item_stock_id']) }}">Hapus</a>
                        </td> --}}
                    </tr>
                    <?php $no++; ?>
                    @endforeach
                </tbody>
            </table>
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

@section('js')
    
@stop