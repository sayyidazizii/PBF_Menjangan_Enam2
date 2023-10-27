@inject('PurchaseOrder', 'App\Http\Controllers\PurchaseOrderController')
@inject('PurchaseOrderReturn', 'App\Http\Controllers\PurchaseOrderReturnController')
@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />
@section('js')
<script>
	function toRp(number) {
		var number = number.toString(), 
		rupiah = number.split('.')[0], 
		cents = (number.split('.')[1] || '') +'00';
		rupiah = rupiah.split('').reverse().join('')
			.replace(/(\d{3}(?!$))/g, '$1.')
			.split('').reverse().join('');
		return rupiah + ',' + cents.slice(0, 2);
	}
</script>
@stop
@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('purchase-order-return') }}">Daftar Return Pembelian</a></li>
        <li class="breadcrumb-item active" aria-current="page">Hapus Return Pembelian</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    Form Hapus Return Pembelian
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
            Form Hapus
        </h5>
        <div class="float-right">
            <button onclick="location.href='{{ url('purchase-order-return') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="get" action="{{route('process-delete-purchase-order-return', ['purchase_order_return_id' => $purchase_order_return_id])}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No Penerimaan</a>
                        <input class="form-control input-bb" type="text" name="purchase_order_return_no" id="purchase_order_return_no" value="{{$purchaseorderreturn['purchase_order_return_no']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Penerimaan</a>
                        <input class="form-control input-bb" type="text" name="purchase_order_return_date" id="purchase_order_return_date" value="{{date('d/m/Y', strtotime($purchaseorderreturn['purchase_order_return_date']))}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No PO</a>
                        <input class="form-control input-bb" type="text" name="purchase_order_date" id="purchase_order_date" value="{{$PurchaseOrderReturn->getPurchaseOrderNo($purchaseorderreturn['purchase_order_id'])}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal PO</a>
                        <input class="form-control input-bb" type="text" name="purchase_order_date" id="purchase_order_date" value="{{date('d/m/Y', strtotime($PurchaseOrderReturn->getPurchaseOrderDate($purchaseorderreturn['purchase_order_id'])))}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Gudang</a>
                        <input class="form-control input-bb" type="text" name="warehouse_id" id="warehouse_id" value="{{$PurchaseOrder->getInvWarehouseName($purchaseorderreturn['warehouse_id'])}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Pemasok</a>
                        <input class="form-control input-bb" type="text" name="supplier_id" id="supplier_id" value="{{$PurchaseOrder->getCoreSupplierName($purchaseorderreturn['supplier_id'])}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Surat Jalan Supplier</a>
                        <input class="form-control input-bb" type="text" name="delivery_note_no" id="delivery_note_no" value="{{$purchaseorderreturn['delivery_note_no']}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12 ">
                    <a class="text-dark">Keterangan</a>
                    <div class="">
                        <textarea rows="3" type="text" class="form-control input-bb" name="purchase_order_remark" onChange="function_elements_add(this.name, this.value);" id="purchase_order_remark" readonly>{{$purchaseorderreturn['purchase_order_return_remark']}}</textarea>
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
                                <th style='text-align:center'>No.</th>
                                <th style='text-align:center'>Kategori Barang</th>
                                <th style='text-align:center'>Nama Barang</th>
                                <th style='text-align:center'>Satuan</th>
                                <th style='text-align:center'>Harga Satuan</th>
                                <th style='text-align:center'>Quantity</th>
                                <th style='text-align:center'>Total Harga</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if(count($purchaseorderreturnitem)<1){
                                    echo "<tr><th colspan='7' style='text-align  : center !important;'>Data Kosong</th></tr>";
                                } else {
                                    $no =1;
                                    $total_price = 0;
                                    $total_item = 0;
                                    foreach ($purchaseorderreturnitem AS $key => $val){
                                        echo"
                                            <tr>
                                                <td style='text-align  : center'>".$no.".</td>
                                                <td style='text-align  : left !important;'>".$PurchaseOrder->getItemCategoryName($val['item_category_id'])."</td>
                                                <td style='text-align  : left !important;'>".$PurchaseOrder->getItemTypeName($val['item_type_id'])."</td>
                                                <td style='text-align  : left !important;'>".$PurchaseOrder->getItemUnitName($val['item_unit_id'])."</td>
                                                <td style='text-align  : right !important;'>".number_format($val['item_unit_cost'],2,',','.')."</td>
                                                <td style='text-align  : right !important;'>".$val['quantity']."</td>
                                                <td style='text-align  : right !important;'>".number_format($val['subtotal_amount'],2,',','.')."</td>";
                                                echo"
                                            </tr>
                                        ";
                                        $no++;
                                        $total_price+=$val['subtotal_amount'];
                                        $total_item+=$val['quantity'];
                                    }
                                        echo"
                                        <th style='text-align  : center' colspan='5'>Total</th>
                                        <th style='text-align  : right'>".$total_item."</th>
                                        <th style='text-align  : right'>".number_format($total_price,2,',','.')."</th>
                                        ";
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <button type="submit" name="Save" class="btn btn-danger btn-sm" title="Save"><i class="fa fa-trash"></i> Hapus</button>
            </div>
        </div>

    </div>
</form>
<br/>
<br/>
<br/>
<br/>

@include('footer')

@stop

@section('css')
    
@stop