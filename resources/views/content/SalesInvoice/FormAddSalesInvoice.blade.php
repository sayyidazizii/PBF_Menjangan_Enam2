@inject('SalesInvoice', 'App\Http\Controllers\SalesInvoiceController')
@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />
@section('js')
<script>
	$(document).ready(function(){
        
        $("#sales_delivery_note_id").change(function(){
			var sales_delivery_note_id 	= $("#sales_delivery_note_id").val();
                $.ajax({
                    type: "POST",
                    url : "{{route('sales-invoice-change-delivery-note')}}",
                    dataType: "html",
                    data: {
                        'sales_delivery_note_id'	: sales_delivery_note_id,
                        '_token'                    : '{{csrf_token()}}',
                    },
                    success: function(return_data){ 
                        // var data = JSON.parse(return_data.slice(521));
                        data = JSON.parse(data);
                        $("#customer_id").val(data.customer.customer_name);
                        $("#sales_order_no").val(data.salesorder.sales_order_no);
                        $("#sales_order_date").val(data.salesorder.sales_order_date);
                        $("#expedition_id").val(data.expedition.expedition_name);
                        $("#sales_delivery_note_cost").val(data.salesdeliverynote.sales_delivery_note_cost);
                        $("#driver_name").val(data.salesdeliverynote.driver_name);
                        $("#fleet_police_number").val(data.salesdeliverynote.fleet_police_number);
                        $("#sales_delivery_note_date").val(data.salesdeliverynote.sales_delivery_note_date);
                        $("#tablebody").html(data.salesdeliverynoteitem);
                    },
                    error: function(data)
                    {
                        console.log(data);
                    }
                });

		});  

    });

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
        <li class="breadcrumb-item"><a href="{{ url('sales-invoice') }}">Daftar Invoice Penjualan</a></li>
        <li class="breadcrumb-item"><a href="{{ url('sales-invoice/search-buyers-acknowledgment') }}">Daftar Sales Invoice</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Invoice Penjualan</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Form Tambah Invoice Penjualan</b>
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
            <button onclick="location.href='{{ url('sales-invoice/search-buyers-acknowledgment') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-add-sales-invoice')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">Delivery Note No</a>
                    <input class="form-control input-bb" type="text" name="sales_delivery_note_no" id="sales_delivery_note_no" value="{{$buyersAcknowledgment == null ? : $buyersAcknowledgment['sales_delivery_note_no']}}" readonly/>
                    <input class="form-control input-bb" type="hidden" name="buyers_acknowledgment_id" id="buyers_acknowledgment_id" value="{{$buyersAcknowledgment == null ? : $buyersAcknowledgment['buyers_acknowledgment_id']}}" readonly/>
                    <input class="form-control input-bb" type="hidden" name="sales_delivery_note_id" id="sales_delivery_note_id" value="{{$buyersAcknowledgment == null ? : $buyersAcknowledgment['sales_delivery_note_id']}}" readonly/>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Pembeli</a>
                        <input class="form-control input-bb" type="text" name="customer_name" id="customer_name" value="{{$SalesInvoice->getCustomername($buyersAcknowledgment['customer_id'])}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="customer_id" id="customer_id" value="{{$buyersAcknowledgment['customer_id']}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No Sales Order</a>
                        <input class="form-control input-bb" type="text" name="sales_order_no" id="sales_order_no" value="{{$buyersAcknowledgment == null ? : $buyersAcknowledgment['sales_order_no']}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="sales_order_id" id="sales_order_id" value="{{$buyersAcknowledgment == null ? : $buyersAcknowledgment['sales_order_id']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Sales Order</a>
                        <input class="form-control input-bb" type="text" name="sales_order_date" id="sales_order_date" value="{{$buyersAcknowledgment == null ? : date('d/m/Y', strtotime($buyersAcknowledgment['sales_order_date']))}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Ekspedisi</a>
                        <input class="form-control input-bb" type="text" name="expedition_id" id="expedition_id" value="{{$coreexpedition['expedition_name']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Biaya Ekspedisi</a>
                        <input class="form-control input-bb" type="text" name="sales_delivery_note_cost" id="sales_delivery_note_cost" value="{{number_format($buyersAcknowledgment['sales_delivery_note_cost'],2,',','.')}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Pengemudi</a>
                        <input class="form-control input-bb" type="text" name="driver_name" id="driver_name" value="{{$buyersAcknowledgment == null ? : $buyersAcknowledgment['driver_name']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Plat Nomor Kendaraan</a>
                        <input class="form-control input-bb" type="text" name="fleet_police_number" id="fleet_police_number" value="{{$buyersAcknowledgment == null ? : $buyersAcknowledgment['fleet_police_number']}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Delivery Note</a>
                        <input class="form-control input-bb" type="text" name="sales_delivery_note_date" id="sales_delivery_note_date" value="{{$buyersAcknowledgment == null ? : date('d/m/Y', strtotime($buyersAcknowledgment['sales_delivery_note_date']))}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <section class="control-label">Jatuh Tempo
                        <span class="required text-danger">
                            *
                        </span>
                    </section>
                    <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="sales_invoice_due_date" id="sales_invoice_due_date" onChange="elements_add(this.name, this.value);" value=""/>
                </div>

                    <input class="form-control input-bb" type="hidden" name="warehouse_id" id="warehouse_id" value="{{$buyersAcknowledgment == null ? : $buyersAcknowledgment['warehouse_id']}}" />

                <div class="col-md-6">
                    <section class="control-label">No Faktur Pajak
                    </section>
                    <input class="form-control input-bb" name="faktur_tax_no"  id="faktur_tax_no" type="text" />
                </div>
                <div class="col-md-6">
                    <section class="control-label">No Penerimaan Pembeli
                    </section>
                    <input class="form-control input-bb" name="buyers_acknowledgment_no"  id="buyers_acknowledgment_no" value="{{$buyersAcknowledgment['buyers_acknowledgment_no']}}"  type="text"/>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <a class="text-dark">Keterangan</a>
                    <div class="">
                        <textarea rows="3" type="text" class="form-control input-bb" name="sales_invoice_remark" onChange="elements_add(this.name, this.value);" id="sales_invoice_remark" ></textarea>
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
                                <th style='text-align:center'>Barang</th>
                                <th style='text-align:center'>Qty BPB</th>
                                <th style='text-align:center'>Harga Satuan</th>
                                <th style='text-align:center'>Satuan</th>
                                <th style='text-align:center'>Total</th>
                                <th style='text-align:center'>Diskon 1</th>
                                <th hidden style='text-align:center'>Subtotal Setelah Diskon 1</th>
                                <th style='text-align:center'>Diskon 2</th>
                                <th hidden style='text-align:center'>Subtotal Setelah Diskon 2</th>
                                <th  style='text-align:center'>Total Bayar</th>
                            </tr>
                        </thead>
                        <tbody id='tablebody'>
                            @php
                                $no             = 1;
                                $total_price    = 0;
                                $total_item     = 0;
                                $diskon = 0 ;
                                $total_discount = 0;
                                $totalppn = 0;
                                $total = 0;
                                $totalBayar = 0;
                                $DPP = 0;
                            @endphp
                            @if(count($buyersAcknowledgmentitem)>0)
                                @foreach($buyersAcknowledgmentitem as $val)
                                @php
                                     $total_discount += $SalesInvoice->getDiscount($val['sales_order_item_id']);
                                     $totalA = $val['quantity_received']*$val['item_unit_price']-$SalesInvoice->getDiscount($val['sales_order_item_id']);
                                     $totalB = $totalA + ($SalesInvoice->getDiscountB($val['sales_order_item_id']) + $SalesInvoice->getPpnItem($val['sales_order_item_id']));
                                     $total = $val['quantity_received']*$val['item_unit_price'];
                                     $totalBayar =  $total - ($SalesInvoice->getDiscount($val['sales_order_item_id']) + $SalesInvoice->getDiscountB($val['sales_order_item_id']));
                                        
                              @endphp
                               
                                        <tr>
                                            <td style='text-align  : center'>{{$no}}</td>
                                            <td style='text-align  : left !important;'>
                                                <input class='form-control' type='text' name='item_id_{{ $no }}' id='item_id_{{ $no }}' value='{{$SalesInvoice->getItemTypeName($val['item_type_id'])}}' readonly/>  
                                                <input class='form-control' type='text' name='item_type_id_{{ $no }}' id='item_type_id_{{ $no }}' value='{{$val['item_type_id']}}' hidden/>  
                                            </td>
                                            <td style='text-align  : right !important;'>
                                                <input style='text-align  : right !important;' class='form-control' type='text' name='quantity_{{ $no }}' id='quantity_{{ $no }}' value='{{$val['quantity_received']}}' readonly/>  
                                            </td>
                                            <td style='text-align  : right !important;'>
                                                <input style='text-align  : right !important;' class='form-control' type='text' name='item_unit_price_view{{ $no }}' id='item_unit_price_view{{ $no }}' value='{{number_format($val['item_unit_price'],2,',','.')}}' readonly/>  
                                                <input style='text-align  : right !important;' hidden class='form-control' type='text' name='item_unit_price_{{ $no }}' id='item_unit_price_{{ $no }}' value='{{$val['item_unit_price']}}' readonly/>  
                                            </td>
                                            <td style='text-align  : left !important;'>
                                                <input class='form-control' type='text' name='item_unit_id_view{{ $no }}' id='item_unit_id_view{{ $no }}' value='{{$SalesInvoice->getItemUnitName($val['item_unit_id'])}}' readonly/>  
                                            </td>
                                            <td style='text-align  : left !important;'>
                                                <input class='form-control' type='text' name='' id='' value='{{number_format(($total),2)}}' readonly/>  
                                            </td>
                                                <input class='form-control' type='hidden' name='item_unit_id_{{ $no }}' id='item_unit_id_{{ $no }}' value='{{$val['item_unit_id']}}' readonly/>  
                                            <td style='text-align  : left !important;'>
                                                <input class='form-control' type='text' name='discount_A_{{ $no }}' id='discount_A_{{ $no }}' value='{{$SalesInvoice->getDiscount($val['sales_order_item_id'])}}' readonly/>  
                                            </td>
                                            <td hidden style='text-align  : right !important;'>
                                                <input style='text-align  : right !important;' class='form-control' type='text' name='subtotal_price_A__view{{ $no }}' id='subtotal_price_A__view{{ $no }}' value='{{number_format(($totalA), 2)}}' readonly/>  
                                                <input style='text-align  : right !important;' class='form-control' type='text' name='subtotal_price_A_{{ $no }}' id='subtotal_price_A_{{ $no }}' value='{{$totalA}}' hidden/> 
                                            </td>
                                            <td style='text-align  : left !important;'>
                                                <input class='form-control' type='text' name='discount_B_{{ $no }}' id='discount_B_{{ $no }}' value='{{$SalesInvoice->getDiscountB($val['sales_order_item_id'])}}' readonly/>  
                                            </td>
					                        <td hidden style='text-align  : left !important;'>	
                                                <input class='form-control' type='text' name='ppn_item_{{ $no }}' id='ppn_item_{{ $no }}' value='{{$SalesInvoice->getPpnItem($val['sales_order_item_id'])}}' readonly/>  
                                            </td>
                                            <td hidden style='text-align  : right !important;'>
                                                <input style='text-align  : right !important;' class='form-control' type='text' name='subtotal_price_B__view{{ $no }}' id='subtotal_price_B_view{{ $no }}' value='{{number_format(($totalB), 2)}}' readonly/>  
                                                <input style='text-align  : right !important;' class='form-control' type='text' name='subtotal_price_B_{{ $no }}' id='subtotal_price_B_{{ $no }}' value='{{$totalB}}' hidden/>  
                                            </td>
                                            <td hidden style='text-align  : right !important;'>
                                                <input style='text-align  : right !important;'  class='form-control' type='text' name='item_stock_id_{{$no}}' id='item_stock_id_{{$no}}' value='{{$SalesInvoice->getItemStock($val['sales_delivery_note_item_id'])}}' readonly/>  
                                            </td>
                                            <td hidden style='text-align  : right !important;'>
                                                <input style='text-align  : right !important;'  class='form-control' type='text' name='sales_delivery_note_item_id_{{ $no }}' id='sales_delivery_note_item_id_{{ $no }}' value='{{$val['sales_delivery_note_item_id']}}' readonly/>  
                                            </td>
                                            <td hidden style='text-align  : right !important;'>
                                                <input style='text-align  : right !important;'  class='form-control' type='text' name='sales_order_id_{{ $no }}' id='sales_order_id_{{ $no }}' value='{{$val['sales_order_id']}}' readonly/>  
                                            </td>
                                            <td  style='text-align  : right !important;'>
                                                <input style='text-align  : right !important;'  class='form-control' type='text' name='' id='' value='{{number_format(($totalBayar),2)}}' readonly/>  
                                            </td>
                                        </tr>
                                        @php
                                            $total_no = $no;
                                            $no++;
                                            $total_price    += $totalB;
                                            $total_item     += $val['quantity_received'];
                                            $totalppn += $SalesInvoice->getPpnItem($val['sales_order_item_id']);
                                            $DPP += $totalBayar;
                                        @endphp
                                @endforeach
                            @else
                                <tr>
                                    <td style='text-align  : center; font-weight: bold;' colspan='6'>Data Kosong</td>    
                                </tr>
                            @endif
                                <th style='text-align  : left' colspan='8'>Total</th>
                                <th style='text-align  : right'>
                                    <input hidden class='form-control' style='text-align  : right !important;' type='text' name='total_amount_view' id='total_amount_view' value='{{number_format($total_price,2,',','.')}}' readonly/>   
                                    <input  class='form-control' style='text-align  : right !important;' type='text' name='' id='' value='{{number_format($DPP,2,',','.')}}' readonly/>   
                                    <div class="row mt-2">
                                        <div hidde class="col">
                                            <label style='text-align  : left !important;'>Ppn</label>
                                        </div>
                                        <div  class="col">
                                            <input class='form-control' style='text-align:right;'type='text' name='ppn' id='ppn' value='{{ number_format($SalesInvoice->getPpnItem($val['sales_order_item_id']),2,',','.' )}}' readonly/>
                                            <!-- <input hidden class='form-control' style='text-align:right;'type='text' name='ppn' id='ppn' value='{{ number_format($SalesInvoice->getPpnOut($buyersAcknowledgment['sales_delivery_note_id']),2,',','.' )}}' readonly/> -->
                                            <input class='form-control' style='text-align:right;'type='hidden' name='tax_amount' id='tax_amount' value='{{ $SalesInvoice->getPpnOut($buyersAcknowledgment['sales_delivery_note_id'])}}' readonly/>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col">
                                            <label style='text-align  : left !important;'>Jumlah Total</label>
                                        </div>
                                        <div class="col">
                                            <input class='form-control' style='text-align:right;'type='text' name='subtotal_after_ppn_out' id='subtotal_after_ppn_out' value='{{ number_format($total_price + $SalesInvoice->getPpnOut($buyersAcknowledgment['sales_delivery_note_id']),2,',','.') }}' readonly/>
                                        </div>
                                    </div>
                                    <input class='form-control' type='hidden' name='total_amount' id='total_amount' value='{{$total_price + $SalesInvoice->getPpnOut($buyersAcknowledgment['sales_delivery_note_id'])}}'/>  
                                    <input class='form-control' type='hidden' name='total_item' id='total_item' value='{{$total_item}}'/>    
                                    <input class='form-control' type='hidden' name='total_no' id='total_no' value='{{$total_no}}'/>  

                                </th>
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
<br>
<br>
<br>

@include('footer')

@stop

@section('css')
    
@stop