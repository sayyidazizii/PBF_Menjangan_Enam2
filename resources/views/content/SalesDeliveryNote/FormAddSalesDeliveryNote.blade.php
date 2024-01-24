@inject('SalesDeliveryNote', 'App\Http\Controllers\SalesDeliveryNoteController')
@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>


function elements_add(name, value){
        $.ajax({
            type: "POST",
            url : "{{route('elements-add-sales-delivery-order-note')}}",
            dataType: "html",
            data: {
                'name'      : name,
                'value'	    : value,
                '_token'    : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                console.log(return_data);
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }



	$(document).ready(function(){
        $("#expedition_id").select2("val", "0");


        var elements = {!! json_encode($salesdeliveryordernoteelements) !!};

        if(!elements || elements==''){
            elements = [];
        }

        if(!elements['warehouse_id']){
            $("#warehouse_id").select2("val", "0");
        }



        $("#expedition_id").select2("val", "0");
        $("#sales_delivery_note_cost").change(function(){
			var cost 	    = $("#sales_delivery_note_cost").val();
            if(isNaN(cost)){
                alert('Biaya Ekspedisi Bukan Nomor!');
                $("#sales_delivery_note_cost").val(0);
            }

		}); 


        
        
	});

    function addExpedition(){
        var expedition_code             = $("#expedition_code").val();
        var expedition_name             = $("#expedition_name").val();
        var expedition_address          = $("#expedition_address").val();
        var expedition_route            = $("#expedition_route").val();
        var expedition_city             = $("#expedition_city").val();
        var expedition_home_phone       = $("#expedition_home_phone").val();
        var expedition_mobile_phone1    = $("#expedition_mobile_phone1").val();
        var expedition_mobile_phone2    = $("#expedition_mobile_phone2").val();
        var expedition_fax_number       = $("#expedition_fax_number").val();
        var expedition_email            = $("#expedition_email").val();
        var expedition_person_in_charge = $("#expedition_person_in_charge").val();
        var expedition_status           = $("#expedition_status").val();
        // var expedition_receipt_no          = $("#expedition_receipt_no").val();
        var expedition_remark           = $("#expedition_remark").val();


        console.log(data);
        $.ajax({
            type: "POST",
            url : "{{route('add-expedition-sales-delivery-note')}}",
            dataType: "html",
            data: {
                'expedition_code'	            : expedition_code,
                'expedition_name'	            : expedition_name,
                'expedition_address'	        : expedition_address,
                'expedition_route'	            : expedition_route,
                'expedition_city'	            : expedition_city,
                'expedition_home_phone'	        : expedition_home_phone,
                'expedition_mobile_phone1'	    : expedition_mobile_phone1,
                'expedition_mobile_phone2'	    : expedition_mobile_phone2,
                'expedition_fax_number'	        : expedition_fax_number,
                'expedition_email'	            : expedition_email,
                'expedition_person_in_charge'	: expedition_person_in_charge,
                'expedition_status'	            : expedition_status,
                // 'expedition_receipt_no'         : expedition_receipt_no,
                'expedition_remark'	            : expedition_remark,
                '_token'                        : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                $('#expedition_id').html(return_data);
                $('#cancel_btn_expedition').click();
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
        <li class="breadcrumb-item"><a href="{{ url('sales-delivery-note') }}">Daftar Sales Delivery Note</a></li>
        <li class="breadcrumb-item"><a href="{{ url('sales-delivery-note/search-sales-delivery-order') }}">Daftar Sales Delivery Order</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Sales Delivery Note</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Form Tambah Sales Delivery Note</b>
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
            <button onclick="location.href='{{ url('sales-delivery-note/search-sales-delivery-order') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-add-sales-delivery-note')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row">
                <h5 class="form-section"><b>Detail Sales Delivery Order</b></h5>
            </div>
            <hr style="margin:0;">
            <br/>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Delivery Order No</a>
                        <input class="form-control input-bb" type="text" name="sales_delivery_order_no" id="sales_delivery_order_no" onChange="function_elements_add(this.name, this.value);" value="{{$salesdeliveryorder['sales_delivery_order_no']}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="sales_delivery_order_id" id="sales_delivery_order_id" onChange="function_elements_add(this.name, this.value);" value="{{$salesdeliveryorder['sales_delivery_order_id']}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="purchase_order_no" id="purchase_order_no" onChange="function_elements_add(this.name, this.value);" value="{{$SalesDeliveryNote->getPonum($salesdeliveryorder['sales_order_id'])}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Pelanggan</a>
                        <input class="form-control input-bb" type="text" name="customer_id_view" id="customer_id_view" onChange="function_elements_add(this.name, this.value);" value="{{$SalesDeliveryNote->getCustomerNameSalesOrderId($salesdeliveryorder['sales_order_id'])}}" readonly/>
                        <input class="form-control input-bb" type="text" name="customer_id" id="customer_id" onChange="function_elements_add(this.name, this.value);" hidden value="{{$SalesDeliveryNote->getCustomerId($salesdeliveryorder['sales_order_id'])}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Gudang</a>
                        <input class="form-control input-bb" type="text" name="warehouse_id" id="warehouse_id" onChange="function_elements_add(this.name, this.value);" value="{{$SalesDeliveryNote->getInvWarehouseName($salesdeliveryorder['warehouse_id'])}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Delivery Order</a>
                        <input class="form-control input-bb" type="text" name="sales_delivery_order_date" id="sales_delivery_order_date" onChange="function_elements_add(this.name, this.value);" value="{{date('d/m/Y', strtotime($salesdeliveryorder['sales_delivery_order_date']))}}" readonly/>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Deskripsi</a>
                        <div class="">
                            <textarea rows="3" type="text" class="form-control input-bb" name="sales_delivery_order_remark" id="sales_delivery_order_remark" onChange="function_elements_add(this.name, this.value);" readonly>{{$salesdeliveryorder['sales_delivery_order_remark']}}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <h5 class="form-section"><b>Form Tambah</b></h5>
            </div>
            <hr style="margin:0;">
            <br/>
            <div class="row form-group">
                <div class="col-md-5">
                    <div class="form-group">
                        <a class="text-dark">Nama Ekspedisi</a>
                        {!! Form::select('expedition_id',  $expedition, 0, ['class' => 'selection-search-clear select-form', 'id' => 'expedition_id']) !!}
                    </div>
                </div>
                <div class="col-md-1" style="margin-top: 0.3%">
                    <a class="text-dark"></a>
                    <a href='#addexpedition' data-toggle='modal' name="Find" class="btn btn-success add-btn btn-sm" title="Add Data">Tambah</a>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a hidden class="text-dark">Gudang</a>
                        <br/>
				<input hidden class="form-control form-control-inline input-medium date-picker input-date" type="text" name="warehouse_id' " id="warehouse_id' " onChange="function_elements_add(this.name, this.value);" value="8"/>
			</div>
                </div>
                {{-- <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Biaya Ekspedisi</a>
                        <input type ="number" class="form-control input-bb" name="sales_delivery_note_cost" id="sales_delivery_note_cost" onChange="" value="0"/>
                    </div>
                </div> --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Delivery Note<a class='red'></a> *</a></a>
                        <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="sales_delivery_note_date" id="sales_delivery_note_date" onChange="function_elements_add(this.name, this.value);" value=""/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Pengemudi</a>
                        <input class="form-control input-bb" type="text" name="driver_name" id="driver_name" onChange="function_elements_add(this.name, this.value);" value=""/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Plat Nomor Kendaraan</a>
                        <input class="form-control input-bb" type="text" name="fleet_police_number" id="fleet_police_number" onChange="function_elements_add(this.name, this.value);" value=""/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">No Resi</a>
                        <div class="">
                            <input class="form-control input-bb" type="text" name="expedition_receipt_no" id="expedition_receipt_no" onChange="function_elements_add(this.name, this.value);" value=""/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Remark</a>
                        <div class="">
                            <textarea rows="3" type="text" class="form-control input-bb" name="sales_delivery_note_remark" id="sales_delivery_note_remark" onChange="function_elements_add(this.name, this.value);"></textarea>
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
                                <th style='text-align:center'>Pelanggan</th>
                                <th style='text-align:center'>No. Sales Order</th>
                                <th style='text-align:center'>Tanggal SO</th>
                                <th style='text-align:center'>Barang</th>
                                <th style='text-align:center'>Qty</th>
                                <th style='text-align:center'>Qty Proses</th>
                                <th style='text-align:center'>Qty Kirim</th>
                                <th style='text-align:center'>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <label hidden style='text-align  : left !important;'>Ppn Out<input class='form-control' style='text-align:right;'type='text' name='ppn_out_amount' id='ppn_out_amount' value='{{ $SalesDeliveryNote->getPpnOut($salesdeliveryorder['sales_delivery_order_id']) }}' readonly/>
                            </label>

                            @if(count($salesdeliveryorderitem)==0)
                            <tr><th colspan='9' style='text-align  : center !important;'>Data Kosong</th></tr>
                        @else
                        @php
                            $no =1;
                        @endphp
                            @foreach ($salesdeliverynoteitem_view AS $key => $val)
                            @php    
                                $item = $SalesDeliveryNote->getSalesOrderItem($val['sales_order_item_id']);
                            @endphp
                                    <tr>
                                        <td style='text-align  : center'>{{$no}}.</td>
                                        <td style='text-align  : left !important;'>{{$SalesDeliveryNote->getCustomerName($item['customer_id'])}}</td>
                                        <td style='text-align  : left !important;'>{{$item['sales_order_no']}}</td>
                                        <td style='text-align  : left !important;'>{{date('d/m/Y', strtotime($item['sales_order_date']))}}</td>
                                        <td style='text-align  : left !important;'>{{$item['item_name']}}</td>
                                        <td style='text-align  : right !important;'>{{$item['quantity']}}</td>
                                        <td style='text-align  : right !important;'>{{$item['quantity_resulted']}}</td>
                                        <td style='text-align  : right !important;'>
                                            <input class='form-control' style='text-align:right;'type='text' name='quantity_delivered_{{$no}}' id='quantity_delivered_{{$no}}' value='{{$val['quantity']}}' readonly/>  
                                            
                                            <input class='form-control' style='text-align:right;'type='hidden' name='sales_order_id__{{$no}}' id='sales_order_id__{{$no}}' value='{{$val['sales_order_id']}}'/>  
                                            <input class='form-control' style='text-align:right;'type='hidden' name='sales_order_item_id__{{$no}}' id='sales_order_item_id__{{$no}}' value='{{$val['sales_order_item_id']}}'/> 
                                            <input class='form-control' style='text-align:right;'type='hidden' name='sales_delivery_order_id__{{$no}}' id='sales_delivery_order_id__{{$no}}' value='{{$val['sales_delivery_order_id']}}'/>  
                                            <input class='form-control' style='text-align:right;'type='hidden' name='sales_delivery_order_item_id__{{$no}}' id='sales_delivery_order_item_id__{{$no}}' value='{{$val['sales_delivery_order_item_id']}}'/>   
                                            <input class='form-control' style='text-align:right;'type='hidden' name='customer_id_{{$no}}' id='customer_id_{{$no}}' value='{{$val['customer_id']}}'/>  
                                            <input class='form-control' style='text-align:right;'type='hidden' name='item_id_{{$no}}' id='item_id_{{$no}}' value='{{$val['item_id']}}'/>
                                            <input class='form-control' style='text-align:right;'type='hidden' name='item_type_id_{{$no}}' id='item_type_id_{{$no}}' value='{{$val['item_type_id']}}'/>
                                            <input class='form-control' style='text-align:right;'type='hidden' name='item_unit_id_{{$no}}' id='item_unit_id_{{$no}}' value='{{$val['item_unit_id']}}'/>
                                            <input class='form-control' style='text-align:right;'type='hidden' name='quantity_{{$no}}' id='quantity_{{$no}}' value='{{$val['quantity']}}' readonly/>  
                                            <input class='form-control' style='text-align:right;'type='hidden' name='item_unit_price_{{ $no }}' id='item_unit_price_{{ $no }}' value='{{$SalesDeliveryNote->getItemUnitprice($val->sales_delivery_order_item_id)}}'/>
                                            <input class='form-control' style='text-align:right;'type='hidden' name='subtotal_price_{{ $no }}' id='subtotal_price_{{ $no }}' value='{{$val->subtotal_price}}'/>
                                            <input class='form-control' style='text-align:right;'type='hidden' name='discount_amount_item_{{ $no }}' id='discount_amount_item_{{ $no }}' value='{{$item['discount_amount_item']}}'/>
                                            <input class='form-control' style='text-align:right;'type='hidden' name='subtotal_after_discount_item_{{ $no }}' id='subtotal_after_discount_item_{{ $no }}' value='{{$item['subtotal_after_discount_item']}}'/>
                                            {{-- <input class='form-control' style='text-align:right;'type='' name='no' id='no_{{$no}}' value='{{$no}}' readonly/> --}}
                                            {{-- <p>{{ $SalesDeliveryNote->getdataItemStokNote($val['sales_delivery_order_item_id'])}}</p> --}}

                                        </td>
                                        <td style='text-align:center;'>
                                            <a href='{{url('/sales-delivery-note/add/detail-item-stock/'.$val['sales_delivery_order_id'].'/'.$val['sales_delivery_order_item_id'])}}' class="btn btn-outline-info btn-sm" title="Detail Stock">Detail Stock</a>
                                        </td>
                                    </tr>
                                @php
                                    $no++;
                                @endphp
                            @endforeach
                        @endif









                                @if(count($salesdeliveryorderitem)==0)
                                    <tr><th colspan='9' style='text-align  : center !important;'>Data Kosong</th></tr>
                                @else
                                    @php
                                        $no =1;
                                        // dd($salesdeliveryorderitem);
                                    @endphp
                                    @foreach ($salesdeliveryorderitem AS $key => $val)

                                    @php
                                            $item = $SalesDeliveryNote->getSalesOrderItem($val->sales_order_item_id);
                                        @endphp
                                            <tr hidden>
                                                <td style='text-align  : center'>{{$no}}</td>
                                                <td style='text-align  : left !important;'>{{$SalesDeliveryNote->getCustomerName($item['customer_id'])}}</td>
                                                <td style='text-align  : left !important;'>{{$item['sales_order_no']}}</td>
                                                <td style='text-align  : left !important;'>{{date('m/d/Y', strtotime($SalesDeliveryNote->getSalesOrderDate($item['sales_order_id'])))}}</td>
                                                <td style='text-align  : left !important;'>{{$item['item_name']}}</td>
                                                <td style='text-align  : right !important;'>{{$item['quantity']}}</td>
                                                <td style='text-align  : right !important;'>{{$item['quantity_resulted']}}</td>
                                                <td style='text-align  : right !important;'>
                                                    <input class='form-control' style='text-align:right;'type='text' name='quantity_delivered_{{$no}}' id='quantity_delivered_{{$no}}' value='{{$item['quantity']}}' readonly/>  

                                            
                                                </td>
                                                <td style='text-align:center;'>
                                                <a href='{{url ('/sales-delivery-note/add/detail-item-stock/'.$val->sales_delivery_order_id.'/'.$val->sales_delivery_order_item_id)}}' class='btn btn-outline-info btn-sm' title='Detail Stock'>Detail Stock</a>
                                            </td>
                                            </tr>
                                        @php
                                            $total_no = $no;
                                            $no++;

                                        @endphp

                                        
                                         <input class='form-control' style='text-align:right;'type='hidden' name='sales_delivery_order_id_{{$total_no}}' id='sales_delivery_order_id_{{$total_no}}' value='{{$val->sales_delivery_order_id}}'/>  
                                         <input class='form-control' style='text-align:right;'type='hidden' readonly name='sales_delivery_order_item_id_{{$total_no}}' id='sales_delivery_order_item_id_{{$total_no}}' value='{{$val->sales_delivery_order_item_id}}'/>
                                         <input class='form-control' style='text-align:right;'type='hidden' name='sales_delivery_order_item_stock_id_{{$total_no}}' id='sales_delivery_order_item_stock_id_{{$total_no}}' value='{{$val->sales_delivery_order_item_stock_id}}'/>    
                                         <input class='form-control' style='text-align:right;'type='hidden' name='sales_order_id_{{$total_no}}' id='sales_order_id_{{$total_no}}' value='{{$item['sales_order_id']}}'/>  
                                         <input class='form-control' style='text-align:right;'type='hidden' name='sales_order_item_id_{{$total_no}}' id='sales_order_item_id_{{$total_no}}' value='{{$item['sales_order_item_id']}}'/>  
                                         <input class='form-control' style='text-align:right;'type='hidden' name='customer_id_{{$total_no}}' id='customer_id_{{$total_no}}' value='{{$item['customer_id']}}'/>  
                                         <input class='form-control' style='text-align:right;'type='hidden' name='item_id_{{$total_no}}' id='item_id_{{$total_no}}' value='{{$item['item_id']}}'/>
                                         <input class='form-control' style='text-align:right;'type='hidden' name='item_type_id_{{$total_no}}' id='item_type_id_{{$total_no}}' value='{{$item['item_type_id']}}'/>
                                         <input class='form-control' style='text-align:right;'type='hidden' name='item_unit_id_{{$total_no}}' id='item_unit_id_{{$total_no}}' value='{{$item['item_unit_id']}}'/>
                                         <input class='form-control' style='text-align:right;'type='hidden' name='item_batch_number_{{$total_no}}' id='item_batch_number_{{$total_no}}' value='{{$SalesDeliveryNote->getItemBatchNumber($val->item_stock_id)}}'/>
                                        <input class='form-control' style='text-align:right;'type='hidden' name='quantity_{{$total_no}}' id='quantity_{{$total_no}}' value='{{ $SalesDeliveryNote->getdataItemStok($val['sales_delivery_order_item_id']) }}'/>
                                        <input class='form-control' style='text-align:right;'type='hidden' name='item_stock_id_{{$total_no}}' id='item_stock_id_{{$total_no}}' value='{{$val->item_stock_id}}'/>
                                        {{-- <input class='form-control' style='text-align:right;'type='hidden' name='item_type_id_{{$total_no}}' id='item_type_id_{{$total_no}}' value='{{$val->item_type_id}}'/> --}}
                                        {{-- <input class='form-control' style='text-align:right;'type='hidden' name='item_unit_id_{{$total_no}}' id='item_unit_id_{{$total_no}}' value='{{$val->item_unit_id}}'/> --}}
                                        <input class='form-control' style='text-align:right;'type='hidden' name='total_no' id='total_no' value='{{$total_no}}'/>
                                    @endforeach
                                @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <a href="{{route('add-sales-delivery-note', ['sales_delivery_order_id' => $sales_delivery_order_id])}}" name='Reset' class='btn btn-danger btn-sm' onClick='javascript:return confirm(\"apakah yakin ingin dihapus ?\")'><i class="fa fa-times"></i> Reset</a>
                <button type="submit" name="Save" class="btn btn-primary btn-sm" title="Save"><i class="fa fa-check"></i> Simpan</button>
            </div>
        </div>
    </div>
    </form>
<br/>


<div class="modal fade bs-modal-lg" id="addexpedition" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"  style='text-align:left !important'>
                <h4>Form Tambah Ekspedisi</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Kode Ekspedisi</a>
                            <input class="form-control input-bb" type="text" name="expedition_code" id="expedition_code" value=""/>
                        </div>
                    </div>
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Nama Ekspedisi</a>
                            <input class="form-control input-bb" type="text" name="expedition_name" id="expedition_name" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Rute</a>
                            <input class="form-control input-bb" type="text" name="expedition_route" id="expedition_route" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-12">	
                        <div class="form-group">	
                            <a class="text-dark">Alamat</a>
                            <input class="form-control input-bb" type="text" name="expedition_address" id="expedition_address" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Nama Kota<a class='red'> *</a></a>
                            {!! Form::select('expedition_city',  $city, 0, ['class' => 'selection-search-clear select-form', 'id' => 'expedition_city']) !!}
                        </div>
                    </div>
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Nomor Telepon</a>
                            <input class="form-control input-bb" type="text" name="expedition_home_phone" id="expedition_home_phone" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Nomor handphone 1</a>
                            <input class="form-control input-bb" type="text" name="expedition_mobile_phone1" id="expedition_mobile_phone1" value=""/>
                        </div>
                    </div>	
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Nomor handphone 2</a>
                            <input class="form-control input-bb" type="text" name="expedition_mobile_phone2" id="expedition_mobile_phone2" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Nomor Fax</a>
                            <input class="form-control input-bb" type="text" name="expedition_fax_number" id="expedition_fax_number" value=""/>
                        </div>
                    </div>	
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Email</a>
                            <input class="form-control input-bb" type="text" name="expedition_email" id="expedition_email" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Person in Charge</a>
                            <input class="form-control input-bb" type="text" name="expedition_person_in_charge" id="expedition_person_in_charge" value=""/>
                        </div>
                    </div>	
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Status<a class='red'> *</a></a>
                            {!! Form::select('expedition_status',  $status, 0, ['class' => 'selection-search-clear select-form', 'id' => 'expedition_status']) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">		
                        <div class="form-group">		
                            <a class="text-dark">Keterangan</a>
                            <input class="form-control input-bb" type="text" name="expedition_remark" id="expedition_remark" value=""/>
                        </div>
                    </div>	
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal" id='cancel_btn_expedition'>Batal</button>
                <a class="btn btn-primary" onClick="addExpedition()">Simpan</a>
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

@section('js')
    
@stop