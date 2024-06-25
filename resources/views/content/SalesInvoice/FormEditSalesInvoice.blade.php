@inject('SalesInvoice', 'App\Http\Controllers\SalesInvoiceController')
@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />
@section('js')
<script>
$(document).ready(function() {
    $("#sales_delivery_note_id").change(function() {
        var sales_delivery_note_id = $("#sales_delivery_note_id").val();
        $.ajax({
            type: "POST",
            url: "{{ route('sales-invoice-change-delivery-note') }}",
            dataType: "html",
            data: {
                'sales_delivery_note_id': sales_delivery_note_id,
                '_token': '{{ csrf_token() }}'
            },
            success: function(return_data) {
                var data = JSON.parse(return_data);
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
            error: function(data) {
                console.log(data);
            }
        });
    });

    function toRp(number) {
        var numberStr = number.toString(),
            rupiah = numberStr.split('.')[0],
            cents = (numberStr.split('.')[1] || '') + '00';
        rupiah = rupiah.split('').reverse().join('')
            .replace(/(\d{3}(?!$))/g, '$1.')
            .split('').reverse().join('');
        return rupiah + ',' + cents.slice(0, 2);
    }

});

function toRp(number) {
        var numberStr = number.toString(),
            rupiah = numberStr.split('.')[0],
            cents = (numberStr.split('.')[1] || '') + '00';
        rupiah = rupiah.split('').reverse().join('')
            .replace(/(\d{3}(?!$))/g, '$1.')
            .split('').reverse().join('');
        return rupiah + ',' + cents.slice(0, 2);
    }

    function calculateTotal(i) {
        var quantity = $("#quantity_" + i).val();
        var item_unit_price = $("#item_unit_price_" + i).val();
        var discount_A = $("#discount_A_" + i).val();
        var discount_B = $("#discount_B_" + i).val();
        var total = parseFloat(quantity) * parseFloat(item_unit_price);
        var total_A = total - discount_A;
        var total_B = total_A - discount_B;
        var total_amount = total_B;
        $("#total_view_" + i).val(toRp(total));
        $("#total_" + i).val(total);
        $("#subtotal_price_A_view" + i).val(toRp(total_A));
        $("#subtotal_price_A_" + i).val(total_A);
        $("#total_bayar_view_" + i).val(toRp(total_B));
        $("#bayar_" + i).val(total_B);
        updateTotalAmount();
    }

    function updateTotalAmount() {
        var totalAmount = 0;
        var ppn = parseFloat($("#ppn").val()); // Parse ppn as float
        $("input[name^='bayar_']").each(function() {
            var val = parseFloat($(this).val());
            if (!isNaN(val)) {
                totalAmount += val;
            }
        });
        $("#total_amount_view").val(toRp(totalAmount));
        $("#total_amount").val(totalAmount);
        var subtotal = totalAmount + ppn; // Calculate subtotal including ppn
        $("#subtotal_after_ppn_out_view").val(toRp(subtotal));
        $("#subtotal_after_ppn_out").val(subtotal);
    }

</script>




@stop
@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('sales-invoice') }}">Daftar Invoice Penjualan</a></li>
        <li class="breadcrumb-item active" aria-current="page">Edit Invoice Penjualan</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    Form Edit Invoice Penjualan
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
            <button onclick="location.href='{{ url('sales-invoice') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-edit-sales-invoice')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">Delivery Note No</a>
                    <input class="form-control input-bb" type="text" name="sales_delivery_note_no" id="sales_delivery_note_no" value="{{$salesdeliverynote == null ? : $salesdeliverynote['sales_delivery_note_no']}}" readonly/>
                    <input class="form-control input-bb" type="hidden" name="buyers_acknowledgment_id" id="buyers_acknowledgment_id" value="{{$salesinvoice == null ? : $salesinvoice['buyers_acknowledgment_id']}}" readonly/>
                    <input class="form-control input-bb" type="hidden" name="sales_delivery_note_id" id="sales_delivery_note_id" value="{{$salesdeliverynote == null ? : $salesdeliverynote['sales_delivery_note_id']}}" readonly/>
                    <input class="form-control input-bb" type="hidden" name="sales_invoice_id" id="sales_invoice_id" value="{{$salesinvoice == null ? : $salesinvoice['sales_invoice_id']}}" readonly/>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Pembeli</a>
                        <input class="form-control input-bb" type="text" name="customer_name" id="customer_name" value="{{$SalesInvoice->getCustomername($salesinvoice['customer_id'])}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="customer_id" id="customer_id" value="{{$salesinvoice['customer_id']}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No Sales Order</a>
                        <input class="form-control input-bb" type="text" name="sales_order_no" id="sales_order_no" value="{{$salesorder['sales_order_no']}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="sales_order_id" id="sales_order_id" value="{{$salesorder['sales_order_id']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Sales Order</a>
                        <input class="form-control input-bb" type="text" name="sales_order_date" id="sales_order_date" value="{{$salesorder['sales_order_date']}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Ekspedisi</a>
                        <input class="form-control input-bb" type="text" name="expedition_id" id="expedition_id" value="{{$SalesInvoice->getExpeditionName($salesdeliverynote['expedition_id'])}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Biaya Ekspedisi</a>
                        <input class="form-control input-bb" type="text" name="sales_delivery_note_cost" id="sales_delivery_note_cost" value="{{number_format($salesdeliverynote['sales_delivery_note_cost'],2,',','.')}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Pengemudi</a>
                        <input class="form-control input-bb" type="text" name="driver_name" id="driver_name" value="{{$salesdeliverynote['driver_name']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Plat Nomor Kendaraan</a>
                        <input class="form-control input-bb" type="text" name="fleet_police_number" id="fleet_police_number" value="{{$salesdeliverynote['fleet_police_number']}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Delivery Note</a>
                        <input class="form-control input-bb" type="text" name="sales_delivery_note_date" id="sales_delivery_note_date" value="{{$salesdeliverynote['sales_delivery_note_date']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <section class="control-label">Jatuh Tempo
                        <span class="required text-danger">
                            *
                        </span>
                    </section>
                    <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="sales_invoice_due_date" id="sales_invoice_due_date" onChange="elements_add(this.name, this.value);" value="{{$salesinvoice['sales_invoice_due_date']}}"/>
                </div>

                <div class="col-md-6">
                    <section class="control-label">Gudang
                    </section>
                    <input class="form-control input-bb" type="text"  value="{{$salesdeliverynote['warehouse_name']}}" readonly/>
                    <input class="form-control input-bb" type="hidden" name="warehouse_id" id="warehouse_id" value="{{$salesdeliverynote == null ? : $salesdeliverynote['warehouse_id']}}" />
                </div>
                <div class="col-md-6">
                    <section class="control-label">No Faktur Pajak
                    </section>
                    <input class="form-control input-bb" name="faktur_tax_no"  id="faktur_tax_no"  type="text"  value="{{$salesinvoice['faktur_tax_no']}}" />
                </div>
                <div class="col-md-6">
                    <section class="control-label">No Penerimaaan Barang
                    </section>
                    <input class="form-control input-bb" name="goods_received_note_no"  id="goods_received_note_no"  type="text"  value="{{$SalesInvoice->getNoBpb($salesinvoice['buyers_acknowledgment_id'])}}" />
                </div>
                <div class="col-md-6">
                    <section class="control-label">Tanda Terima Faktur
                    </section>
                    <input class="form-control input-bb" name="ttf_no"  id="ttf_no"  type="text" value="{{$salesinvoice['ttf_no']}}"/>
                </div>

            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <a class="text-dark">Keterangan</a>
                    <div class="">
                        <textarea rows="3" type="text" class="form-control input-bb" name="sales_invoice_remark" onChange="elements_add(this.name, this.value);" id="sales_invoice_remark" >{{$salesinvoice['sales_invoice_remark']}}</textarea>
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
                                <th style='text-align:center'>Barang</th>
                                <th style='text-align:center'>Quantity</th>
                                <th style='text-align:center'>Satuan</th>
                                <th style='text-align:center'>Harga Satuan</th>
                                <th style='text-align:center'>Total</th>
                                <th style='text-align:center'>Diskon 1</th>
                                <th style='text-align:center'>After Diskon A</th>
                                <th style='text-align:center'>Diskon 2</th>
				                <th hidden style='text-align:center'>PPN Item</th>
                                <th style='text-align:center'>Total Bayar</th>
                            </tr>
                        </thead>
                        <tbody id='tablebody'>
                            <?php
                            // print_r($salesdeliverynote);
                            $no             = 1;
                            $total_price    = 0;
                            $total_item     = 0;
                            $total          = 0;
                            $totalBayar     = 0;
                            $ppn            = 0;
                            $DPP            = 0;
                            $discountA      = 0; 
                            $discountB      = 0; 
                            $total_discount = 0;
                            $discount_A     = 0;
                            $discount_B     = 0;
                            if(count($salesinvoiceitem)>0){ 

                                foreach($salesinvoiceitem as $val){
                                    $item = $SalesInvoice->getSalesDeliveryNoteItem($val['sales_delivery_note_item_id']);
                                    $discount_A = $SalesInvoice->getDiscount($SalesInvoice->getSalesOrderItem($val['sales_delivery_note_item_id']));
                                    $discount_B = $SalesInvoice->getDiscountB($SalesInvoice->getSalesOrderItem($val['sales_delivery_note_item_id']));
                                    $total = $item['quantity'] * $val['item_unit_price'];
                                    $totalBayar =$val['subtotal_price_A'] - $discount_B;
                                    $ppn += $SalesInvoice->getPpnItem($SalesInvoice->getSalesOrderItem($val['sales_delivery_note_item_id']));
                                    $discountA += $discount_A; 
                                    $discountB += $discount_B; 
                                    $total_discount = $discountA + $discountB;
                                ?>
                                    <tr>
                                            <td style='text-align  : center'>{{ $no }}</td>
                                            <td style='text-align  : left !important;'>
                                                {!! Form::select('item_type_id_'.$no,  $invitemtype, $val['item_type_id'], ['class' => 'selection-search-clear select-form', 'id' => 'item_type_id_'.$no]) !!}
                                            </td>
                                            <td style='text-align  : right !important;'>
                                                <input class='form-control' type='text' hidden name='sales_invoice_item_id_{{ $no }}' id='sales_invoice_item_id_{{ $no }}' value='{{  $val['sales_invoice_item_id'] }}' readonly/>  
                                                <input style='text-align  : right !important;' class='form-control' type='text' name='quantity_{{ $no }}' id='quantity_{{ $no }}' onchange="calculateTotal({{ $no }})" value='{{ $val['quantity'] }}' />  
                                            </td>   
                                            <td style='text-align  : left !important;'>
                                                <input class='form-control' type='text' name='item_unit_{{ $no }}' id='item_unit_{{ $no }}' value='{{  $SalesInvoice->getItemUnitName($item['item_unit_id'])}}' readonly/>  
                                            </td>
                                            <td style='text-align  : right !important;'>
                                                <input style='text-align  : right !important;' class='form-control' type='text' name='item_unit_price_{{ $no }}' id='item_unit_price_{{ $no }}' onchange="calculateTotal( {{ $no }} )" value='{{ $val['item_unit_price'] }}' />  
                                            </td>
                                            <td style='text-align  : right !important;'>
                                                <input style='text-align  : right !important;' class='form-control' type='text' name='total_view_{{ $no }}' id='total_view_{{ $no }}' value='{{ number_format($total, 2)}}' readonly/>  
                                                <input style='text-align  : right !important;' class='form-control' type='text' hidden name='total_{{ $no }}' id='total_{{ $no }}' value='{{ $total }}' readonly/>  
                                            </td>
                                            <td style='text-align  : right !important;'>
                                                <input style='text-align  : right !important;' class='form-control' type='text' name='discount_A_{{ $no }}' id='discount_A_{{ $no }}' onchange="calculateTotal( {{ $no }} )" value='{{  $discount_A }}' />  
                                            </td>
                                             <td  style='text-align  : right !important;'>
                                                <input style='text-align  : right !important;' class='form-control' type='text' name='subtotal_price_A_view{{ $no }}' id='subtotal_price_A_view{{ $no }}' value='{{ number_format($total -  $discount_A , 2) }}' readonly/>  
                                                <input style='text-align  : right !important;' class='form-control' type='text' hidden name='subtotal_price_A_{{ $no }}' id='subtotal_price_A_{{ $no }}' value='{{ $total -  $discount_A }}' readonly/>  
                                            </td>
                                            <td style='text-align  : right !important;'>
                                                <input style='text-align  : right !important;' class='form-control' type='text' name='discount_B_{{ $no }}' id='discount_B_{{ $no }}'  onchange="calculateTotal( {{ $no }} )" value='{{ $discount_B }}' />  
                                            </td>
 						                    <td hidden style='text-align  : right !important;'>
                                                <input style='text-align  : right !important;' class='form-control' type='text' name='ppn_item_amount_{{ $no }}' id='ppn_item_amount_{{ $no }}' value='{{  $SalesInvoice->getPpnItem($SalesInvoice->getSalesOrderItem($val['sales_delivery_note_item_id']))}}' readonly/>  
                                            </td>

                                            <td style='text-align  : right !important;'>
                                                <input style='text-align  : right !important;' class='form-control' type='text' name='total_bayar_view_{{ $no }}' id='total_bayar_view_{{ $no }}' value='{{ number_format(($totalBayar), 2)}}' readonly/>  
                                                <input style='text-align  : right !important;' class='form-control' type='text' hidden name='bayar_{{ $no }}' id='bayar_{{ $no }}' value='{{ $totalBayar }}' readonly/>  
                                            </td>
                                        </tr>
                                    <?php 
                                    $no++;
                                    $total_price    += ($val['subtotal_price_B']);
                                    $total_item     += $item['quantity'];
                                    $totalPpn       = $ppn;
                                    $DPP +=$totalBayar; 
                                    ?> 
                                    
                            <?php    
                            } 
                            }else{ ?>
                                <tr>
                                    <td style='text-align  : center; font-weight: bold;' colspan='6'>Data Kosong</td>    
                                </tr>
                            <?php } ?>
                            <input class='form-control' style='text-align  : right !important;' hidden type='text' name='total_no' id='total_no' value='{{ count($salesinvoiceitem) }}' readonly/>   
                            <input class='form-control' style='text-align  : right !important;' hidden type='text' name='total_discount_amount' id='total_discount_amount' value='{{ number_format($total_discount, 2, '.', '') }}' readonly/>

                                <th style='text-align  : left' colspan='8'>Total</th>
                                <th style='text-align  : right' colspan='3'>
                                    <input class='form-control' style='text-align  : right !important;' type='text' name='total_amount_view' id='total_amount_view' value='{{ number_format($DPP, 2)}}' readonly/>   
                                    <input class='form-control' style='text-align  : right !important;' hidden type='text' name='total_amount' id='total_amount' value='{{ $DPP }}' readonly/>   
                                    <div class='row mt-2'>
                                        <div class='col'>
                                            <label style='text-align  : left !important;'>PPN</label>
                                        </div>
                                        <div class='col'>
                                            <input class='form-control' style='text-align:right;'type='text' name='ppn_view' id='ppn_view' value='{{ number_format($ppn, 2 )}}' readonly/>
                                            <input class='form-control' style='text-align:right;' hidden type='text' name='ppn' id='ppn' value='{{ $ppn }}' readonly/>
                                        </div>
                                    </div>
                                    <div class='row mt-2'>
                                        <div class='col'>
                                            <label style='text-align  : left !important;'>Jumlah Total</label>
                                        </div>
                                        <div class='col'>
                                            <input class='form-control' style='text-align:right;'type='text' name='subtotal_after_ppn_out_view' id='subtotal_after_ppn_out_view' value='{{ number_format($DPP + $ppn,2)}}' readonly/>
                                            <input class='form-control' style='text-align:right;' type='text' hidden name='subtotal_after_ppn_out' id='subtotal_after_ppn_out' value='{{ $DPP + $ppn }}' readonly/>
                                        </div>
                                    </div>
                                    <input class='form-control' type='hidden' name='total_item' id='total_item' value='{{  $total_item }}'/>    

                                </th>
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

@stop

@section('footer')
    
@stop

@section('css')
    
@stop