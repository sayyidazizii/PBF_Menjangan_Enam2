@inject('Kwitansi', 'App\Http\Controllers\KwitansiController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />
@section('js')
<script>

var salesinvoice = {!! json_encode($salesinvoice) !!};

    function check_all(){
        $(':checkbox').each(function() {
            this.checked = true;                        
        });
    }
    // function alert(){
    //     // $(':checkbox').each(function() {
    //     //     if(this.checked = false){
    //             confirm("Press a button!\nEither OK or Cancel.");

    //     //     };                        
    //     // });
    // }

    $(document).ready(function() {

       
    $(':checkbox').attr('checked', true);
        // $("checkbox").val(1);
});

function handleChange($no) {

 
    console.log($no);
    $("#checkbox_"+$no).val("0");

    $('#checkbox_view_'+$no).change(function() {
        if($(this).is(":checked")) {
    
            $("#checkbox_"+$no).val("1");  
        }
        // $("#checkbox_"+$no).val("1");  
        
        if(!$(this).is(":checked")) {
           
            $("#checkbox_"+$no).val("0");  
            // confirm("Press a button!\nEither OK or Cancel."); 
        }
        

    });
}

</script>
@stop

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
      <li class="breadcrumb-item active" aria-current="page">Daftar Invoice</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Invoice</b> <small>Mengelola Invoice </small>
</h3>
<br/>

<div id="accordion">
    <form  method="post" action="{{route('filter-print-kwitansi')}}" enctype="multipart/form-data">
    @csrf
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
            <button onclick="location.href='{{ url('/print-kwitansi/add') }}'" name="Find" class="btn btn-sm btn-info" title="Add Data"><i class="fa fa-angle-left"></i> Kembali</button>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <form method="post" action="{{route('process-add-kwitansi')}}" enctype="multipart/form-data">
                @csrf
                <div class="col-md-6">
                    <div class="form-group">
                        {{-- <a class="text-dark">Jenis Cetak<a class='red'> *</a></a>
                        <br/>
                         <select class="selection-search-clear" name="print_type" id="print_type" style="width: 100% !important">
                            <option value="0">-- Pilih --</option>
                            <option value="1">Single</option>
                            <option value="2">Multiple</option>
                        </select> --}}
                        {{-- <div class="col-md-6" style="margin-left: 75vw;">
                            <a onclick="check_all()" name="Find" class="btn btn-sm btn-info" title="Back"> Check All</a>
                            <a onclick="uncheck_all()" name="Find" class="btn btn-sm btn-info" title="Back"> UnCheck All</a>
                        </div> --}}
                        </div>
                    </div>
                <br>
            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                <thead>
                    <tr>
                        <th width="2%" style='text-align:center'>No</th>
                        <th width="10%" style='text-align:center'>Customer</th>
                        <th width="10%" style='text-align:center'>Tanggal Invoice</th>
                        <th width="10%" style='text-align:center'>No. Invoice</th>
                        <th width="10%" style='text-align:center'>No. BPB</th>
                        <th width="10%" style='text-align:center'>Jumlah</th>
                        <th width="10%" style='text-align:center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                     $no = 1;   
                     $total_no = 1;                 
                    ?>
                    @foreach($salesinvoice as $item)
                    <tr>
                        <td style='text-align:center'>{{$no}}</td>
                        <td>{{ $Kwitansi->getCustomerName($item['customer_id'])}}</td>
                        <td>{{$item['sales_invoice_date']}}</td>
                        <td>{{ $item['sales_invoice_no']}}
                            <input class='form-control' type='hidden' name='sales_invoice_id_{{$no}}' id='sales_invoice_id_{{$no}}' value='{{$item['sales_invoice_id']}}'/>  
                        </td>
                        <td >{{ $item['buyers_acknowledgment_no']}}
                            <input class='form-control' type='hidden' name='buyers_acknowledgment_id_{{$no}}' id='buyers_acknowledgment_id_{{$no}}' value='{{$item['buyers_acknowledgment_id']}}'/>  </td>
                        <td>{{ number_format($item['total_amount'])}}</td>
                        <td style='text-align:center'>
                            <input type='checkbox' class='checkboxes' name='checkbox_view_{{$no}}' id='checkbox_view_{{$no}}' onchange="handleChange({{ $no }})"/>
                            <input class='form-control' type='hidden' name='checkbox_{{$no}}' id='checkbox_{{ $no }}' value="1"/> 
                        </td>
                    </tr>
                    @php
                        $total_no = $no;
                    $no++;

                     @endphp
                    @endforeach
                </tbody>
                <input class='form-control' style='text-align:right;'type='hidden' name='total_no' id='total_no' value='{{$total_no}}'/>
                <input class='form-control' type='hidden' name='customer_id' id='customer_id' value='{{$customer_id}}'/>  
            </table>
            <div class="card-footer text-muted">
                <div class="form-actions float-right">
                    <button type="reset" name="Reset" class="btn btn-danger btn-sm" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
                    <button type="submit" name="Save"  class="btn btn-primary btn-sm" title="Save"><i class="fa fa-check"></i> Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>
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