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

    function disapprove(){
        var sales_order_id 	= $("#sales_order_id").val();
        $.ajax({
            type: "POST",
            url : "{{route('process-disapprove-sales-order')}}",
            dataType: "html",
            data: {
                'sales_order_id'	: sales_order_id,
                '_token'            : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                window.location.href = "{{route('sales-order-approval')}}"
            },
            error: function(data)
            {
                console.log(data);
            }
        });
    }
</script>
@stop
@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('sales-order-approval') }}">Daftar Persetujuan Sales Order</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail Persetujuan Sales Order</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Form Detail Persetujuan Sales Order</b>
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
            Form Detail
        </h5>
        <div class="float-right">
            <button onclick="location.href='{{ url('sales-order-approval') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-approve-sales-order')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Tanggal SO</a>
                        <input class="form-control input-bb" type="text" name="sales_order_date" id="sales_order_date" value="{{date('d/m/Y', strtotime($salesorder['sales_order_date']))}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="sales_order_id" id="sales_order_id" value="{{$sales_order_id}}" readonly/>
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
                                <th style='text-align:center'>Total Harga/Barang</th>
                                <th style='text-align:center'>Discount A/Barang(%)</th>
                                <th style='text-align:center'>nominal Discount A</th>
                                <th style='text-align:center'>Total Harga Setelah Discount A</th>
                                <th style='text-align:center'>Discount B/Barang(%)</th>
                                <th style='text-align:center'>nominal Discount B</th>
                                <th style='text-align:center'>Total Harga Setelah Discount B</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                                @if(count($salesorderitem)<1)
                                    <tr><th colspan='9' style='text-align  : center !important;'>Data Kosong</th></tr>
                                @else
                                    @php
                                        $no =1;
                                        $total_price = 0;
                                        $total_price_item = 0;
                                        $total_item = 0;
                                        $total_discount_amount = 0;
                                        $total_discount_amount_item = 0;
                                        $last_total_discount_amount = 0;
                                    @endphp
                                    @foreach ($salesorderitem AS $key => $val)
                                            <tr>
                                                <td style='text-align  : center'>{{$no}}.</td>
                                                <td style='text-align  : left !important;'>{{$SalesOrder->getItemTypeName($val['item_type_id'])}}</td>
                                                <td style='text-align  : right !important;'>{{$val['quantity']}}</td>
                                                <td style='text-align  : left !important;'>{{$SalesOrder->getItemUnitName($val['item_unit_id'])}}</td>
                                                <td style='text-align  : right !important;'>{{number_format($val['item_unit_price'],2,',','.')}}</td>
                                                <td style='text-align  : right !important;'>{{number_format($val['subtotal_amount'],2,',','.')}}</td>
                                                <td style='text-align  : right !important;'>{{$val['discount_percentage_item']}}</td>
                                                <td style='text-align  : right !important;'>{{number_format($val['discount_amount_item'],2,',','.')}}</td>
                                                <td style='text-align  : right !important;'>{{number_format($val['subtotal_after_discount_item_a'],2,',','.')}}</td>
                                                <td style='text-align  : right !important;'>{{$val['discount_percentage_item_b']}}</td>
                                                <td style='text-align  : right !important;'>{{number_format($val['discount_amount_item_b'],2,',','.')}}</td>
                                                <td style='text-align  : right !important;'>{{number_format($val['subtotal_after_discount_item_b'],2,',','.')}}</td>
                                    
                                            </tr>
                                        
                                        @php
                                            $no++;
                                            $total_discount_amount += $SalesOrder->getDiscountNota($val['sales_order_id']);
                                            $total_discount_amount_item += $val['discount_amount_item'];
                                            $total_price_item += $val['subtotal_amount'] - $val['discount_amount_item'] - $val['discount_amount_item_b'];
                                            $total_item += $val['quantity'];
                                        @endphp
                                    @endforeach
                                        <tr>
                                            <td style='text-align  : center' colspan='2'><b>Total Harga</b></td>
                                            <td style='text-align  : center'><b>{{$total_item}}</b></td>
                                            <td colspan='8'></td>
                                            <td style='text-align  : right'><b>{{number_format(($total_price_item),2,',','.')}}</b></td>
                                        </tr>
                                        <tr>
                                            <td style='text-align  : center' colspan='2'><b>Discount/Nota</b></td>
                                            <td style='text-align  : center'><b>:</b></tdstyle=>
                                            <td colspan='8'></td>
                                            <td style='text-align  : right'><b>{{number_format($SalesOrder->getDiscountNota($val['sales_order_id']),2,',','.')}}</b></td>
                                        </tr>
                                        <tr>
                                            <td style='text-align  : center' colspan='2'><b>PPN Keluar</b></td>
                                            <td style='text-align  : center'><b>:</b></tdstyle=>
                                            <td colspan='8'></td>
                                            <td style='text-align  : right'><b>{{number_format($SalesOrder->getPPNOut($val['sales_order_id']),2,',','.')}}</b></td>
                                        </tr>
                                        <tr>
                                            <td style='text-align  : center' colspan='2'><b>Total Harga Akhir</b></td>
                                            <td style='text-align  : center'><b>:</b></td>
                                            <td colspan='8  '></td>
                                            <td style='text-align  : right'><b>{{number_format($SalesOrder->getAmountAfterPPN_Out($val['sales_order_id']),2,',','.')}}</b></td>
                                        </tr>
                                        
                                    @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <a name="disapprove" class="btn btn-danger btn-sm" title="disapprove" onclick="disapprove()"><i class="fa fa-times"></i> Disapprove</a>
                <button type="submit" name="approve" class="btn btn-primary btn-sm" title="approve"><i class="fa fa-check"></i> Approve</button>
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