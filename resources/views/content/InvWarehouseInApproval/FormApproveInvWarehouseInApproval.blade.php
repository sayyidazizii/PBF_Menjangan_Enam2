@inject('InvWarehouseInApproval', 'App\Http\Controllers\InvWarehouseInApprovalController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>
    function disapprove(){
        var warehouse_in_id 	= $("#warehouse_in_id").val();
        $.ajax({
            type: "POST",
            url : "{{route('process-disapprove-warehouse-in-approval')}}",
            dataType: "html",
            data: {
                'warehouse_in_id'	: warehouse_in_id,
                '_token'            : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                window.location.href = "{{route('warehouse-in-approval')}}"
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
        <li class="breadcrumb-item"><a href="{{ url('warehouse-in-requisition') }}">Daftar Permintaan Pengeluaran Gudang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Persetujuan Permintaan Pengeluaran Gudang</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    Form Persetujuan Permintaan Pengeluaran Gudang
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
            Form Persetujuan
        </h5>
        <div class="float-right">
            <button onclick="location.href='{{ url('warehouse-in-approval') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="get" action="{{route('process-approve-warehouse-in-approval', ['id' => $warehousein['warehouse_in_id']])}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Gudang<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="warehouse_id" id="warehouse_id" value="{{$InvWarehouseInApproval->getInvWarehouseName($warehousein['warehouse_id'])}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="warehouse_in_id" id="warehouse_in_id" value="{{$warehousein['warehouse_in_id']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tipe Pengeluaran Gudang<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="warehouse_in_type_id" id="warehouse_in_type_id" value="{{$InvWarehouseInApproval->getInvWarehouseInTypeName($warehousein['warehouse_in_type_id'])}}" readonly/>
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
                        <input type ="text" class="form-control input-bb" name="warehouse_in_requisition_date" id="date" onChange="elements_add(this.name, this.value);" value="{{date('m/d/Y', strtotimr($warehousein['warehouse_in_date']))}}" style="width: 15rem;" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12">
                    <div class="form-group">
                        <a class="text-dark">Keterangan</a>
                        <textarea rows="3" type="text" class="form-control input-bb" name="warehouse_in_remark" onChange="elements_add(this.name, this.value);" id="warehouse_in_remark" readonly>{{$warehousein['warehouse_in_remark']}}</textarea>
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
                                if(count($warehouseinitem)==0){
                                    echo "<tr><th colspan='6' style='text-align  : center !important;'>Data Kosong</th></tr>";
                                } else {
                                    $no =1;
                                    foreach ($warehouseinitem AS $key => $val){
                                        echo"
                                            <tr>
                                                <td style='text-align  : center'>".$no."</td>
                                                <td style='text-align  : left !important;'>".$InvWarehouseInApproval->getItemName($val['item_stock_id'])."</td>
                                                <td style='text-align  : right !important;'>".$val['quantity']."</td>
                                                <td style='text-align  : left !important;'>".$InvWarehouseInApproval->getItemUnitName($val['item_unit_id'])."</td>";
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
        
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <a name="disapprove" class="btn btn-danger btn-sm" title="disapprove" onclick="disapprove()">Disapprove</a>
                <button type="submit" name="approve" class="btn btn-success btn-sm" title="approve">Approve</button>
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