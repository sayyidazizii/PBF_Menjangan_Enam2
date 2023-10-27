@inject('InvWarehouseOutRequisition', 'App\Http\Controllers\InvWarehouseOutRequisitionController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>
</script>
@stop

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('warehouse-out-requisition') }}">Daftar Permintaan Pengeluaran Gudang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail Permintaan Pengeluaran Gudang</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Form Detail Permintaan Pengeluaran Gudang</b>
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
            <button onclick="location.href='{{ url('warehouse-out-requisition') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="get" action="{{route('process-delete-warehouse-out-requisition', ['id' => $warehouseout['warehouse_out_id']])}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Gudang<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="warehouse_id" id="warehouse_id" value="{{$InvWarehouseOutRequisition->getInvWarehouseName($warehouseout['warehouse_id'])}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tipe Pengeluaran Gudang<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="warehouse_out_type_id" id="warehouse_out_type_id" value="{{$InvWarehouseOutRequisition->getInvWarehouseOutTypeName($warehouseout['warehouse_out_type_id'])}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group form-md-line-input">
                        <section class="control-label">Tanggal
                            <span class="required text-danger">
                                *
                            </span>
                        </section>
                        <input type ="text" class="form-control input-bb" name="warehouse_out_requisition_date" id="date" onChange="elements_add(this.name, this.value);" value="{{$warehouseout['warehouse_out_date']}}" style="width: 15rem;" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Keterangan</a>
                        <textarea rows="3" type="text" class="form-control input-bb" name="warehouse_out_remark" onChange="elements_add(this.name, this.value);" id="warehouse_out_remark" readonly>{{$warehouseout['warehouse_out_remark']}}</textarea>
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
                                <th style='text-align:center'>Nama Barang</th>
                                <th style='text-align:center'>Jumlah</th>
                                <th style='text-align:center'>Satuan Barang</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                if(count($warehouseoutitem)==0){
                                    echo "<tr><th colspan='6' style='text-align  : center !important;'>Data Kosong</th></tr>";
                                } else {
                                    $no =1;
                                    foreach ($warehouseoutitem AS $key => $val){
                                        echo"
                                            <tr>
                                                <td style='text-align  : center'>".$no.".</td>
                                                <td style='text-align  : left !important;'>".$InvWarehouseOutRequisition->getItemName($val['item_stock_id'])."</td>
                                                <td style='text-align  : right !important;'>".$val['quantity']."</td>
                                                <td style='text-align  : left !important;'>".$InvWarehouseOutRequisition->getItemUnitName($val['item_unit_id'])."</td>";
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

@include('footer')

@stop

@section('css')
    
@stop