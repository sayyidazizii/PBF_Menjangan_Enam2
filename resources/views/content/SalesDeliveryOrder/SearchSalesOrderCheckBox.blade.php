@inject('SalesDeliveryOrder', 'App\Http\Controllers\SalesDeliveryOrderController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>
function checkboxSalesOrderChange (sales_order_item_id) {
    $.ajax({
        type: "POST",
        url : "{{route('add-checkbox-sales-delivery-order')}}",
        dataType: "html",
        data: {
            'sales_order_item_id'   : sales_order_item_id,
            '_token'                : '{{csrf_token()}}',
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
</script>
@stop

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
      <li class="breadcrumb-item"><a href="{{ url('sales-delivery-order') }}">Daftar Sales Delivery Order</a></li>
      <li class="breadcrumb-item active" aria-current="page">Daftar Sales Order</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Sales Order</b>
</h3>
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
    <div class="float-right">
        <button onclick="location.href='{{ url('sales-delivery-order') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
    </div>
  </div>

  <form method="post" action="/sales-delivery-order/add" enctype="multipart/form-data">
    @csrf
    <div class="card-body">
        <div class="table-responsive">
            <table style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No</th>
                        <th width="13%" style='text-align:center'>Pelanggan</th>
                        <th width="15%" style='text-align:center'>No. Sales Order</th>
                        <th width="10%" style='text-align:center'>Tanggal SO</th>
                        <th width="15%" style='text-align:center'>Barang</th>
                        <th width="10%" style='text-align:center'>Qty</th>
                        <th width="8%" style='text-align:center'>Unit</th>
                        <th width="12%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($salesorderitem as $item)
                    <tr>
                        <td style='text-align:center'>{{$no}}</td>
                        <td>{{$SalesDeliveryOrder->getCustomerName($item['customer_id'])}}</td>
                        <td>{{$item['sales_order_no']}}</td>
                        <td>{{$item['sales_order_date']}}</td>
                        <td>{{$SalesDeliveryOrder->getItemName($item['item_id'])}}</td>
                        <td style='text-align:right'>{{$item['quantity_resulted']}}</td>
                        <td>{{$SalesDeliveryOrder->getInvItemUnitName($item['item_unit_id'])}}</td>
                        <td style='text-align:center'>
                            {{-- <a type="button" class="btn btn-outline-primary btn-sm" href="{{ url('/sales-delivery-order/add/'.$item['sales_delivery_order_item_id']) }}"><i class="fa fa-plus"></i></a> --}}
                            
                            <input type='checkbox' class='checkboxes' name='".$no."_checkbox' id='".$no."_checkbox' value='1'  OnClick='checkboxSalesOrderChange({{$item['sales_order_item_id']}})';/>
                        </td>
                    </tr>
                    <?php $no++; ?>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
        
    <div class="card-footer text-muted">
        <div class="form-actions float-right">
            <button type="submit" name="Save" class="btn btn-primary" title="Save">Tambah</button>
        </div>
    </div>
  </div>
</div>
</form>

@stop

@section('footer')
    
@stop

@section('css')
    
@stop