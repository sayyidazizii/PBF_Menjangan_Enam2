@inject('SalesCollection', 'App\Http\Controllers\SalesCollectionController')
@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />
@section('js')
<script>
	function toRp(number) {
		var number = number.toString(), 
		rupiah = number.split('.')[0], 
		cents = (number.split('.')[1] || '') +'00';
		rupiah = rupiah.split('').reverse().join('')
			.replace(/(\d{3}(?!$))/g, '$1.')
			.split('').reverse().join('');
		return rupiah + ',' + cents.slice(0, 2);
	}
</script>
@stop
@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('sales-collection') }}">Daftar Pelunasan Piutang</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail Pelunasan Piutang</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    Form Detail Pelunasan Piutang
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
            Form Detail Pelunasan Piutang Diskon
        </h5>
        <div class="float-right">
            <button onclick="location.href='{{ url('sales-collection') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form enctype="multipart/form-data">
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Pemasok</a>
                        <input class="form-control input-bb" type="text" name="customer_name" id="customer_name" value="" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tanggal Pelunasan</a>
                        <input class="form-control input-bb" type="text" name="collection_date" id="collection_date" value="" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Kas</a>
                        <input class="form-control input-bb" type="text" name="cash_account_name" id="cash_account_name" value="" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Jumlah Pelunasan Tunai</a>
                        <input class="form-control input-bb" type="text" name="collection_total_cash_amount" id="collection_total_cash_amount" value="" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Jumlah Pelunasan Transfer</a>
                        <input class="form-control input-bb" type="text" name="collection_total_transfer_amount" id="collection_total_transfer_amount" value="" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12 ">
                    <a class="text-dark">Keterangan</a>
                    <div class="">
                        <textarea rows="3" type="text" class="form-control input-bb" name="collection_remark" onChange="function_elements_add(this.name, this.value);" id="collection_remark" readonly></textarea>
                    </div>
                </div>
            </div>
    </div>
    </div>

    <br/>
    <div class="card border border-dark">
        <div class="card-header border-dark bg-dark">
            <h5 class="mb-0 float-left">
                Daftar Invoice
            </h5>
        </div>
    
        <div class="card-body">
            <div class="form-body form">
                <div class="table-responsive">
                    <table class="table table-bordered table-advance table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style='text-align:center'>No.</th>
                                <th style='text-align:center'>Tanggal</th>
                                <th style='text-align:center'>No. Invoice</th>
                                <th style='text-align:center'>Jumlah Piutang</th>
                                <th style='text-align:center'>Telah Dibayarkan</th>
                                <th style='text-align:center'>Sisa Piutang</th>
                                <th style='text-align:center'>Alokasi</th>
                                <th style='text-align:center'>Pembulatan</th>
                                <th style='text-align:center'>Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                // if(count($salescollectionitem)<1){
                                //     echo "<tr><th colspan='9' style='text-align  : center !important;'>Data Kosong</th></tr>";
                                // } else {
                                //     $no =1;
                                //     $total_allocation = 0;
                                //     $total_shortover  = 0;
                                //     foreach ($salescollectionitem AS $key => $val){
                                //         echo"
                                //             <tr>
                                //                 <td style='text-align  : center'>".$no."</td>
                                //                 <td style='text-align  : left'>".$val['sales_invoice_date']."</td>
                                //                 <td style='text-align  : left'>".$val['sales_invoice_no']."</td>
                                //                 <td style='text-align  : right'>".number_format($val['total_amount'], 2)."</td>
                                //                 <td style='text-align  : right'>".number_format($val['paid_amount'], 2)."</td>
                                //                 <td style='text-align  : right'>".number_format($val['owing_amount'], 2)."</td>
                                //                 <td style='text-align  : right'>".number_format($val['allocation_amount'], 2)."</td>
                                //                 <td style='text-align  : right'>".number_format($val['shortover_value'], 2)."</td>
                                //                 <td style='text-align  : right'>".number_format($val['last_balance'], 2)."</td>";
                                //                 echo"
                                //             </tr>
                                //         ";
                                //         $no++;
                                //         $total_allocation+=$val['allocation_amount'];
                                //         $total_shortover+=$val['shortover_value'];
                                //     }
                                //         echo"
                                //         <th style='text-align  : center' colspan='6'>Total</th>
                                //         <th style='text-align  : right'>".number_format($total_allocation,2)."</th>
                                //         <th style='text-align  : right'>".number_format($total_shortover,2)."</th>
                                //         <th style='text-align  : center'></th>
                                //         ";
                                // }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <br/>
    <div class="card border border-dark">
        <div class="card-header border-dark bg-dark">
            <h5 class="mb-0 float-left">
                Daftar Transfer Bank
            </h5>
        </div>
    
        <div class="card-body">
            <div class="form-body form">
                <div class="table-responsive">
                    <table class="table table-bordered table-advance table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th style='text-align:center'>No.</th>
                                <th style='text-align:center'>Nama Bank</th>
                                <th style='text-align:center'>Nama Akun</th>
                                <th style='text-align:center'>No. Rekening</th>
                                <th style='text-align:center'>Jumlah Transfer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                // if(count($salescollectiontransfer)<1){
                                //     echo "<tr><th colspan='5' style='text-align  : center !important;'>Data Kosong</th></tr>";
                                // } else {
                                //     $no =1;
                                //     $total_transfer = 0;
                                    // foreach ($salescollectiontransfer AS $key => $val){
                                    //     echo"
                                    //         <tr>
                                    //             <td style='text-align  : center'>".$no."</td>
                                    //             <td style='text-align  : left'>".$val['collection_transfer_bank_name']."</td>
                                    //             <td style='text-align  : left'>".$val['collection_transfer_account_name']."</td>
                                    //             <td style='text-align  : left'>".$val['collection_transfer_account_no']."</td>
                                    //             <td style='text-align  : right'>".number_format($val['collection_transfer_amount'], 2)."</td>";
                                    //             echo"
                                    //         </tr>
                                    //     ";
                                    //     $no++;
                                    //     $total_transfer+=$val['collection_transfer_amount'];
                                    // }
                                        // echo"
                                        // <th style='text-align  : center' colspan=4'>Total</th>
                                        // <th style='text-align  : right'>".number_format($total_transfer, 2)."</th>
                                        // ";
                                // }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</form>
<br/>
<br/>
<br/>

@stop

@section('footer')
    
@stop

@section('css')
    
@stop