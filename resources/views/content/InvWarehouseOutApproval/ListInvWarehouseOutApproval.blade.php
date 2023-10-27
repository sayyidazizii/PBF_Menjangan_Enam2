@inject('InvWarehouseOutApproval', 'App\Http\Controllers\InvWarehouseOutApprovalController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item active" aria-current="page">Daftar Pengeluaran Gudang</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Pengeluaran Gudang</b> <small>Mengelola Pengeluaran Gudang</small>
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
        {{-- <div class="form-actions float-right">
            <button onclick="location.href='{{ url('warehouse-out-approval/search') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Permintaan Pengeluaran Gudang Baru</button>
        </div> --}}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No.</th>
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
                        <td style='text-align:center'>{{$no}}.</td>
                        <td>{{$warehouse['warehouse_out_no']}}</td>
                        <td>{{date('d/m/Y', strtotime($warehouse['warehouse_out_date']))}}</td>
                        <td>{{$InvWarehouseOutApproval->getInvWarehouseName($warehouse['warehouse_id'])}}</td>
                        <td>{{$InvWarehouseOutApproval->getInvWarehouseOutTypeName($warehouse['warehouse_out_type_id'])}}</td>
                    <?php if($warehouse['warehouse_out_status']==0){ ?>
                        <td>Belum Disetujui</td>
                    <?php }else{ ?>
                        <td>Disetujui</td>
                    <?php } ?>
                        <td style='text-align:center'>
                        <?php if($warehouse['warehouse_out_status'] == 0){ ?>
                            <a type="button" class="btn btn-outline-success btn-sm" href="{{ url('/warehouse-out-approval/approve/'.$warehouse['warehouse_out_id']) }}">Approve</a>
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

@stop

@section('footer')
    
@stop

@section('css')
    
@stop

@section('js')
    
@stop