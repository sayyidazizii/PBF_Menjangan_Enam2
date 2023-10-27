@inject('SalesCollectionPiece', 'App\Http\Controllers\SalesCollectionPieceController')
@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />
@section('js')
    <script>
        function toRp(number) {
            var number = number.toString(),
                rupiah = number.split('.')[0],
                cents = (number.split('.')[1] || '') + '00';
            rupiah = rupiah.split('').reverse().join('')
                .replace(/(\d{3}(?!$))/g, '$1.')
                .split('').reverse().join('');
            return rupiah + ',' + cents.slice(0, 2);
        }



     function datapiece_add($sales_invoice_id){
        var sales_invoice_id = $("#sales_invoice_id").val();
        var sales_collection_piece_remark = $("#sales_collection_piece_remark").val();



        console.log(sales_invoice_id,sales_collection_piece_remark);
        $.ajax({
            type: "post",
            url : "{{route('process-claim')}}",
            dataType: "html",
            data: {
                'sales_invoice_id'                      : sales_invoice_id,
                'sales_collection_piece_remark'         : sales_collection_piece_remark,
                '_token'                                : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                window.location.replace('http://127.0.0.1:8000/sales-collection-piece');
                console.log(data);
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }


    function data_delete($no){
        var sales_collection_piece_id = $("#id-piece_"+$no).val();


        console.log(sales_collection_piece_id);
        $.ajax({
            type: "post",
            url : "{{route('delete-sales-collection-piece')}}",
            dataType: "html",
            data: {
                'sales_collection_piece_id'      : sales_collection_piece_id,
                '_token'                   : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                window.location.reload();
                console.log(data);
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
            <li class="breadcrumb-item"><a href="{{ url('sales-collection') }}">Sales Collection</a></li>
            <li class="breadcrumb-item active" aria-current="page">Claim Potongan</li>
        </ol>
    </nav>

@stop

@section('content')

    <h3 class="page-title">
        Form Detail Claim Potongan
    </h3>
    <br />
    @if (session('msg'))
        <div class="alert alert-info" role="alert">
            {{ session('msg') }}
        </div>
    @endif
    <div class="card border border-dark">
        <div class="card-header border-dark bg-dark">
            <h5 class="mb-0 float-left">
                Form Detail
            </h5>
            <div class="float-right">
                <button onclick="location.href='{{ url('/sales-collection') }}'" name="Find"
                    class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i> Kembali</button>
            </div>
        </div>

        {{-- <form method="POST" action="{{ url('/sales-collection-piece/process-claim')}}" enctype="multipart/form-data"> --}}
            @csrf
            <div class="card-body">
                <div class="row form-group">
                
                    <input class="form-control input-bb" type="hidden" name="sales_invoice_id"
                    id="sales_invoice_id"
                    value="{{ $salesinvoice['sales_invoice_id'] }}" readonly />

                        <div class="col-md-6">
                            <div class="form-group">
                                <a class="text-dark">Nama Pelanggan</a>
                                <input class="form-control input-bb" type="text" name="customer_id"
                                    id="customer_id"
                                    value="{{ $SalesCollectionPiece->getCustomerName($salesinvoice['customer_id']) }}" readonly />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <a class="text-dark">No Invoice</a>
                                <input class="form-control input-bb" type="text" name="sales_invoice_no"
                                    id="sales_invoice_no" value="{{ $salesinvoice['sales_invoice_no'] }}" readonly />
                            </div>
                        </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-6">
                        <div class="form-group">
                            <a class="text-dark">Tanggal Invoice</a>
                            <input class="form-control input-bb" type="text" name="sales_invoice_date"
                                id="sales_invoice_date" value="{{ $salesinvoice['sales_invoice_date'] }}" readonly />
                        </div>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-12 ">
                        <a class="text-dark">Keterangan</a>
                        <div class="">
                            <textarea rows="3" type="text" class="form-control input-bb" name="sales_collection_piece_remark"
                                onChange="function_elements_add(this.name, this.value);" id="sales_collection_piece_remark" readonly></textarea>
                        </div>
                    </div>
                </div>
               
            </div>
    </div>

    <br />
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
                                <th style='text-align:center'>Total Invoice</th>
                                <th style='text-align:center'>Potongan</th>
                                <th style='text-align:center'>Jenis</th>
                                <th style='text-align:center'>jumlah Setelah Potongan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $no = 1;
                        @endphp
                        @foreach ($salescolectionpiece as $item)
                            <tr>
                                <td style='text-align  : center'>{{ $no }}</td>
                                <td style='text-align  : right !important;'>
                                    {{number_format(($item['total_amount'] ), 2)}}</td>
                                <td style='text-align  : right !important;'>
                                    {{number_format(($item['piece_amount'] ), 2)}}</td>
                                    @php
                                    if($item->sales_collection_piece_type_id == 1){
                                      $claim = 'Promosi';
                                    }
                                    if($item->sales_collection_piece_type_id == 2){
                                      $claim = 'biasa';
                                    }
                                @endphp   
                                <td>{{ $claim }}</td>
                                <td style='text-align  : right !important;'>
                                    {{number_format(($item['total_amount_after_piece'] ), 2)}}</td>
                                    <td>
                                        <input class="form-control" type="hidden" style='text-align:right' name="id-piece_{{ $item['sales_collection_piece_id'] }}" id="id-piece_{{ $item['sales_collection_piece_id'] }}" value="{{$item['sales_collection_piece_id']}}" readonly/>
                                        <button id="delete-piece-button_{{ $item['sales_collection_piece_id'] }}" onclick="data_delete({{ $item['sales_collection_piece_id'] }});" type="button" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                                    </td>
                            </tr>
                            @php
                            $no++
                        @endphp
                        @endforeach
                        </tbody>
                    </table>
                  
                </div>
            </div>
        </div>
    </div>

    {{-- </form> --}}
    <br />
    <br />
    <br />

@stop

@section('footer')

@stop

@section('css')

@stop
