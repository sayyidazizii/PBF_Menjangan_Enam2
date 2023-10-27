@inject('InvItemStockAdjustment','App\Http\Controllers\InvItemStockAdjustmentController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
      <li class="breadcrumb-item active" aria-current="page">Daftar Penyesuaian Stok </li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Penyesuaian Stok </b> <small>Kelola Penyesuaian Stok  </small>
</h3>
<br/>
<div id="accordion">
    <form  method="post" action="{{ route('filter-list-stock-adjustment') }}" enctype="multipart/form-data">
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
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="start_date" id="start_date" value="{{ $start_date }}" style="width: 15rem;"/>
                        </div>
                    </div>

                    <div class = "col-md-6">
                        <div class="form-group form-md-line-input">
                            <section class="control-label">Tanggal Akhir
                                <span class="required text-danger">
                                    *
                                </span>
                            </section>
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="end_date" id="end_date" value="{{ $end_date }}" style="width: 15rem;"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-muted">
                <div class="form-actions float-right">
                    <a href="{{ route('filter-reset-stock-adjustment') }}" type="reset" name="Reset" class="btn btn-danger"><i class="fa fa-times"></i> Batal</a>
                    <button type="submit" name="Find" class="btn btn-primary" title="Search Data"><i class="fa fa-search"></i> Cari</button>
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
        <button onclick="location.href='{{ url('/item-stock-adjustment/add') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Penyesuaian Stok</button>
    </div>
  </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th style="text-align: center; width: 5%">No </th>
                        <th style="text-align: center; width: 10%">Tanggal </th>
                        <th style="text-align: center; width: 15%">Nama Gudang</th>
                        <th style="text-align: center; width: 20%">Nama Barang</th>
                        <th style="text-align: center; width: 5%">Satuan</th>
                        <th style="text-align: center; width: 10%">Stok Awal</th>		
                        <th style="text-align: center; width: 10%">Stok Penyesuaian</th>		
                        <th style="text-align: center; width: 25%">Stok Penyesuaian</th>		
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                  @foreach ($data as $val)
                      <tr>
                        <td>{{ $no++ }}</td>
                        <td>{{ $val['stock_adjustment_date'] }}</td>
                        <td>{{ $InvItemStockAdjustment->getWarehouseName($val['warehouse_id']) }}</td>
                        <td>{{ $InvItemStockAdjustment->getItemNameStock($val['item_stock_id']) }}</td>
                        <td>{{ $InvItemStockAdjustment->getItemUnitName($val['item_unit_id']) }}</td>
                        <td style="text-align: right;">{{ $val['item_first_amount'] }}</td>
                        <td style="text-align: right;">{{ $val['item_last_amount'] }}</td>
                        <td>{{ $val['item_adjustment_remark'] }}</td>
                      </tr>
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