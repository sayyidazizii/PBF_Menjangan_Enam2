@inject('SalesInvoice', 'App\Http\Controllers\SalesInvoiceController')

@extends('adminlte::page')

@section('js')
<script>
	$(document).ready(function(){
        var customer_id    = {!! json_encode($customer_id) !!};
        
        if(customer_id == null){
            $("#customer_id").select2("val", "0");
        }
    });
</script>
@stop

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Laporan Penjualan</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Laporan Penjualan</b> <small>Mengelola Laporan Penjualan</small>
</h3>
<br/>
<div id="accordion">
    <form  method="post" action="{{route('filter-sales-invoice-report')}}" enctype="multipart/form-data">
    @csrf
        <div class="card border border-dark">
        <div class="card-header bg-dark" id="headingOne" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            <h5 class="mb-0">
                Filter
            </h5>
        </div>
    
        <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
            <div class="card-body">
                <div class = "row">
                    <div class = "col-md-4">
                        <div class="form-group form-md-line-input">
                            <section class="control-label">Tanggal Mulai
                                <span class="required text-danger">
                                    *
                                </span>
                            </section>
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="start_date" id="date" onChange="function_elements_add(this.name, this.value);" value="{{$start_date}}"/>
                        </div>
                    </div>

                    <div class = "col-md-4">
                        <div class="form-group form-md-line-input">
                            <section class="control-label">Tanggal Akhir
                                <span class="required text-danger">
                                    *
                                </span>
                            </section>
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="end_date" id="date" onChange="function_elements_add(this.name, this.value);" value="{{$end_date}}"/>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <a class="text-dark">Pembeli</a>
                        <br/>
                        {!! Form::select('customer_id',  $customer, $customer_id, ['class' => 'selection-search-clear select-form', 'id' => 'customer_id']) !!}
                    </div>
                </div>
            </div>
            <div class="card-footer text-muted">
                <div class="form-actions float-right">
                    <a href="{{route('filter-reset-sales-invoice-report')}}" type="reset" name="Reset" class="btn btn-danger btn-sm" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</a>
                    <button type="submit" name="Find" class="btn btn-primary btn-sm" title="Search Data"><i class="fa fa-search"></i> Cari</button>
                </div>
            </div>
        </div>
        </div>
    </form>
</div>
<br/>
@if(session('msg'))
<div class="alert alert-info" role="alert">
    {{session('msg')}}
</div>
@endif 
<div class="card border border-dark">
    <div class="card-header bg-dark clearfix">
        <h5 class="mb-0 float-left">
            Daftar
        </h5>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No.</th>
                        <th width="5%" style='text-align:center'>Tanggal Invoice</th>
                        <th width="10%" style='text-align:center'>No Faktur Pajak</th>
                        <th width="20%" style='text-align:center'>Nama Pembeli</th>
                        <th width="10%" style='text-align:center'>No Invoice Penjualan</th>
                        <th width="20%" style='text-align:center'>Nama Obat</th>
                        <th width="5%" style='text-align:center'>Jumlah</th>
                        <th width="5%" style='text-align:center'>HPP</th>
                        <th width="5%" style='text-align:center'>DISKON</th>
                        <th width="5%" style='text-align:center'>DPP</th>
                        <th width="5%" style='text-align:center'>PPN</th>
                        <th width="5%" style='text-align:center'>TOTAL</th>

                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1; 
                    $salesorderitem = 0;
                    $itemunitcost   = 0;
                    $jumlahDiskon   = 0;
                    $ppn            = 0;
                    $dpp            = 0;
                    $diskonPersen   = 0;

                    $totalJumlah    = 0;
                    $totalHpp       = 0;
                    $totalDiskon    = 0;
                    $totalDpp       = 0;
                    $totalPpn       = 0;
                    $subTotal       = 0;

                    ?>

                    @foreach($salesinvoice as $item)
                    @php

                    $salesorderitem = $SalesInvoice->getSalesOrderItem($item['sales_delivery_note_item_id']);
                    $itemunitcost   = $SalesInvoice->getUnitCost($salesorderitem);
                    $jumlahDiskon   = $item['discount_A'] + $item['discount_B'];
                    $ppn            = $SalesInvoice->getPpnItem($salesorderitem);
                    $dpp            = ($item['item_unit_price'] * $item['quantity']) - $jumlahDiskon;
                    $diskonPersen   = $SalesInvoice->getDiscountAmt($item['sales_delivery_note_item_id']) + $SalesInvoice->getDiscountAmtB($item['sales_delivery_note_item_id']) ;


                    $totalJumlah    += $item['item_unit_price'] * $item['quantity'];
                    $totalHpp       += $itemunitcost * $item['quantity'];
                    $totalDiskon    += $jumlahDiskon;
                    $totalDpp       += $dpp;
                    $totalPpn       += $ppn;
                    $subTotal       += $dpp + $ppn;
                    @endphp
                    <tr>
                        <td style='text-align:center'>{{$no}}.</td>
                        <td>{{ $item['sales_invoice_date']}}</td>
                        <td>{{ $item['faktur_tax_no']}}</td>
                        <td>{{ $SalesInvoice->getCustomerName($item['customer_id'])}}</td>
                        <td>{{ $item['sales_invoice_no']}}</td>
                        <td>{{ $SalesInvoice->getItemTypeName($item['item_type_id']) }}</td>
                        <td>{{ $item['item_unit_price'] * $item['quantity']}}</td>
                        <td>{{ $itemunitcost * $item['quantity']}}</td>
                        <td>{{ $jumlahDiskon }}</td>
                        <td>{{ $dpp }}</td>
                        <td>{{ $ppn }}</td>
                        <td>{{ $dpp + $ppn }}</td>
                    </tr>
                    <?php $no++; ?>
                    @endforeach
                </tbody>
                <tr class="text-bold">
                        <td colspan="6" style='text-align:center'> JUMLAH TOTAL</td>
                        <td>{{ $totalJumlah }}</td>
                        <td>{{ $totalHpp }}</td>
                        <td>{{ $totalDiskon }}</td>
                        <td>{{ $totalDpp }}</td>
                        <td>{{ $totalPpn }}</td>
                        <td>{{ $subTotal }}</td>
                    </tr>
                    <tr class="text-bold">
                        <td colspan="10" style='text-align:center'></td>
                        <td colspan="2" style='text-align:center'>
                            <a href="{{ url('sales-invoice/export') }}"name="Find" class="btn btn-sm btn-info" title="Export Excel"><i class="fa fa-print"></i>Export</a>
                        </td>
                    </tr>
            </table>
        </div>
    </div>
</div>
</div>
<br>
<br>
<br>

@include('footer')

@stop

@section('css')
    
@stop