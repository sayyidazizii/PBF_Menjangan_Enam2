@inject('SalesOrderReturn', 'App\Http\Controllers\SalesOrderReturnController')
@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>


    $(document).ready(function(){
            var barang_kembali 	                = $("#barang_kembali").val();
            var warehouse_id 	                = $("#warehouse_id").val();
            $('#warehouse_id_view').attr('disabled','true'); 
            
			if(barang_kembali == 1){
                warehouse_id = 7
               $('#warehouse_id_view').select2('val','7');
               
            }else{
                warehouse_id = 9
                $('#warehouse_id_view').select2('val','9');
            }
            $("#barang_kembali").val(barang_kembali);
            $("#warehouse_id").val(warehouse_id);
    
            console.log(barang_kembali , warehouse_id);


        $("#barang_kembali").change(function(){
            var barang_kembali 	                = $("#barang_kembali").val();
            var warehouse_id 	                = $("#warehouse_id").val();
            $('#warehouse_id_view').attr('disabled','true'); 

            if(barang_kembali == 1){
                warehouse_id = 7
                $('#warehouse_id_view').select2('val','7');
            }else{
                warehouse_id = 9
               $('#warehouse_id_view').select2('val','9');
            }
            $("#barang_kembali").val(barang_kembali);
            $("#warehouse_id").val(warehouse_id);
    
            console.log(barang_kembali , warehouse_id);


		});
    });
</script>
@stop

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('sales-order-return') }}">Daftar Return Penjualan</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Return Penjualan</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Form Edit Return Penjualan</b>
</h3>
<br/>
@if(session('msg'))
<div class="alert alert-info" role="alert">
    {{session('msg')}}
</div>
@endif
    <div class="card border border-dark">
    <div class="card-header border-dark bg-dark">
        <h5 class="mb-0 float-left">
            Form Detail
        </h5>
        <div class="float-right">
            <button onclick="location.href='{{ url('sales-order-return') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-edit-sales-order-return')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row">
                <h5 class="form-section"><b>Detail Sales Delivery Note</b></h5>
            </div>
            <hr style="margin:0;">
            <br/>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Delivery Note No</a>
                        <input class="form-control input-bb" type="text" name="sales_delivery_order_no" id="sales_delivery_order_no" onChange="function_elements_add(this.name, this.value);" value="{{$SalesOrderReturn->getSalesDeliveryNoteNo($salesorderreturn['sales_delivery_note_id'])}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="sales_delivery_note_no" id="sales_delivery_note_no" onChange="function_elements_add(this.name, this.value);" value="{{$sales_order_return_id}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="sales_order_return_id" id="sales_order_return_id" onChange="function_elements_add(this.name, this.value);" value="{{$sales_order_return_id}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Pelanggan</a>
                        <input class="form-control input-bb" type="text" name="customer_name" id="customer_name" onChange="function_elements_add(this.name, this.value);" value="{{$SalesOrderReturn->getCustomerName($salesorderreturn['sales_order_id'])}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="customer_name" id="customer_name" onChange="function_elements_add(this.name, this.value);" value="{{$SalesOrderReturn->getCustomerName($salesorderreturn['sales_order_id'])}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Delivery Note</a>
                        <input class="form-control input-bb" type="text" name="sales_delivery_order_date" id="sales_delivery_order_date" onChange="function_elements_add(this.name, this.value);" value="{{$SalesOrderReturn->getSalesDeliveryNoteDate($salesorderreturn['sales_delivery_note_id'])}}" readonly/>
                    </div>
                </div>
            </div>
            <br>
            <div class="row">
                <h5 class="form-section"><b>Detail Form Return Penjualan</b></h5>
            </div>
            <hr style="margin:0;">
            <br/>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Gudang</a>
                        <input class="form-control input-bb" type="text" name="warehouse_id_old" id="warehouse_id_old"  value="{{$SalesOrderReturn->getWarehouseName($salesorderreturn['warehouse_id'])}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Return</a>
                        <input type ="text" class="form-control input-bb" name="sales_order_return_date" id="sales_order_return_date" value="{{date('d/m/Y', strtotime($salesorderreturn['sales_order_return_date']))}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nomor Retur Barang</a>
                         <a class="text-dark">Satuan<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="no_retur_barang" id="no_retur_barang" value="{{ $salesorderreturn['no_retur_barang'] }}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nota Retur Pajak</a>
                        <a class="text-dark">Satuan<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="nota_retur_pajak" id="nota_retur_pajak" value="{{ $salesorderreturn['nota_retur_pajak'] }}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Barang Sudah Kembali<a class='red'> *</a></a>
                        <br/>
                         <select class="selection-search-clear" name="barang_kembali" id="barang_kembali" style="width: 100% !important">
                            <option value="0">Belum</option>
                            <option value="1">Sudah</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Gudang Saat ini</a>
                        <br/>
                        {!! Form::select('warehouse_id_view',  $warehouse, 0, ['class' => 'selection-search-clear select-form', 'id' => 'warehouse_id_view']) !!}
                        <input class="form-control input-bb" hidden type="text" name="warehouse_id" id="warehouse_id"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Alasan Return</a>
                        <div class="">
                            <textarea rows="3" type="text" class="form-control input-bb" name="sales_delivery_note_remark" id="sales_order_return_remark" onChange="function_elements_add(this.name, this.value);" readonly>{{$salesorderreturn['sales_order_return_remark']}}</textarea>
                        </div>
                    </div>
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
                                <th width="2%" style='text-align:center'>No.</th>
                                <th width="5%" style='text-align:center'>No Sales Order</th>
                                <th width="2%" style='text-align:center'>Tanggal SO</th>
                                <th width="10%" style='text-align:center'>Barang</th>
                                <th width="2%" style='text-align:center'>Qty Kirim</th>
                                <th width="2%" style='text-align:center'>Qty Return</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if(count($salesorderreturnitem)==0){
                                    echo "<tr><th colspan='9' style='text-align  : center !important;'>Data Kosong</th></tr>";
                                } else {
                                    $no =1;
                                    foreach ($salesorderreturnitem AS $key => $val){
                                        // $item = $SalesOrderReturn->getSalesOrderItem($val['sales_order_item_id']);
                                        echo"
                                            <tr>
                                                <td style='text-align  : center'>".$no.".</td>
                                                <td style='text-align  : left !important;'>".$val['sales_order_no']."</td>
                                                <td style='text-align  : left !important;'>".date('d/m/Y', strtotime($val['sales_order_date']))."</td>
                                                <td style='text-align  : left !important;'>".$SalesOrderReturn->getItemStockName($val['item_stock_id'])."</td>
                                                <td style='text-align  : right !important;'>".$val['quantity']."</td>
                                                <td style='text-align  : right !important;'>
                                                    <input class='form-control' style='text-align:right;'type='text' name='quantity_delivered_".$no."' id='quantity_delivered_".$no."' value='".$val['quantity_return']."' readonly/>  



                                                    <input class='form-control' style='text-align:right;'type='' name='harga_beli_".$no."' id='harga_beli_".$no."' value='".$SalesOrderReturn->getIntemUnitCost($val['item_type_id'])."'/>
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='sales_order_id_".$no."' id='sales_order_id_".$no."' value='".$val['sales_order_id']."'/>  
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='sales_order_item_id_".$no."' id='sales_order_item_id_".$no."' value='".$val['sales_order_item_id']."'/>  
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='customer_id_".$no."' id='customer_id_".$no."' value='".$val['customer_id']."'/>  
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='item_id_".$no."' id='item_id_".$no."' value='".$val['item_id']."'/>
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='item_type_id_".$no."' id='item_type_id_".$no."' value='".$val['item_type_id']."'/>
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='quantity_".$no."' id='quantity_".$no."' value='".$val['quantity']."'/>
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='item_stock_id_".$no."' id='item_stock_id_".$no."' value='".$val['item_stock_id']."'/>
                                                    </td>";
                                                echo"
                                            </tr>
                                        ";
                                        $total_no = $no;
                                        $no++;
                                    }
                                    echo " <input class='form-control' style='text-align:right;'type='hidden' name='total_no' id='total_no' value='$total_no'/>";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <a name='Reset'  class='btn btn-danger btn-sm' onClick='javascript:return confirm(\"apakah yakin ingin dihapus ?\")'><i class="fa fa-times"></i> Reset</a>
                <button type="submit" name="Save"  class="btn btn-primary btn-sm" title="Save"><i class="fa fa-check"></i> Simpan</button>
            </div>
        </div>
    </div>
    </form>
<br/>
<br>
<br>

@include('footer')
    
@stop

@section('css')
    
@stop

@section('js')
    
@stop