@inject('PurchaseInvoice', 'App\Http\Controllers\PurchaseInvoiceController')
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
        <li class="breadcrumb-item"><a href="{{ url('purchase-order-return') }}">Daftar Retur Pembelian</a></li>
        <li class="breadcrumb-item active" aria-current="page">Retur Pembelian</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Detail Retur Pembelian</b>
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
            <button onclick="location.href='{{ url('purchase-order-return') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-add-purchase-order-return')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <div class="form-group">
                            <a class="text-dark">No. Invoice</a>
                            <input class="form-control input-bb" type="text" name="purchase_invoice_no" id="purchase_invoice_no" onChange="function_elements_add(this.name, this.value);" value="{{$purchaseinvoice['purchase_invoice_no']}}" readonly/>
                            <input class="form-control input-bb" type="hidden" name="supplier_id" id="supplier_id" value="{{$purchaseinvoice['supplier_id']}}"/>
                            <input class="form-control input-bb" type="hidden" name="warehouse_id" id="warehouse_id" value="7"/>
                            <input class="form-control input-bb" type="hidden" name="purchase_order_id" id="purchase_order_id" value="{{$purchaseorder['purchase_order_id']}}"/>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                    <a class="text-dark">Pemasok</a>
                        <input class="form-control input-bb" type="text" name="supplier_name" id="supplier_name" value="{{$PurchaseInvoice->getSupplierName($purchaseinvoice['supplier_id'])}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="purchase_invoice_id" id="purchase_invoice_id" value="{{$purchase_invoice_id}}"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                    <a class="text-dark">Tanggal Invoice Pembelian<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="purchase_invoice_date" id="purchase_invoice_date" onChange="function_elements_add(this.name, this.value);" value="{{date('d/m/Y', strtotime($purchaseinvoice['purchase_invoice_date']))}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Faktur Tax No</a>
                        <input class="form-control input-bb" type="text" name="faktur_tax_no" id="faktur_tax_no" value="{{$purchaseinvoice['faktur_tax_no']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                    <a class="text-dark">Tanggal Jatuh Tempo<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="purchase_invoice_due_date" id="purchase_invoice_due_date" onChange="function_elements_add(this.name, this.value);" value="{{date('d/m/Y', strtotime($purchaseinvoice['purchase_invoice_due_date']))}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                    <a class="text-dark">Tanggal Retur Pembelian<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="date" name="purchase_order_return_date" id="purchase_order_return_date" onChange="function_elements_add(this.name, this.value);" required/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <a class="text-dark">Keterangan</a>
                    <div class="">
                        <textarea rows="3" type="text" class="form-control input-bb" name="purchase_invoice_remark" onChange="elements_add(this.name, this.value);" id="purchase_invoice_remark" readonly>{{$purchaseinvoice['purchase_invoice_remark']}}</textarea>
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
                Detail Retur Pembelian
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
                                <th style='text-align:center'>Nomor Batch</th>
                                <th style='text-align:center'>Nama Barang</th>
                                <th style='text-align:center'>Satuan</th>
                                <th style='text-align:center'>Harga Satuan</th>
                                <th style='text-align:center'>Quantity PO</th>
                                <th style='text-align:center'>Quantity Penerimaan</th>
                                <th style='text-align:center'>Quantity Invoice</th>
                                <th style='text-align:center'>Quantity Retur</th>
                                <th style='text-align:center'>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($purchaseinvoiceitem)==0)
                                    <tr><th colspan='8' style='text-align  : center !important;'>Data Kosong</th></tr>
                            @else
                                @php
                                    $no             = 1;
                                    $total_price    = 0;
                                    $total_item     = 0;
                                    $qtyPo = 0;
                                    $qtyReceived = 0;
                                    $qtyRetur = 0;
                                @endphp    
                                @foreach ($purchaseinvoiceitem AS $key => $val)
                                @php
                                    $qtyPo = $PurchaseOrderReturn->getQuantityPO($val['goods_received_note_item_id']);
                                    $qtyReceived = $PurchaseOrderReturn->getQuantityTerima($val['goods_received_note_item_id']);
                                    $qtyRetur = $qtyPo - $qtyReceived;
                                @endphp  
                                    <tr>
                                        <td style='text-align  : center'>{{$no}}
                                        <input hidden class='form-control input-bb' style='text-align  : right !important;' type='number' name='purchase_invoice_item_id_{{ $no }}' id='purchase_invoice_item_id_{{ $no }}' value="{{$val['purchase_invoice_item_id']}}" readonly/>
                                        <input hidden class='form-control input-bb' style='text-align  : right !important;' type='number' name='purchase_order_item_id_{{ $no }}' id='purchase_order_item_id_{{ $no }}' value="{{$PurchaseOrderReturn->getPoItemId($val['goods_received_note_item_id'])}}" readonly/>
                                        </td>
                                        <td style='text-align  : left !important;'>{{$PurchaseInvoice->getItemCategoryName($val['item_category_id'])}}
                                        <input hidden class='form-control input-bb' style='text-align  : right !important;' type='number' name='item_category_id_{{ $no }}' id='item_category_id_{{ $no }}' value="{{ $val['item_category_id'] }}" readonly/>
                                        </td>
                                        <td>
                                        <input  class='form-control input-bb' style='text-align  : right !important;' type='number' name='item_batch_number_{{ $no }}' id='item_batch_number_{{ $no }}' value="{{ $PurchaseOrderReturn->getItemBatchNumber($val['goods_received_note_item_id']) }}" readonly/>
                                        </td>
                                        <td style='text-align  : left !important;'>{{$PurchaseInvoice->getItemTypeName($val['item_type_id'])}}
                                        <input hidden class='form-control input-bb' style='text-align  : right !important;' type='number' name='item_type_id_{{ $no }}' id='item_type_id_{{ $no }}' value="{{ $val['item_type_id'] }}" readonly/>
                                        <input hidden class='form-control input-bb' style='text-align  : right !important;' type='text' name='item_expired_date_{{ $no }}' id='item_expired_date_{{ $no }}' value="{{ $PurchaseOrderReturn->getItemExpDate($val['goods_received_note_item_id']) }}" readonly/>
                                        </td>
                                        <td style='text-align  : left !important;'>{{$PurchaseInvoice->getItemUnitName($val['item_unit_id'])}}
                                        <input hidden class='form-control input-bb' style='text-align  : right !important;' type='number' name='item_unit_id_{{ $no }}' id='item_unit_id_{{ $no }}' value="{{ $val['item_unit_id'] }}" readonly/>
                                        </td>
                                        <td style='text-align  : right !important;'>{{number_format($val['item_unit_cost'],2,',','.')}}</td>
                                        <td style='text-align  : right !important;'>{{$PurchaseOrderReturn->getQuantityPO($val['goods_received_note_item_id'])}}</td>
                                        <td style='text-align  : right !important;'>{{$PurchaseOrderReturn->getQuantityTerima($val['goods_received_note_item_id'])}}</td>
                                        <td style='text-align  : right !important;'>{{$val['quantity']}}</td>
                                        <td style='text-align  : right !important;'>
                                            <input hidden class='form-control input-bb' style='text-align  : right !important;' type='number' name='quantity_order_{{ $no }}' id='quantity_order_{{ $no }}' value="{{ $qtyPo }}" readonly/>
                                            <input class='form-control input-bb' style='text-align  : right !important;' type='number' name='quantity_return_{{ $no }}' id='quantity_return_{{ $no }}' value="{{ $qtyRetur }}" readonly/>
                                        </td>
                                        <td style='text-align  : right !important;'>{{number_format($val['subtotal_amount'],2,',','.')}}
                                        <input hidden class='form-control input-bb' style='text-align  : right !important;' type='number' name='total_amount_{{ $no }}' id='total_amount_{{ $no }}' value="{{ $qtyRetur * $val['item_unit_cost'] }}" readonly/>
                                        </td>
                                    </tr>
                                    @php
                                        $totalRow = $no;
                                        $no++;
                                        $total_price += $val['subtotal_amount'];
                                        $total_item  += $val['quantity'];
                                        $totalAfterPpn = $total_price + $purchaseorder->ppn_in_amount;
                                        
                                    @endphp
                                @endforeach
                                <th style='text-align  : center' colspan='4'>Total</th>
                                <th style='text-align  : center' colspan='5'></th>
                                <th style='text-align  : right'>{{$total_item}}</th>
                                <th style='text-align  : right'>{{number_format($total_price,2,',','.')}}
                                    <input class='form-control input-bb' type='hidden' name='subtotal_amount' id='subtotal_amount' value='{{$total_price}}'/>  
                                    <input class='form-control input-bb' type='hidden' name='total_item' id='total_item' value='{{$total_item}}'/>    
                                    <input class='form-control input-bb' type='hidden' name='total_price' id='total_price' value='{{$total_price}}'/>  
                                </th>
                                <tr>
                                    <td style='text-align  : center' colspan='5'><b>PPN Masuk (%)</b></td>
                                    <td style='text-align  : center'><b>:</b></td>
                                    <td colspan='3'></td>
                                    <td>
                                        <input style='text-align  : right' type="text" class="form-control" name="ppn_in_percentage" id="ppn_in_percentage" value="{{ $purchaseorder->ppn_in_percentage }}" readonly>
                                    </td>
                                    <td>
                                        <input type="text" style='text-align  : right' class="form-control" name="ppn_in_amount_view" id="ppn_in_amount_view" value="{{number_format($purchaseorder->ppn_in_amount,2,',','.')}}" readonly>
                                        <input type="hidden" style='text-align  : right' class="form-control" name="ppn_in_amount" id="ppn_in_amount" value="{{$purchaseorder->ppn_in_amount}}" >
                                    </td>
                                </tr>
                                <tr>
                                    <td style='text-align  : center' colspan='5'><b>Total Harga Akhir </td>
                                    <td style='text-align  : center'><b>:</b></td>
                                    <td colspan='4'></td>
                                    <td style='text-align  : center'>
                                        <input type="text" style='text-align  : right' class="form-control" name="subtotal_amount_after_ppn_view" id="subtotal_amount_after_ppn_view" value="{{number_format($totalAfterPpn,2,',','.')}}" readonly>
                                        <input type="hidden" style='text-align  : right' class="form-control" name="subtotal_amount_after_ppn" id="subtotal_amount_after_ppn" value="{{$totalAfterPpn}}" readonly>
                                    </td>
                                    <input type="hidden" style='text-align  : right' class="form-control" name="total_no" id="total_no" value="{{$totalRow}}" readonly>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <button type="reset" name="Reset" class="btn btn-danger" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
                <button type="submit" name="Save" class="btn btn-primary" title="Save"><i class="fa fa-check"></i> Simpan</button>
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