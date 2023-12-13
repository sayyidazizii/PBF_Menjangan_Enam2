@inject('SalesOrder', 'App\Http\Controllers\SalesOrderController')
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
        <li class="breadcrumb-item"><a href="{{ url('sales-order') }}">Daftar Sales Order</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail Sales Order</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Detail Sales Order</b>
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
            <button onclick="location.href='{{ url('sales-order') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-add-sales-order')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Tanggal PO</a>
                        <input class="form-control input-bb" type="text" name="sales_order_date" id="sales_order_date" value="{{date('d/m/Y', strtotime($salesorder['sales_order_date']))}}" readonly/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Pengiriman</a>
                        <input class="form-control input-bb" type="text" name="sales_order_delivery_date" id="sales_order_delivery_date" value="{{date('d/m/Y', strtotime($salesorder['sales_order_delivery_date']))}}" readonly/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Pelanggan</a>
                        <input class="form-control input-bb" type="text" name="customer_id" id="customer_id" value="{{$SalesOrder->getCoreCustomerName($salesorder['customer_id'])}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12 ">
                    <a class="text-dark">Keterangan</a>
                    <div class="">
                        <textarea rows="3" type="text" class="form-control input-bb" name="sales_order_remark" onChange="function_elements_add(this.name, this.value);" id="sales_order_remark" readonly>{{$salesorder['sales_order_remark']}}</textarea>
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
                                <th style='text-align:center'>Qty</th>
                                <th style='text-align:center'>Satuan</th>
                                <th style='text-align:center'>Harga Satuan</th>
                                <th style='text-align:center'>Discount 1(%)</th>
                                <th style='text-align:center'> Discount 1</th>
                                <th style='text-align:center'>Total</th>
                                <th style='text-align:center'>Discount 2(%)</th>
                                <th style='text-align:center'> Discount 2</th>
                                <th style='text-align:center'>PPN</th>
                                <th style='text-align:center'>Total Bayar</th>
                               
                            </tr>
                        </thead>
                        <tbody>
                            @if(count($salesorderitem)<1)
                                <tr><th colspan='7' style='text-align  : center !important;'>Data Kosong</th></tr>
                            @else
                                @php
                                    $total_discount_amount = 0;
                                    $total_price = 0;
                                    $total_item = 0;
                                    $total_price_after_discount_item = 0;
                                    $ppn_amount = 0 ;
                                    $no = 1;
                                @endphp
                                @foreach ($salesorderitem AS $key => $val) 
                                    <tr>
                                        <td style='text-align  : center'>{{$no}}</td>
                                        <td style='text-align  : left !important;'>{{$val['item_type_name']}}</td>
                                        <td style='text-align  : right !important;'>{{$val['quantity']}}</td>
                                        <td style='text-align  : left !important;'>{{$SalesOrder->getItemUnitName($val['item_unit_id'])}}</td>
                                        <td style='text-align  : right !important;'>{{number_format($val['item_unit_price'],2,',','.')}}</td>
                                        <td style='text-align  : right !important;'>{{$val['discount_percentage_item']}}</td>
                                        <td style='text-align  : right !important;'>{{number_format($val['discount_amount_item'],2,',','.')}}</td>
                                        <td style='text-align  : right !important;'>{{number_format($val['subtotal_after_discount_item_a'],2,',','.')}}</td>
                                        <td style='text-align  : right !important;'>{{$val['discount_percentage_item_b']}}</td>
                                        <td style='text-align  : right !important;'>{{number_format($val['discount_amount_item_b'],2,',','.')}}</td>
                                        <td style='text-align  : right !important;'>{{number_format($val['ppn_amount_item'],2,',','.')}}</td>
                                        <td style='text-align  : right !important;'>{{number_format($val['subtotal_after_discount_item_b'],2,',','.')}}</td>
                                    </tr>
                                    @php
                                    $no++;
                                    $total_price_after_discount_item += $val['subtotal_after_discount_item_b'];
                                    $total_item+=$val['quantity'];
                                    $ppn_amount += $val['ppn_amount_item'];
                                    $price_after_discount_amount = $total_price_after_discount_item + $ppn_amount;
                                @endphp
                                
                                @endforeach
                                <th style='text-align  : center' colspan='2'>Total</th>
                                <th style='text-align  : right' >{{$total_item}}</th>
                                <th colspan='8'></th>
                                <th style='text-align  : right'>{{number_format($total_price_after_discount_item,2,',','.')}}</th>
                                <th hidden>
                                    <input class='form-control input-bb' type='text' name='total_price_all' id='total_price_all' value='{{$total_price_after_discount_item}}'/>
                                    <input class='form-control input-bb' type='text' name='total_item_all' id='total_item_all' value='{{$total_item}}'/>
                                </th>
                                <tr hidden>
                                    <td style='text-align  : center' colspan='2'><b>Discount Nota (%)</b></td>
                                    <td style='text-align  : center'><b>:</b></td>
                                    <td colspan='7'></td>
                                    <td>
                                        <input style='text-align  : right' type="text" class="form-control" name="discount_percentage" id="discount_percentage" value="0"  placeholder="isi 0 jika kosong"></td>
                                    <td>
                                        <input type="hidden" class="form-control" name="discount_amount" id="discount_amount" readonly>
                                        <input type="hidden" class="form-control" name="subtotal_after_discount" id="subtotal_after_discount" readonly>
                                        <input style='text-align  : right;  font-weight: bold;' type="text"  value="{{$salesorder['discount_amount']}}"  class="form-control" name="discount_amount_view" id="discount_amount_view" readonly>
                                    </td>
                                </tr>
                                <tr hidden>
                                    <td style='text-align  : center' colspan='2'><b>Harga Setelah Discount Nota </td>
                                    <td style='text-align  : center'><b>:</b></td>
                                    <td colspan='8'></td>
                                    <td style='text-align  : center'>
                                        <input style='text-align  : right;  font-weight: bold;' type="text" value="{{number_format($price_after_discount_amount,2,',','.')}}" class="form-control" name="subtotal_after_discount_view" id="subtotal_after_discount_view" readonly>
                                    </td>
                                </tr>
                                <tr>
                                    <td  style='text-align  : center' colspan='2'><b>Total PPN Keluar (%)</b></td>
                                    <td  style='text-align  : center'><b>:</b></td>
                                    <td  colspan='7'></td>
                                    <td>
                                    <td>
                                        <input hidden type="text" class="form-control" name="ppn_out_amount" id="ppn_out_amount" value="{{ $ppn_amount }}" readonly>
                                        <input  style='text-align  : right;  font-weight: bold;' type="text" class="form-control" value="{{ number_format($ppn_amount,2,',','.') }}" name="ppn_out_amount_view" id="ppn_out_amount_view" readonly>
                                    </td>
                                </tr>
                                <tr>
                                    <td style='text-align  : center' colspan='2'><b>Total Harga Akhir </td>
                                    <td style='text-align  : center'><b>:</b></td>
                                    <td colspan='8'></td>
                                    <td style='text-align  : center'>
                                        <input type="hidden" class="form-control" name="subtotal_after_ppn_out" id="subtotal_after_ppn_out" readonly>
                                        <input style='text-align  : right;  font-weight: bold;' type="text"  value="{{number_format($price_after_discount_amount,2,',','.')}}"  class="form-control" name="subtotal_after_ppn_out_view" id="subtotal_after_ppn_out_view" readonly>
                                    </td>
                                </tr>
                    @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <br/>
    <div class="card border border-dark">
        <div class="card-header border-dark bg-dark">
            <h5 class="mb-0 float-left">
                Foto Kwitansi
            </h5>
        </div>
    
        <div class="card-body">
            <div class="form-body form">
                <div class="table-responsive" style="text-align : center;">
                    <img src="{{asset('storage/receipt/'.$salesorder['receipt_image'])}}" style='width:2500px; height:1000px;'>
                </div>
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