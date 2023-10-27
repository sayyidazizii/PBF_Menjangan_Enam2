@inject('BuyersAcknowledgment', 'App\Http\Controllers\BuyersAcknowledgmentController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>
	$(document).ready(function(){
        // $("#expedition_id").select2("val", "0");
	});
</script>
@stop

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('return-pdp-lost-on-expedition') }}">Daftar Penerimaan Pihak Pembeli</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail Penerimaan Pihak Pembeli</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    Form Detail Penerimaan Pihak Pembeli
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
            <button onclick="location.href='{{ url('buyers-acknowledgment') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="" enctype="multipart/form-data">
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
                        <input class="form-control input-bb" type="text" name="sales_delivery_order_no" id="sales_delivery_order_no" onChange="function_elements_add(this.name, this.value);" value="{{$BuyersAcknowledgment->getSalesDeliveryNoteNo($buyers_acknowledgment['sales_delivery_note_id'])}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="sales_delivery_note_no" id="sales_delivery_note_no" onChange="function_elements_add(this.name, this.value);" value="{{$buyers_acknowledgment_id}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Delivery Note</a>
                        <input class="form-control input-bb" type="text" name="sales_delivery_order_date" id="sales_delivery_order_date" onChange="function_elements_add(this.name, this.value);" value="{{$BuyersAcknowledgment->getSalesDeliveryNoteDate($buyers_acknowledgment['sales_delivery_note_id'])}}" readonly/>
                    </div>
                </div>
            </div>
            <br>
            <div class="row">
                <h5 class="form-section"><b>Detail Penerimaan Pihak Pembeli</b></h5>
            </div>
            <hr style="margin:0;">
            <br/>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No. Penerimaan</a>
                        <input class="form-control input-bb" type="text" name="account_id" id="account_id"  value="{{$buyers_acknowledgment['buyers_acknowledgment_no']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">No. Perkiraan</a>
                        <input class="form-control input-bb" type="text" name="account_id" id="account_id"  value="{{$buyers_acknowledgment['account_code'].' - '. $buyers_acknowledgment['account_name']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal</a>
                        <input type ="text" class="form-control input-bb" name="buyers_acknowledgment_date" id="buyers_acknowledgment_date" value="{{$buyers_acknowledgment['buyers_acknowledgment_date']}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Alasan</a>
                        <div class="">
                            <textarea rows="3" type="text" class="form-control input-bb" name="buyers_acknowledgment_remark" id="buyers_acknowledgment_remark" onChange="function_elements_add(this.name, this.value);" readonly>{{$buyers_acknowledgment['buyers_acknowledgment_remark']}}</textarea>
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
                                <th width="10%" style='text-align:center'>Pelanggan</th>
                                <th width="5%" style='text-align:center'>No Sales Order</th>
                                <th width="2%" style='text-align:center'>Tanggal SO</th>
                                <th width="10%" style='text-align:center'>Barang</th>
                                <th width="2%" style='text-align:center'>Qty Kirim</th>
                                <th width="2%" style='text-align:center'>Qty Terima</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if(count($buyers_acknowledgment_item)==0){
                                    echo "<tr><th colspan='9' style='text-align  : center !important;'>Data Kosong</th></tr>";
                                } else {
                                    $no =1;
                                    foreach ($buyers_acknowledgment_item AS $key => $val){
                                        // $item = $BuyersAcknowledgment->getSalesOrderItem($val['sales_order_item_id']);
                                        echo"
                                            <tr>
                                                <td style='text-align  : center'>".$no."</td>
                                                <td style='text-align  : left !important;'>".$BuyersAcknowledgment->getCustomerNameSalesOrderId($val['sales_order_id'])."</td>
                                                <td style='text-align  : left !important;'>".$val['sales_order_no']."</td>
                                                <td style='text-align  : left !important;'>".$val['sales_order_date']."</td>
                                                <td style='text-align  : left !important;'>".$BuyersAcknowledgment->getInvItemTypeName($val['item_type_id'])."</td>
                                                <td style='text-align  : right !important;'>".$val['quantity']." 
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='sales_order_id_".$no."' id='sales_order_id_".$no."' value='".$val['sales_order_id']."'/>  
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='sales_order_item_id_".$no."' id='sales_order_item_id_".$no."' value='".$val['sales_order_item_id']."'/>  
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='customer_id_".$no."' id='customer_id_".$no."' value='".$val['customer_id']."'/>  
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='item_id_".$no."' id='item_id_".$no."' value='".$val['item_id']."'/>
                                                    <input class='form-control' style='text-align:right;'type='hidden' name='quantity_".$no."' id='quantity_".$no."' value='".$val['quantity']."'/>
                                                    </td>
                                                <td>".$val['quantity_received']."</td>";
                                                echo"
                                            </tr>
                                        ";
                                        $no++;
                                    }
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
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