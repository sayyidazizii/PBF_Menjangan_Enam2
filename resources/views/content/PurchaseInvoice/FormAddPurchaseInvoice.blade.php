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
    <b>Form Tambah Invoice Pembelian</b>
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
            <button onclick="location.href='{{ url('purchase-invoice/search-goods-received-note') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-add-purchase-invoice')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No Invoice <a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="purchase_invoice_no" id="purchase_invoice_no"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Pemasok</a>
                        <input class="form-control input-bb" type="text" name="supplier_id" id="supplier_id" value="{{$PurchaseInvoice->getSupplierName($goodsreceivednote['supplier_id'])}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="supplier_id" id="supplier_id" value="{{$goodsreceivednote['supplier_id']}}"/>
                        <input class="form-control input-bb" type="hidden" name="warehouse_id" id="warehouse_id" value="{{$goodsreceivednote['warehouse_id']}}"/>
                        <input class="form-control input-bb" type="hidden" name="goods_received_note_id" id="goods_received_note_id" value="{{$goods_received_note_id}}"/>
                        <input class="form-control input-bb" type="hidden" name="purchase_order_id" id="purchase_order_id" value="{{$purchaseorder['purchase_order_id']}}"/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No Penerimaan Barang</a>
                        <input class="form-control input-bb" type="text" name="goods_received_note_no" id="goods_received_note_no" value="{{$goodsreceivednote['goods_received_note_no']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Penerimaan Barang</a>
                        <input class="form-control input-bb" type="text" name="goods_received_note_date" id="goods_received_note_date" value="{{date('d/m/Y', strtotime($goodsreceivednote['goods_received_note_date']))}}" readonly/>
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
                        <input class="form-control input-bb" type="text" name="purchase_order_date" id="purchase_order_date" value="{{date('d/m/Y', strtotime($purchaseorder['purchase_order_date']))}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Invoice Pembelian<a class='red'> *</a></a>
                        <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="purchase_invoice_date" id="purchase_invoice_date" onChange="function_elements_add(this.name, this.value);" value=""/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Jatuh Tempo<a class='red'> *</a></a>
                        <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="purchase_invoice_due_date" id="purchase_invoice_due_date" onChange="function_elements_add(this.name, this.value);" value=""/>
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
                                <th style='text-align:center'>Quantity</th>
                                <th style='text-align:center'>Harga Satuan</th>
                                <th style='text-align:center'>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($goodsreceivednoteitem)==0)
                                <tr><th colspan='7' style='text-align  : center !important;'>Data Kosong</th></tr>
                                @else
                                    @php
                                        $no = 1;
                                        $total_price = 0;
                                        $total_item = 0;
                                    @endphp
                                    @foreach ($goodsreceivednoteitem AS $key => $val)
                                        @php
                                            $purchaseorderitem = $PurchaseInvoice->getPurchaseOrderItem($val['goods_received_note_item_id']);  
                                        @endphp
                                        <tr>
                                            <td style='text-align  : center'>{{$no}}</td>
                                            <td style='text-align  : left !important;'>{{$PurchaseInvoice->getItemCategoryName($val['item_category_id'])}}</td>
                                            <td style='text-align  : left !important;'>{{$PurchaseInvoice->getItemTypeName($val['item_type_id'])}}</td>
                                            <td style='text-align  : left !important;'>{{$PurchaseInvoice->getItemUnitName($val['item_unit_id'])}}</td>
                                            <td style='text-align  : right !important;'>{{$val['quantity']}}</td>
                                            <td style='text-align  : right !important;'>{{number_format($purchaseorderitem['item_unit_cost'],2,',','.')}}</td>
                                            <td style='text-align  : right !important;'>{{number_format($purchaseorderitem['item_unit_cost']*$val['quantity'],2,',','.')}}</td>
                                        </tr>
                                        @php
                                            $no++;
                                            $total_price += $purchaseorderitem['item_unit_cost']*$val['quantity'];
                                            $total_item  += $val['quantity'];
                                            $totalafterPpn = $total_price + $purchaseorder->ppn_in_amount;
                                        @endphp
                                    @endforeach
                                    <th style='text-align  : center' colspan='2'>Total</th>
                                    <th style='text-align  : center' colspan='2'></th>
                                    <th style='text-align  : right'>{{$total_item}}</th>
                                    <th style='text-align  : right'></th>
                                    <th style='text-align  : right'>{{number_format($total_price,2,',','.')}}
                                        <input class='form-control input-bb' type='hidden' name='total_amount' id='total_amount' value='{{$total_price}}'/>  
                                        <input class='form-control input-bb' type='hidden' name='total_item' id='total_item' value='{{$total_item}}'/>    
                                    </th>
                                    <tr>
                                        <td style='text-align  : center' colspan='2'><b>PPN Masuk (%)</b></td>
                                        <td style='text-align  : center'><b>:</b></td>
                                        <td colspan='2'></td>
                                        <td>
                                            <input style='text-align  : right' type="text" class="form-control" name="ppn_in_percentage" id="ppn_in_percentage" value="{{$purchaseorder->ppn_in_percentage}}" readonly>
                                        </td>
                                        <td>
                                            <input type="text" style='text-align  : right' class="form-control" name="ppn_in_amount_view" id="ppn_in_amount_view" value="{{number_format($purchaseorder->ppn_in_amount,2,',','.')}}" readonly>
                                            <input type="text" style='text-align  : right' class="form-control" name="ppn_in_amount" id="ppn_in_amount" value="{{$purchaseorder->ppn_in_amount}}" hidden>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style='text-align  : center' colspan='2'><b>Total Harga Akhir </td>
                                        <td style='text-align  : center'><b>:</b></td>
                                        <td colspan='3'></td>
                                        <td style='text-align  : center'>
                                            <input type="text" style='text-align  : right' class="form-control" name="subtotal_after_ppn_in" id="subtotal_after_ppn_in" value="{{number_format($totalafterPpn,2,',','.')}}" readonly>
                                        </td>
                                    </tr>
                                @endif
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