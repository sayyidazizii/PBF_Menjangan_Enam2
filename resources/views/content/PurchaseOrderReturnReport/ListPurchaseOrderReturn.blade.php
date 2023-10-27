@inject('PORR', 'App\Http\Controllers\PurchaseOrderReturnReportController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Purchase Order Return</li>
    </ol>
</nav>

@stop

@section('content')


@if(session('msg'))
<div class="alert alert-info" role="alert">
    {{session('msg')}}
</div>
@endif 
<div class="card border border-dark">
    <div class="card-header bg-dark clearfix">
        <h5 class="mb-0 float-left">
            Daftar Purchase Order Return
        </h5>
        <div class="form-actions float-right">
            {{-- <a  href="{{route('add-journal')}}" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Jurnal Umum Baru</a> --}}
        </div>
    </div>

<form  method="post" action="{{route('filter-purchase-order-return-report')}}" enctype="multipart/form-data">
@csrf
    <div class="card-body">
        <div class='row'>
            <div class = "col-md-4">
                <div class="form-group form-md-line-input">
                    <section class="control-label">Tanggal Mulai
                        <span class="required text-danger">
                            *
                        </span>
                    </section>
                    <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="start_date" id="start_date" onChange="function_elements_add(this.name, this.value);" value="{{$start_date}}" style="width: 15rem;"/>
                </div>
            </div>
            <div class = "col-md-4">
                <div class="form-group form-md-line-input">
                    <section class="control-label">Tanggal Akhir
                        <span class="required text-danger">
                            *
                        </span>
                    </section>
                    <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="end_date" id="end_date" onChange="function_elements_add(this.name, this.value);" value="{{$end_date}}" style="width: 15rem;"/>
                </div>
            </div>
            <div class="col-md-4">
                <div class="row form-group">
                    <a class="text-dark">Supplier</a>
                    <br/>
                    {!! Form::select('supplier_id',  $coreSupplier, null, ['class' => 'selection-search-clear select-form']) !!}
                </div>
            </div>	
        </div>
    </div>
    <div class="card-footer text-muted">
        <div class="form-actions float-right">
            <button type="reset" name="Reset" class="btn btn-sm btn-danger" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
            <button type="submit" name="Find" class="btn btn-sm btn-primary" title="Search Data"><i class="fa fa-search"></i> Cari</button>
        </div>
    </div>
</form>
    
    <div class="card-body">
        <table class="table table-striped table-bordered table-hover table-full-width">
            <thead>
                <tr style='text-align:center'>
                    <th width="5%">No</th>
                    <th width="15%">Purchase Order Return No</th>
                    <th width="15%">No Faktur Pajak</th>
                    <th width="15%">Nomor Batch</th>
                    <th width="8%">Qty</th>
                    <th width="8%">Harga</th>
                    <th width="8%">Discount</th>
                    <th width="11%">Jumlah</th>
                    <th width="11%">ED</th>
                </tr>
            </thead>
            <tbody>
                @php
                   $total = 0;
                   $no = 1;
                       if(empty($purchaseorderreturn)){
                    echo "
                        <tr>
                            <td colspan='6' align='center'>Data Kosong</td>
                        </tr>
                    ";
                }else{
                    foreach ($purchaseorderreturn as $key=>$val){	
                      
                        $amount = $val['quantity_return'] * $PORR->getCost($val['purchase_order_item_id']);
                        $jumlah = $amount - $PORR->getDiscountAmount($val['purchase_order_item_id']);
                        $total += $jumlah;
                        echo "
                        <tr>
                            <td>$no</td>
                            <td>".$val['purchase_order_return_no']."</td>
                            <td>".$val['faktur_tax_no']."</td>
                            <td>".$val['item_batch_number']."</td>
                            <td>".$val['quantity_return']."</td>
                            <td>".$PORR->getCost($val['purchase_order_item_id'])."</td>
                            <td>".$PORR->getDiscount($val['purchase_order_item_id'])."%</td>
                            <td>".$jumlah."</td>
                            <td>".$val['item_expired_date']."</td>
                        </tr>
                    ";
                    $no++;
                        
                    }
                    echo "
                        <tr>
                            <td colspan=\"7\" style=\"text-align: center;font-weight: bold\";>Total</td>
                            <td>".$total."</td>
                            <td></td>
                        </tr>
                    ";
                }
                @endphp

            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted">
        <div class="form-actions float-right">
            <a type="reset" name="Reset" class="btn btn-sm btn-info" href="{{route('print-purchase-order-return-report')}}"><i class="fa fa-eye"></i> Preview</a>
            <a hidden type="submit" name="Find" class="btn btn-sm btn-primary" href="{{route('export-purchase-order-return-report')}}"><i class="fa fa-download"></i> Export Data</a>
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