@inject('Kwitansi', 'App\Http\Controllers\KwitansiController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />
@section('js')
<script>
    $(function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    });
       function multiple($id){
        var multiple = $("#input_multiple_"+$id).val();


        console.log(multiple);
        $.ajax({
            type: "GET",
            url: "/PBF_Menjangan_Enam/print-kwitansi/cetak-multiple/" + multiple ,
            dataType: "html",
            data: {
                'sales_kwitansi_id'        : multiple,
                '_token'                   : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                location.href= "/PBF_Menjangan_Enam/print-kwitansi/cetak-multiple/" + multiple;
                // location.reload();
                console.log(data);
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }


    function single($id){
        var single = $("#input_single_"+$id).val();


        console.log(single);
        $.ajax({
            type: "GET",
            url: "/_Menjangan_Enam/print-kwitansi/cetak-single/" + single ,
            dataType: "html",
            data: {
                'sales_kwitansi_id'        : single,
                '_token'                   : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                location.href= "/PBF_Menjangan_Enam/print-kwitansi/cetak-single/" + single;
                // location.reload();
                console.log(data);
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }

    function confirmDelete(event, url) {
        event.preventDefault(); // Prevent the default link behavior
        if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
            window.location.href = url; // If confirmed, proceed to the URL
        }
    }

</script>
@stop
@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
      <li class="breadcrumb-item active" aria-current="page">Daftar Kwitansi</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Kwitansi</b> <small>Mengelola Kwitansi </small>
</h3>
<br/>

<div id="accordion">
    <form  method="post" action="{{route('filter-print-kwitansi')}}" enctype="multipart/form-data">
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
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="start_date" id="start_date" onChange="function_elements_add(this.name, this.value);" value="{{$start_date}}" style="width: 15rem;"/>
                        </div>
                    </div>

                    <div class = "col-md-6">
                        <div class="form-group form-md-line-input">
                            <section class="control-label">Tanggal Akhir
                                <span class="required text-danger">
                                    *
                                </span>
                            </section>
                            <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="end_date" id="end_date" onChange="function_elements_add(this.name, this.value);" value="{{$end_date}}" style="width: 15rem;"/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-muted">
                <div class="form-actions float-right">
                    <button type="reset" name="Reset" class="btn btn-danger btn-sm" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
                    <button type="submit" name="Find" class="btn btn-primary btn-sm" title="Search Data"><i class="fa fa-search"></i> Cari</button>
                    {{-- <a href="{{ url('sales-delivery-note/export') }}"name="Find" class="btn btn-sm btn-info" title="Export Excel"><i class="fa fa-print"></i>Export</a> --}}
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
            <button onclick="location.href='{{ url('/print-kwitansi/add') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-plus"></i> Buat Kwitansi</button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No</th>
                        <th width="10%" style='text-align:center'>Customer</th>
                        <th width="10%" style='text-align:center'>Tanggal Kwitansi</th>
                        <th width="10%" style='text-align:center'>Periode</th>
                        <th width="10%" style='text-align:center'>No. Kwitansi</th>
                        <th width="10%" style='text-align:center'>No. Tagihan</th>
                        <th width="12%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                     $no = 1;                    
                    ?>
                    @foreach($saleskwitansi as $item)
                    <tr>
                        <td style='text-align:center'>{{$no}}</td>
                        <td>{{ $Kwitansi->getCustomerName($item['customer_id'])}}</td>
                        <td>{{$item['sales_kwitansi_date']}}</td>
                        <td>{{$item['start_date'] }} S/D {{$item['end_date'] }}</td>
                        <td>{{ $item['sales_kwitansi_no']}}</td>
                        <td>{{ $item['sales_tagihan_no']}}</td>
                        <td style='text-align:center'>
                          {{-- <a href="/print-kwitansi/{{ $item['sales_kwitansi_id'] }}" class="btn btn-outline-primary">detail</a> --}}
                          <a href=" {{ url('/print-kwitansi/cetak-pengantar/'.$item['sales_kwitansi_id']) }}" id="button_multiple" type="button" title="Cetak Pengantar" data-bs-toggle="tooltip" data-bs-placement="top" class="btn btn-outline-secondary"><i class="fa fa-file"></i></a>
                          <a href=" {{ url('/print-kwitansi/cetak-multiple/'.$item['sales_kwitansi_id']) }}" id="button_multiple" type="button" title="Cetak Multi" data-bs-toggle="tooltip" data-bs-placement="top" class="btn btn-outline-warning"><i class="fa fa-file-invoice"></i></a>
                          <a href=" {{ url('/print-kwitansi/cetak-single/'.$item['sales_kwitansi_id']) }}" id="button_multiple" type="button" title="Cetak Single" data-bs-toggle="tooltip" data-bs-placement="top" class="btn btn-outline-info"><i class="fa fa-file-invoice"></i></a>
                          <a href=" {{ url('/print-kwitansi/delete/'.$item['sales_kwitansi_id']) }}" id="button_multiple" type="button" class="btn btn-outline-danger" title="Hapus" data-bs-toggle="tooltip" data-bs-placement="top"  onclick="confirmDelete(event, '{{ url('/print-kwitansi/delete/'.$item['sales_kwitansi_id']) }}')"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php
                         $no++; 
                     ?>
                    @endforeach
                </tbody>
            </table>
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