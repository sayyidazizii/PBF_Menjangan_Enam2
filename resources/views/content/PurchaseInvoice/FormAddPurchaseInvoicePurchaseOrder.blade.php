@inject('PurchaseInvoice', 'App\Http\Controllers\PurchaseInvoiceController')
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
        <li class="breadcrumb-item"><a href="{{ url('purchase-invoice') }}">Daftar Invoice Pembelian</a></li>
        <li class="breadcrumb-item"><a href="{{ url('purchase-invoice/search-purchase-order') }}">Daftar Purchase Order</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Invoice Pembelian</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    Form Tambah Invoice Pembelian
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
            <button onclick="location.href='{{ url('purchase-invoice/search-purchase-order') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-add-purchase-invoice')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Pemasok</a>
                        <input class="form-control input-bb" type="text" name="supplier_id" id="supplier_id" value="{{$PurchaseInvoice->getSupplierName($purchaseorder['supplier_id'])}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="supplier_id" id="supplier_id" value="{{$purchaseorder['supplier_id']}}"/>
                        <input class="form-control input-bb" type="hidden" name="warehouse_id" id="warehouse_id" value="{{$purchaseorder['warehouse_id']}}"/>
                        <input class="form-control input-bb" type="hidden" name="purchase_order_id" id="purchase_order_id" value="{{$purchase_order_id}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No Purchase Order</a>
                        <input class="form-control input-bb" type="text" name="purchase_order_no" id="purchase_order_no" value="{{$purchaseorder['purchase_order_no']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Purchase Order</a>
                        <input class="form-control input-bb" type="text" name="purchase_order_date" id="purchase_order_date" value="{{$purchaseorder['purchase_order_date']}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No Penerimaan Barang</a>
                        <input class="form-control input-bb" type="text" name="goods_received_note_no" id="goods_received_note_no" value="{{$invgoodsreceivednote['goods_received_note_no']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Penerimaan Barang</a>
                        <input class="form-control input-bb" type="text" name="goods_received_note_date" id="goods_received_note_date" value="{{$invgoodsreceivednote['goods_received_note_date']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Invoice Pembelian<a class='red'> *</a></a>
                        <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="purchase_invoice_date" id="purchase_invoice_date" onChange="function_elements_add(this.name, this.value);" value=""/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <a class="text-dark">Keterangan</a>
                    <div class="">
                        <textarea rows="3" type="text" class="form-control input-bb" name="purchase_invoice_remark" onChange="elements_add(this.name, this.value);" id="purchase_invoice_remark" ></textarea>
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
                                <th style='text-align:center'>No.</th>
                                <th style='text-align:center'>Kategori Barang</th>
                                <th style='text-align:center'>Nama Barang</th>
                                <th style='text-align:center'>Quantity</th>
                                <th style='text-align:center'>Satuan</th>
                                <th style='text-align:center'>Harga Satuan</th>
                                <th style='text-align:center'>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if(count($purchaseorderitem)==0){
                                    echo "<tr><th colspan='7' style='text-align  : center !important;'>Data Kosong</th></tr>";
                                } else {
                                    $no             = 1;
                                    $total_price    = 0;
                                    $total_item     = 0;
                                    foreach ($purchaseorderitem AS $key => $val){
                                        echo"
                                            <tr>
                                                <td style='text-align  : center'>".$no."</td>
                                                <td style='text-align  : left !important;'>".$PurchaseInvoice->getItemCategoryName($val['item_category_id'])."</td>
                                                <td style='text-align  : left !important;'>".$PurchaseInvoice->getItemTypeName($val['item_type_id'])."</td>
                                                <td style='text-align  : right !important;'>".$val['quantity']."</td>
                                                <td style='text-align  : left !important;'>".$PurchaseInvoice->getItemUnitName($val['item_unit_id'])."</td>
                                                <td style='text-align  : right !important;'>".number_format($val['item_unit_cost'],2,',','.')."</td>
                                                <td style='text-align  : right !important;'>".number_format($val['subtotal_amount'],2,',','.')."</td>";
                                                echo"
                                            </tr>
                                        ";
                                        $no++;
                                        $total_price += $val['subtotal_amount'];
                                        $total_item  += $val['quantity'];
                                    }
                                        echo"
                                        <th style='text-align  : left' colspan='6'>Total</th>
                                        <th style='text-align  : right'>".number_format($total_price,2,',','.')."
                                            <input class='form-control input-bb' type='hidden' name='total_amount' id='total_amount' value='".$total_price."'/>  
                                            <input class='form-control input-bb' type='hidden' name='total_item' id='total_item' value='".$total_item."'/>    
                                        </th>
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
                <button type="reset" name="Reset" class="btn btn-danger btn-sm" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
                <button type="submit" name="Save" class="btn btn-primary btn-sm" title="Save"><i class="fa fa-check"></i> Simpan</button>
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