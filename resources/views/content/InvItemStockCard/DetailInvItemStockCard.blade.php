@inject('InvItemStockCard', 'App\Http\Controllers\InvItemStockCardController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('item-stock-card') }}">Kartu Stock</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail Kartu Stock</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Detail Kartu Stock</b> <small>Mengelola Kartu Stock</small>
</h3>
<br/>
<div id="accordion">
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
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="start_date" id="start_date" onChange="function_elements_add(this.name, this.value);" value="" style="width: 15rem;"/>
                        </div>
                    </div>

                    <div class = "col-md-4">
                        <div class="form-group form-md-line-input">
                            <section class="control-label">Tanggal Akhir
                                <span class="required text-danger">
                                    *
                                </span>
                            </section>
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="end_date" id="end_date" onChange="function_elements_add(this.name, this.value);" value="" style="width: 15rem;"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-muted">
                <div class="form-actions float-right">
                    <a href="{{ route('filter-reset-item-stock-card') }}"type="button" name="Reset" class="btn btn-danger btn-sm"><i class="fa fa-times"></i> Batal</a>
                    <button type="submit" name="Find" class="btn btn-primary btn-sm" title="Search Data"><i class="fa fa-search"></i> Cari</button>
                </div>
            </div>
        </div>
    </div>
</div>
<br>
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
            <button onclick="location.href='{{ url('goods-received-note/search-purchase-order') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Tambah Kartu Stock Baru</button>
        </div> --}}
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No</th>
                        <th width="8%" style='text-align:center'>Batch Number</th>
                        <th width="10%" style='text-align:center'>Kategori</th>
                        <th width="10%" style='text-align:center'>Nama Barang</th>
                        <th width="8%" style='text-align:center'>Unit</th>
                        <th width="8%" style='text-align:center'>Stock Awal</th>
                        <th width="8%" style='text-align:center'>Stock Masuk</th>
                        <th width="8%" style='text-align:center'>Stock Keluar</th>
                        <th width="8%" style='text-align:center'>Stock Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $no = 1; 
                        $opening_balance = 0;
                        $last_balance = 0;
                        $stock_in = 0;
                        $stock_out = 0;
                    ?>
                    @foreach($itemstockcard as $item)
                    <tr>
                        <td style='text-align:center'>{{$no}}</td>
                        <td>{{$item['item_batch_number']}}</td>
                        <td>{{$InvItemStockCard->getItemCategoryName($item['item_category_id'])}}</td>
                        <td>{{$InvItemStockCard->getItemTypeName($item['item_type_id'])}}</td>
                        <td>{{$InvItemStockCard->getItemUnitName($item['item_unit_id'])}}</td>
                        <td style='text-align:right'>{{$item['opening_balance']}}</td>
                        <td style='text-align:right'>{{$item['item_stock_card_in']}}</td>
                        <td style='text-align:right'>{{$item['item_stock_card_out']}}</td>
                        <td style='text-align:right'>{{$item['last_balance']}}</td>
                    </tr>
                    <?php 
                        $no++; 
                        $stock_in++;
                        $stock_out++;
                    ?>
                    @endforeach
                </tbody>
            </table>
            <div style='text-align:right'>
                <a href="" type="button" class="mt-2 btn btn-secondary btn-sm"><i class="fa fa-print"></i> Export</a>
            </div>
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