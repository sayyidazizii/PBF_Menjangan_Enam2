@inject('PurchaseOrder', 'App\Http\Controllers\PurchaseOrderController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Purchase Order</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Purchase Order</b> <small>Mengelola Purchase Order</small>
</h3>
<br/>
<div id="accordion">
    <form  method="post" action="{{route('filter-purchase-order')}}" enctype="multipart/form-data">
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
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="start_date" id="date" onChange="function_elements_add(this.name, this.value);" value="{{$start_date}}" style="width: 15rem;"/>
                        </div>
                    </div>

                    <div class = "col-md-4">
                        <div class="form-group form-md-line-input">
                            <section class="control-label">Tanggal Akhir
                                <span class="required text-danger">
                                    *
                                </span>
                            </section>
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="end_date" id="date" onChange="function_elements_add(this.name, this.value);" value="{{$end_date}}" style="width: 15rem;"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-muted">
                <div class="form-actions float-right">
                    <a href="{{route('filter-reset-purchase-order')}}" type="reset" name="Reset" class="btn btn-danger btn-sm" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</a>
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
        <div class="form-actions float-right">
            <button onclick="location.href='{{ url('purchase-order/add') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Purchase Order Baru</button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No.</th>
                        <th width="20%" style='text-align:center'>Nama Gudang</th>
                        <th width="20%" style='text-align:center'>Nama Pemasok</th>
                        <th width="20%" style='text-align:center'>No PO</th>
                        <th width="10%" style='text-align:center'>Tanggal PO</th>
                        {{-- <th width="10%" style='text-align:center'>PPN Masuk</th> --}}
                        <th width="15%" style='text-align:center'>Status</th>
                        <th width="15%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($purchaseorder as $item)
                    <tr>
                        <td style='text-align:center'>{{$no}}.</td>
                        <td>{{$PurchaseOrder->getInvWarehouseName($item['warehouse_id'])}}</td>
                        <td>{{$PurchaseOrder->getCoreSupplierName($item['supplier_id'])}}</td>
                        <td>{{$item['purchase_order_no']}}</td>
                        <td>{{date('d/m/Y', strtotime($item['purchase_order_date']))}}</td>
                        {{-- <td>{{$item['ppn_in_amount']}}</td> --}}
                        <?php 
                        if($item['approved']==2){
                            ?>
                            <td>Tidak Disetujui</td>
                        <?php 
                        }else if($item['purchase_order_status']==0){
                            ?>
                            <td>Dalam Proses</td>
                        <?php
                        }else if($item['purchase_order_status']==1) {
                            ?>
                            <td>Sebagian diterima</td>
                            <?php
                        }else if($item['purchase_order_status']==2) {
                            ?>
                            <td>Sudah diterima</td>
                            <?php
                        }else if($item['purchase_order_status']==3) {
                            ?>
                            <td>Di return</td>
                            <?php
                        }
                        ?>
                        <td class="" style='text-align:center'>
                            <?php if($item['approved']==1){ ?>
                            <a type="button" class="btn btn-outline-secondary btn-sm" target="_blank" href="{{ url('/purchase-order/cetak/'.$item['purchase_order_id']) }}">Cetak</a>
                            <?php }?>
                            <a type="button" class="btn btn-outline-primary btn-sm" href="{{ url('/purchase-order/detail/'.$item['purchase_order_id']) }}">Detail</a>
                            <?php if($item['approved']==0){ ?>
                                <a type="button" class="btn btn-outline-danger btn-sm" href="{{ url('/purchase-order/delete/'.$item['purchase_order_id']) }}">Hapus</a>
                            <?php }else if($item['approved']==1){ ?>
                                <a type="button" class="btn btn-outline-success btn-sm">Approved</a>
                            <?php }else if($item['approved']==2){ ?>
                                <a type="button" class="btn btn-outline-danger btn-sm">Disapproved</a>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php $no++; ?>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
  </div>
</div>
<br>
<br>

@include('footer')

@stop

@section('css')
    
@stop

@section('js')
    
@stop