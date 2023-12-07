@inject('ISAC','App\Http\Controllers\InvItemStockAdjustmentController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
@section('js')
<script>
      function function_elements_add(name, value){
		$.ajax({    
            type: "POST",
            url : "{{route('add-elements-stock-adjustment')}}",
            data : {
                'name'      : name, 
                'value'     : value,
                '_token'    : '{{csrf_token()}}'
            },
            success: function(msg){
			}
		});
	}

    function changeAmount(item_stock_id, value){
        var item_total 	= $("#item_total_"+item_stock_id).val();
        var adjustment_final_amount = parseFloat(item_total) - parseFloat(value);
        $('#adjustment_difference_amount_'+item_stock_id).val(adjustment_final_amount);
    }

    $(document).ready(function(){
        var elements = {!! json_encode($stockadjustmentelement) !!};

        if(!elements || elements==''){
            elements = [];
        }

        if(!elements['warehouse_id']){
            $("#warehouse_id").select2("val", "0");
        }

        if(!elements['item_category_id']){
            $("#item_category_id").select2("val", "0");
        }

        if(!elements['item_type_id']){
            $("#item_type_id").select2("val", "0");
        }



        $("#item_category_id").change(function(){
			var item_category_id 	= $("#item_category_id").val();
            $.ajax({
                type: "POST",
                url : "{{route('purchase-order-type')}}",
                dataType: "html",
                data: {
                    'item_category_id'			    : item_category_id,
                    '_token'                        : '{{csrf_token()}}',
                },
                success: function(return_data){ 
                    $('#item_type_id').html(return_data);
                },
                error: function(data)
                {
                }
            });
		});
    });
</script>
@stop
@section('content_header')

<?php 
if(!$stockadjustmentelement['stock_adjustment_date']){
    $stockadjustmentelement['stock_adjustment_date'] = date('d-m-Y');
}
if(!$stockadjustmentelement['warehouse_id'] || $stockadjustmentelement['warehouse_id'] == ''){
    $stockadjustmentelement['warehouse_id'] = null;
}
?>


<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('item-stock-adjustment') }}">Daftar Penyesuaian Stok</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Penyesuaian Stok</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    Form Tambah Penyesuaian Stok
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
            Filter
        </h5>
        <div class="float-right">
            <button onclick="location.href='{{ url('item-stock-adjustment') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{ route('get-list-item-stock-adjustment') }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-3">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Penyesuaian Stok<a class='red'> *</a></a>
                        <input class="form-control input-bb" name="stock_adjustment_date" id="stock_adjustment_date" type="date" data-date-format="dd-mm-yyyy" autocomplete="off" value="{{ $stockadjustmentelement['stock_adjustment_date'] }}" onchange="function_elements_add(this.name, this.value)"/>
                    </div>
                </div>
                <div class="col-md-3">
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Gudang<a class='red'> *</a></a>
                        {!! Form::select('warehouse_id',  $warehouse, $stockadjustmentelement['warehouse_id'], ['class' => 'form-control selection-search-clear select-form', 'id' => 'warehouse_id', 'name' => 'warehouse_id', 'onchange' => 'function_elements_add(this.name, this.value)']) !!}
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Kategori Barang<a class='red'> *</a></a>
                        {!! Form::select('item_category_id',  $itemcategory, $stockadjustmentelement['item_category_id'], ['class' => 'form-control selection-search-clear select-form', 'id' => 'item_category_id', 'name' => 'item_category_id', 'onchange' => 'function_elements_add(this.name, this.value)']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Barang<a class='red'> *</a></a>
                        {!! Form::select('item_type_id',  $itemtype, $stockadjustmentelement['item_type_id'], ['class' => 'form-control selection-search-clear select-form', 'id' => 'item_type_id', 'name' => 'item_type_id', 'onchange' => 'function_elements_add(this.name, this.value)']) !!}
                    </div>
                </div>
            </div>
        </div> 
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <button type="submit" name="Find" class="btn btn-primary" title="Search Data"><i class="fa fa-search"></i> Cari</button>
            </div>
        </div>       
    </form>    
</div>

<div class="card border border-dark">
    <div class="card-header border-dark bg-dark">
        <h5 class="mb-0 float-left">
            Daftar
        </h5>
    </div>
    <form method="POST" action="{{ route('process-add-stock-adjustment') }}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="form-body form">
                <div class="table-responsive">
                    <table class="table table-bordered table-advance table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style='text-align:center'>Batch Number</th>
                                <th style='text-align:center'>Nama Barang</th>
                                <th style='text-align:center'>Satuan Barang</th>
                                <th style='text-align:center'>Gudang</th>
                                <th style='text-align:center'>Stock Sistem</th>
                                <th style='text-align:center'>Penyesuaian Sistem</th>
                                <th style='text-align:center'>Selisih Stock</th>
                                <th style='text-align:center'>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                          <?php $no = 1; ?>
                            @foreach ($itemstock as $val)
                              <?php $no++ ?>
                                <tr>
                                    <td style="text-align: center">
                                            <input style="text-align: left" class="form-control input-bb" type="text" name="item_batch_number_<?php echo $val['item_stock_id']?>" id="item_batch_number_<?php echo $val['item_stock_id'] ?>" value="<?php echo $val['item_batch_number'] ?>" autocomplete="off">
                                    </td>
                                    <td>
                                        {{ $ISAC->getItemName($val['item_category_id'], $val['item_type_id'], $val['item_id']) }}
                                    </td>
                                    <td>
                                        {{ $ISAC->getItemUnitName($val['item_unit_id']) }}
                                        <input style="text-align: right" class="form-control input-bb" type="text" name="item_unit_id_<?php echo $val['item_stock_id'] ?>" id="item_unit_id_<?php echo $val['item_stock_id'] ?>" value="<?php echo $val['item_unit_id'] ?>" hidden>
                                    </td>
                                    <td>
                                        {{ $ISAC->getWarehouseName($val['warehouse_id']) }}
                                    </td>
                                    <td style="text-align: right">
                                        {{ number_format($val['quantity_unit']) }}
                                        <input style="text-align: right" class="form-control input-bb" type="text" name="item_total_<?php echo $val['item_stock_id'] ?>" id="item_total_<?php echo $val['item_stock_id'] ?>" value="<?php echo $val['quantity_unit'] ?>" hidden>
                                    </td>
                                    <td style="text-align: center">
                                        <input style="text-align: right" class="form-control input-bb" type="text" name="adjustment_amount_<?php echo $val['item_stock_id'] ?>" id="adjustment_amount_<?php echo $val['item_stock_id'] ?>" onchange="changeAmount(<?php echo $val['item_stock_id'] ?>, this.value)" autocomplete="off">
                                    </td>
                                    <td style="text-align: center">
                                        <input style="text-align: right" class="form-control input-bb" type="text" name="adjustment_difference_amount_<?php echo $val['item_stock_id'] ?>" id="adjustment_difference_amount_<?php echo $val['item_stock_id'] ?>" readonly>
                                    </td>
                                    <td style="text-align: center">
                                        <input class="form-control input-bb" type="text" name="stock_adjustment_item_remark_<?php echo $val['item_stock_id'] ?>" id="stock_adjustment_item_remark_<?php echo $val['item_stock_id'] ?>" autocomplete="off">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <button type="submit" name="Save" class="btn btn-success" title="Save"><i class="fa fa-check"></i> Simpan</button>
            </div>
        </div>
    </form>
</div>
</div>



@stop

@section('footer')
    
@stop

@section('css')
    
@stop