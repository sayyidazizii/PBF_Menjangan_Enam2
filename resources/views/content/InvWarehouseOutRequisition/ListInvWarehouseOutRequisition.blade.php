@inject('InvWarehouseOutRequisition', 'App\Http\Controllers\InvWarehouseOutRequisitionController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Permintaan Pengeluaran Gudang</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Permintaan Pengeluaran Gudang</b> <small>Mengelola Permintaan Pengeluaran Gudang</small>
</h3>
<br/>
<div id="accordion">
    <form  method="post" action="{{route('filter-warehouse-out-requisition')}}" enctype="multipart/form-data">
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
                    <div class = "col-md-6">
                        <div class="form-group form-md-line-input">
                            <section class="control-label">Tanggal Mulai
                                <span class="required text-danger">
                                    *
                                </span>
                            </section>
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="start_date" id="date" onChange="function_elements_add(this.name, this.value);" value="{{$start_date}}" style="width: 15rem;"/>
                        </div>
                    </div>

                    <div class = "col-md-6">
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
                    <a href="{{route('filter-reset-warehouse-out-requisition')}}" type="reset" name="Reset" class="btn btn-danger btn-sm"><i class="fa fa-times"></i> Batal</a>
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
            <button onclick="location.href='{{ url('warehouse-out-requisition/add') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Permintaan Pengeluaran Gudang</button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No</th>
                        <th width="13%" style='text-align:center'>Nomor Pengeluaran</th>
                        <th width="10%" style='text-align:center'>Tanggal</th>
                        <th width="12%" style='text-align:center'>Gudang</th>
                        <th width="20%" style='text-align:center'>Tipe</th>
                        <th width="13%" style='text-align:center'>Status</th>
                        <th width="10%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    @foreach($invwarehouseout as $warehouse)
                    <tr>
                        <td style='text-align:center'>{{$no}}</td>
                        <td>{{$warehouse['warehouse_out_no']}}</td>
                        <td>{{date('d/m/Y', strtotime($warehouse['warehouse_out_date']))}}</td>
                        <td>{{$InvWarehouseOutRequisition->getInvWarehouseName($warehouse['warehouse_id'])}}</td>
                        <td>{{$InvWarehouseOutRequisition->getInvWarehouseOutTypeName($warehouse['warehouse_out_type_id'])}}</td>
                    <?php if($warehouse['warehouse_out_status']==0){ ?>
                        <td>Belum Disetujui</td>
                    <?php }else if($warehouse['warehouse_out_status']==1){ ?>
                        <td>Disetujui</td>
                    <?php }else if($warehouse['warehouse_out_status']==2){ ?>
                        <td>Tidak Disetujui</td>
                    <?php } ?>
                        <td style='text-align:center'>
                            <a type="button" class="btn btn-outline-warning btn-sm" href="{{ url('/warehouse-out-requisition/detail/'.$warehouse['warehouse_out_id']) }}">Detail</a>
                        <?php if($warehouse['warehouse_out_status'] == 0){ ?>
                            {{-- <a type="button" class="btn btn-outline-warning btn-sm" href="{{ url('/warehouse-out-requisition/edit/'.$warehouse['warehouse_out_id']) }}">Edit</a> --}}
                            <a type="button" class="btn btn-outline-danger btn-sm" href="{{ url('/warehouse-out-requisition/delete-warehouse-out-requisition/'.$warehouse['warehouse_out_id']) }}">Hapus</a>
                        <?php }else if($warehouse['warehouse_out_status'] == 1){ ?>
                            <a type="button" class="btn btn-outline-success btn-sm">Approved</a>
                        <?php }else if($warehouse['warehouse_out_status'] == 2){ ?>
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