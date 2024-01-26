@inject('SalesCollection', 'App\Http\Controllers\SalesCollectionController')
@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>
    
	$(document).ready(function(){
        $("#account_id").select2("val", "0");

        var payment_type = $("#payment_type").val();
               if(payment_type == 0){
                $('#collection_total_cash_amount').attr('readonly', false);
                $('#tranfer-bank').hide();
               }else{
                $('#collection_total_cash_amount').attr('readonly', true);
                $('#tranfer-bank').show();
               }

        var elements = {!! json_encode($salescollectionelements) !!};

        if(!elements || elements==''){
            elements = [];
        }


        //payment type
        $("#payment_type").change(function(){
            var payment_type = $("#payment_type").val();
               if(payment_type == 0){
                $('#collection_total_cash_amount').attr('readonly', false);
                $('#tranfer-bank').hide();
               }else{
                $('#collection_total_cash_amount').attr('readonly', true);
                $('#tranfer-bank').show();
               }
        });

        if(!elements['cash_account_id']){
            $("#cash_account_id").select2("val", "0");
        }

        if(!elements['transfer_account_id']){
            $("#transfer_account_id").select2("val", "0");
        }

        if(!elements['collection_total_cash_amount']){
            $("#collection_total_cash_amount").val(0);
        }
        $('#lain_lain_amount').attr('readonly', true);


        $("#lain_lain_amount_check").change(function(){
            var ppn = $("#lain_lain").val();
                // Check the state of the checkbox
                if($(this).is(":checked")){
                    // If checkbox is checked, set text value to 1
                    $("#lain_lain").val("1");
                    $('#lain_lain_amount').attr('readonly', false);
                    console.log(ppn);
                } else {
                    // If checkbox is not checked, set text value to 0
                    $("#lain_lain").val("0");
                    $('#lain_lain_amount').attr('readonly', true);
                    console.log(ppn);
                }
        });

        calculateTotal();
    });

    function elements_add(name, value){
        $.ajax({
            type: "POST",
            url : "{{route('elements-add-sales-collection')}}",
            dataType: "html",
            data: {
                'name'      : name,
                'value'	    : value,
                '_token'    : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                console.log(return_data);
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }

    $("#collection_total_cash_amount").change(function(){
        calculateTotal();
    });

    function calculateTotal(){
        var collection_total_cash_amount 	    = $("#collection_total_cash_amount").val();
        var collection_total_transfer_amount   = $("#collection_total_transfer_amount").val();
        if(isNaN(collection_total_cash_amount)){
            collection_total_cash_amount = 0;
        }
        if(isNaN(collection_total_transfer_amount)){
            collection_total_transfer_amount = 0;
        }

        var total = parseFloat(collection_total_cash_amount) + parseFloat(collection_total_transfer_amount);

        $("#collection_amount_view").val(toRp(total));
        $("#collection_amount").val(total);
    }

    function calculateAllocation(){
        var collection_amount  = $("#collection_amount").val();
        var item_total      = $("#item_total").val();
        var allocationtotal = 0;
        var shortovertotal  = 0;
        var lainlaintotal   = 0;



        for(i=0; i<item_total; i++){
            var lastbalance     = 0;
            var owing_amount 	= $("#"+i+"_owing_discount_amount").val();
            var allocation 	    = $("#"+i+"_allocation").val();
            var shortover 	    = $("#"+i+"_shortover").val();
            var lain_lain 	    = $("#"+i+"_lain_lain_amount").val();
            
            
            if(isNaN(allocation)){
                allocation = 0;
            }
            if(isNaN(shortover)){
                shortover = 0;
            }
            if(isNaN(lain_lain)){
                lain_lain = 0;
            }

            allocationtotal += parseFloat(allocation);
            shortovertotal  += parseFloat(shortover);
            lainlaintotal   += parseFloat(lain_lain);


            lastbalance = parseFloat(owing_amount) - parseFloat(allocation) - parseFloat(shortover) - parseFloat(lain_lain);
            $("#"+i+"_last_balance_view").val(toRp(lastbalance));
            $("#"+i+"_last_balance").val(lastbalance);
        }

        $("#allocation_total").val(allocationtotal);
        $("#shortover_total").val(shortovertotal);
        $("#allocation_total_view").val(toRp(allocationtotal));
        $("#shortover_total_view").val(toRp(shortovertotal));

        $("#lain_lain_amount_view").val(toRp(lainlaintotal));
        $("#lain_lain_amount").val(lainlaintotal);


        $("#collection_allocated_move_view").val(toRp(parseFloat(collection_amount) - parseFloat(allocationtotal) - parseFloat(shortovertotal)));
        $("#collection_allocated_move").val(parseFloat(collection_amount) - parseFloat(allocationtotal) - parseFloat(shortovertotal));
    }

	function toRp(number) {
		var number = number.toString(), 
		rupiah = number.split('.')[0], 
		cents = (number.split('.')[1] || '') +'00';
		rupiah = rupiah.split('').reverse().join('')
			.replace(/(\d{3}(?!$))/g, '$1.')
			.split('').reverse().join('');
		return rupiah + ',' + cents.slice(0, 2);
	}
    
    
    function processAddArraySalesCollectionTransfer(){
        var transfer_account_id	                    = document.getElementById("transfer_account_id").value;
        var collection_transfer_bank_name	        = document.getElementById("collection_transfer_bank_name").value;
        var collection_transfer_account_name	    = document.getElementById("collection_transfer_account_name").value;
        var collection_transfer_account_no	        = document.getElementById("collection_transfer_account_no").value;
        var collection_transfer_amount	            = document.getElementById("collection_transfer_amount").value;

        $.ajax({
            type: "POST",
            url : "{{route('add-transfer-array-sales-collection')}}",
            data: {
                'transfer_account_id'	            : transfer_account_id,
                'collection_transfer_bank_name'	    : collection_transfer_bank_name,
                'collection_transfer_account_name'	: collection_transfer_account_name,
                'collection_transfer_account_no'	: collection_transfer_account_no,
                'collection_transfer_amount'	    : collection_transfer_amount,
                '_token'                            : '{{csrf_token()}}'
            },
            success: function(msg){
                location.reload();
            }
        });
    }



    function datapiece_add($no){

        $("#sales_collection_piece_type_id"+$no).change(function(){
        var sales_collection_piece_type_id = $("#sales_collection_piece_type_id_"+$no).val();

        if(sales_collection_piece_type_id == 2){
            $("#nomor_promosi_"+$no).show();
            $("#nomor_memo_"+$no).show();
        }else{
            $("#nomor_promosi_"+$no).hide();
            $("#nomor_memo_"+$no).hide();
        }

        console.log(sales_collection_piece_type_id);
    });


        var sales_invoice_id = $("#sales_invoice_id_"+$no).val();
        var sales_invoice_no = $("#sales_invoice_no_"+$no).val();
        var customer_id = $("#customer_id").val();
        var total_amount = $("#total_amount_"+$no).val();
        var piece_amount = $("#piece_amount_"+$no).val();
        var sales_collection_piece_type_id = $("#sales_collection_piece_type_id_"+$no).val();
        var promotion_no = $("#nomor_promosi_"+$no).val();
        var memo_no = $("#nomor_memo_"+$no).val();


        console.log(sales_invoice_id,sales_invoice_no,total_amount,piece_amount,sales_collection_piece_type_id,promotion_no,memo_no);
        $.ajax({
            type: "POST",
            url : "{{route('add-sales-collection-piece')}}",
            dataType: "html",
            data: {
                'sales_invoice_id'                          : sales_invoice_id,
                'sales_invoice_no'                          : sales_invoice_no,
                'customer_id'                               : customer_id,
                'total_amount'                              : total_amount,
                'piece_amount'                              : piece_amount,
                'sales_collection_piece_type_id'            : sales_collection_piece_type_id,
                'promotion_no'                              : promotion_no,
                'memo_no'                                   : memo_no,
                '_token'                                    : '{{csrf_token()}}',
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


    function change_type($no){
        var sales_collection_piece_type_id = $("#sales_collection_piece_type_id_"+$no).val();
        console.log(sales_collection_piece_type_id);
        // $("#sales_collection_piece_type_id"+$no).change(function(){
        if(sales_collection_piece_type_id == 2){
            $("#nomor_promosi_"+$no).hide();
            $("#nomor_memo_"+$no).hide();
            $("#label_promosi").hide();
            $("#label_memo").hide();
            
        }else{
            $("#nomor_promosi_"+$no).show();
            $("#nomor_memo_"+$no).show();
            $("#label_promosi").show();
            $("#label_memo").show();
        }
        // console.log(sales_collection_piece_type_id);
    // });
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
        <li class="breadcrumb-item"><a href="{{ url('sales-collection') }}">Daftar Pelunasan Diskon Margin</a></li>
        <li class="breadcrumb-item"><a href="{{ url('sales-collection/search') }}">Daftar Pelanggan</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Pelunasan Diskon Margin</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    Form Tambah Pelunasan Diskon Margin
</h3>
<br/>
@if(session('msg'))
<div class="alert alert-info" role="alert">
    {{session('msg')}}
</div>
@endif
@if(count($errors) > 0)
<div class="alert alert-danger" role="alert">
    @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
    @endforeach
</div>
@endif
<div class="card border border-dark">
    <div class="card-header border-dark bg-dark">
        <h5 class="mb-0 float-left">
            Form Tambah
        </h5>
        <div class="float-right">
            <button onclick="location.href='{{ url('sales-collection') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('add-sales-discount-collection')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <section class="control-label">Tanggal Pelunasan
                        <span class="required text-danger">
                            *
                        </span>
                    </section>
                    <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="collection_date" id="collection_date" onChange="elements_add(this.name, this.value);" value="{{$salescollectionelements == null ? '' : $salescollectionelements['collection_date']}}" style="width: 15rem;"/>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Nama Pelanggan</a>
                        <input class="form-control input-bb" type="text" name="customer_name" id="customer_name" value="{{$customer['customer_name']}}" readonly/>
                        <input class="form-control input-bb" type="hidden" name="customer_id" id="customer_id" value="{{$customer['customer_id']}}" readonly/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12 ">
                    <a class="text-dark">Keterangan</a>
                    <div class="">
                        <textarea rows="3" type="text" class="form-control input-bb" name="collection_remark" onChange="elements_add(this.name, this.value);" id="collection_remark" autocomplete='off'>{{$salescollectionelements == null ? '' : $salescollectionelements['collection_remark']}}</textarea>
                    </div>
                </div>
            </div>

            <br/>
            <div class="row">
                <h4 class="form-section"><b>Detail Pelunasan Piutang Diskon</b></h4>
            </div>
            <hr style="margin:0;">
            <br/>
            
            <div class="row form-group">
                {{-- <div class="col-md-6">
                    <a class="text-dark">No Perkiraan</a>
                    {!! Form::select('cash_account_id',  $acctaccount, $salescollectionelements == null ? '' :  $salescollectionelements['cash_account_id'], ['class' => 'selection-search-clear select-form', 'id' => 'cash_account_id', 'onchange' => 'elements_add(this.name, this.value);']) !!}
                </div> --}}
                <div class="col-md-6">
                    <a class="text-dark">Tipe Pembayaran</a>
                            <br/>
                            {!! Form::select(0, $payment_type_list, $salescollectionelements['payment_type'] ?? '', ['class' => 'form-control selection-search-clear select-form', 'id' => 'payment_type','name' => 'payment_type','onchange'=>'elements_add(this.name, this.value)']) !!}
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Tunai</a>
                        <input class="form-control input-bb" type="text" name="collection_total_cash_amount" id="collection_total_cash_amount" value="{{$salescollectionelements == null ? '' : $salescollectionelements['collection_total_cash_amount']}}" onChange="elements_add(this.name, this.value);" style='text-align:right'/>
                    </div>
                </div>
            </div>
            <br/>
            <div class="container" id="tranfer-bank">
                <div class="row">
                    <h5 class="form-section"><b>Transfer Bank</b></h5>
                </div>
                <hr style="margin:0;">
                <br/>
                <div class="row form-group">
                    <div class="col-md-6">
                        <a class="text-dark">No Perkiraan</a>
                        {!! Form::select('transfer_account_id',  $acctaccount, $salescollectionelements == null ? '' : $salescollectionelements['transfer_account_id'], ['class' => 'selection-search-clear select-form', 'id' => 'transfer_account_id', 'onchange' => 'elements_add(this.name, this.value);']) !!}
                    </div>
                </div>
                <br/>
                <div class="row">
                    <p class="form-section"><b>Data Bank</b></p>
                </div>
                <br/>
                <div class="row form-group">
                    <div class="col-md-6">
                        <div class="form-group">
                            <a class="text-dark">Nama Bank</a>
                            <input class="form-control input-bb" type="text" name="collection_transfer_bank_name" id="collection_transfer_bank_name" value="" autocomplete='off'/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <a class="text-dark">Nama Akun</a>
                            <input class="form-control input-bb" type="text" name="collection_transfer_account_name" id="collection_transfer_account_name" value="" autocomplete='off'/>
                        </div>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-6">
                        <div class="form-group">
                            <a class="text-dark">No Rekening</a>
                            <input class="form-control input-bb" type="text" name="collection_transfer_account_no" id="collection_transfer_account_no" value="" autocomplete='off'/>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <a class="text-dark">Jumlah Transfer</a>
                            <input class="form-control input-bb" type="text" name="collection_transfer_amount" id="collection_transfer_amount" value="" autocomplete='off'/>
                        </div>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-md-12" style='text-align:right'>
                        <div class="form-actions float-right">
                            <a type="submit" name="Save" class="btn btn-primary" title="Save" id="buttonAddArraySalesCollectionTransfer" onclick="processAddArraySalesCollectionTransfer()">Tambah</a>
                        </div>
                    </div>
                </div>
                <br>
                <br>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="example" style="width:100%" class="table table-striped table-bordered table-hover table-full-width">
                                <thead class="thead-light">
                                    <tr>
                                        <th style='text-align:center' width="20%">Bank</th>
                                        <th style='text-align:center' width="20%">Nama Akun</th>
                                        <th style='text-align:center' width="20%">No. Akun</th>
                                        <th style='text-align:center' width="20%">Jumlah Transfer</th>
                                        <th style='text-align:center' width="10%">Aksi</th>	
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        $total_transfer = 0;
                                        if(!is_array($salescollectiontransfer)){
                                            echo "<tr><th colspan='9' style='text-align:center'>Data Kosong</th></tr>";
                                        } else {
                                            foreach ($salescollectiontransfer as $key=>$val){
                                                echo"
                                                    <tr>
                                                        <td style='text-align  : left !important;'>".$SalesCollection->getAccountName($val['transfer_account_id'])."</td>
                                                        <td style='text-align  : left !important;'>".$val['collection_transfer_account_name']."</td>
                                                        <td style='text-align  : left !important;'>".$val['collection_transfer_account_no']."</td>
                                                        <td style='text-align  : right !important;'>".number_format($val['collection_transfer_amount'], 2)."</td>";
                                                        ?>
                                                        <td style='text-align  : center !important;'>
                                                            <a href="{{route('delete-transfer-array-sales-discount-collection', ['record_id' => $key, 'sales_kwitansi_id' => $sales_kwitansi_id])}}" name='Reset' class='btn btn-danger btn-sm' onClick='javascript:return confirm(\"apakah yakin ingin dihapus ?\")'></i> Hapus</a>
                                                        </td>
                                                        <?php
                                                        echo"
                                                    </tr>								
                                                ";	
                                                $total_transfer += $val['collection_transfer_amount'];													
                                            }
                                            echo"
                                                <input class='form-control input-bb' type='hidden' name='collection_total_transfer_amount' id='collection_total_transfer_amount' value='".$total_transfer."' autocomplete='off'/>
                                            ";
                                        }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <br>
                <br>
            </div>
            <div class="row form-group">
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Jumlah</a>
                        <input class="form-control input-bb" type="text"  style='text-align:right' name="collection_amount_view" id="collection_amount_view" value="" readonly/>
                        <input class="form-control input-bb" type="hidden"  style='text-align:right' name="collection_amount" id="collection_amount" value=""/>
                    </div>
                </div>
                <div class="col-md-4" hidden>
                    <div class="form-group">
                        <a class="text-dark">Total Alokasi</a>
                        <input class="form-control input-bb" type="text" style='text-align:right' name="collection_allocated_move_view" id="collection_allocated_move_view" value="" readonly/>
                        <input class="form-control input-bb" type="hidden" style='text-align:right' name="collection_allocated_move" id="collection_allocated_move" value=""/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<br/>
@if (session('msg'))
<div class="alert alert-info" role="alert">
    {{ session('msg') }}
</div>
@endif
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
                            <th style='text-align:center'>Tanggal</th>
                            <th style='text-align:center'>No_Invoice</th>
                            <th style='text-align:center'>No_Penerimaan</th>
                            <th style='text-align:center'>Jumlah_Diskon</th>
                            <th style='text-align:center'>Dibayar</th>
                            <th style='text-align:center'>Sisa</th>
                            <th style='text-align:center'>Alokasi_Pembayaran</th>
                            <th style='text-align:center'>Pembulatan</th>
                            <th style='text-align:center'>potongan_lain_lain</th>
                            <th style='text-align:center'>Saldo_Akhir_margin</th>
                            <!-- <th style='text-align:center'>Aksi</th> -->
                            <!-- <th style='text-align:center' colspan="3">Potongan -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if(count($salesinvoiceowing) == 0){
                                echo "<tr><th colspan='9' style='text-align  : center !important;'>Data Kosong</th></tr>";
                            } else {
                                $no =1;
                                $nos =0;
                                $allocation_total = 0;
                                $shortover_total  = 0;
                                $lain_lain_total  = 0;

                                foreach ($salesinvoiceowing AS $key => $val){?>
                                <tr>
                                    <td style='text-align  : center'>{{$val['sales_invoice_date']}}</td>
                                    <td style='text-align  : center'>{{$val['sales_invoice_no']}}</td>
                                    <td style='text-align  : center'>{{$val['buyers_acknowledgment_no']}}</td>
                                    <td style='text-align  : center'>{{number_format($val['total_discount_amount'], 2)}}</td>
                                    <td style='text-align  : center'>{{number_format($val['paid_discount_amount'], 2)}}</td>
                                    <td style='text-align  : center'>{{number_format($val['owing_discount_amount'], 2)}}</td>
                                    <td style='text-align  : center'>
                                        <input class="form-control" type="text" style='text-align:right' name="{{$no}}_allocation" id="{{$no}}_allocation" value="0" onChange="calculateAllocation()"/>
                                    </td>
                                    <td style='text-align  : center'>
                                        <input class="form-control" type="text" style='text-align:right' name="{{$no}}_shortover" id="{{$no}}_shortover" value="0" onChange="calculateAllocation()"/>
                                    </td>
                                    <td style='text-align  : center'>
                                        <input class="form-control" type="text" style='text-align:right' name="{{$no}}_lain_lain_amount" id="{{$no}}_lain_lain_amount" value="0" onChange="calculateAllocation()"/>
                                    </td>
                                    <td style='text-align  : center'>
                                        <input class="form-control" type="text" style='text-align:right' name="{{$no}}_last_balance_view" id="{{$no}}_last_balance_view" value="{{number_format($val['owing_discount_amount'],2)}}" readonly/>
                                        <input class="form-control" type="hidden" style='text-align:right' name="{{$no}}_last_balance" id="{{$no}}_last_balance" value="{{$val['owing_discount_amount'],2}}" readonly/>

                                        
                                        <input class="form-control" type="hidden" style='text-align:right' name="{{$no}}_sales_invoice_id" id="{{$no}}_sales_invoice_id" value="{{$val['sales_invoice_id']}}" readonly/>
                                        <input class="form-control" type="hidden" style='text-align:right' name="{{$no}}_sales_invoice_amount" id="{{$no}}_sales_invoice_amount" value="{{$val['total_discount_amount']}}" readonly/>
                                        <input class="form-control" type="hidden" style='text-align:right' name="{{$no}}_sales_invoice_date" id="{{$no}}_sales_invoice_date" value="{{$val['sales_invoice_date']}}" readonly/>
                                        <input class="form-control" type="hidden" style='text-align:right' name="{{$no}}_sales_invoice_no" id="{{$no}}_sales_invoice_no" value="{{$val['sales_invoice_no']}}" readonly/>
                                        <input class="form-control" type="hidden" style='text-align:right' name="{{$no}}_total_discount_amount" id="{{$no}}_total_discount_amount" value="{{$val['total_discount_amount']}}" readonly/>
                                        <input class="form-control" type="hidden" style='text-align:right' name="{{$no}}_paid_discount_amount" id="{{$no}}_paid_discount_amount" value="{{$val['paid_discount_amount']}}" readonly/>
                                        <input class="form-control" type="hidden" style='text-align:right' name="{{$no}}_owing_discount_amount" id="{{$no}}_owing_discount_amount" value="{{$val['owing_discount_amount']}}" readonly/>
                                    </td>
                                </tr>
                                <?php
                                $no++;
                                $nos++;
                                }
                                    echo"
                                    <th style='text-align  : right' colspan='6'>Total</th>
                                    <th style='text-align  : right'>
                                        <input class='form-control' type='text' style='text-align:right' name='allocation_total_view' id='allocation_total_view' value='".$allocation_total."' readonly/>
                                        <input class='form-control' type='hidden' style='text-align:right' name='allocation_total' id='allocation_total' value='".$allocation_total."' readonly/>
                                    </th>
                                    <th>
                                        <input class='form-control' type='text' style='text-align:right' name='shortover_total_view' id='shortover_total_view' value='".$shortover_total."' readonly/>
                                        <input class='form-control' type='hidden' style='text-align:right' name='shortover_total' id='shortover_total' value='".$shortover_total."' readonly/>
                                    </th>
                                    <th>
                                        <input class='form-control input-bb' type='hidden' name='item_total' id='item_total' value='".$no."'/>
                                    </th>
                                    <th>
                                    </th>

                                    ";

                                    echo"
                                    <tr>
                                        <th style='text-align  : right' colspan='6'>Total Lain - Lain</th>
                                    <th style='text-align  : right'colspan='2' >
                                        <input class='form-control' hidden type='text' style='text-align:left' name='lain_lain' id='lain_lain' value='0'/>
                                        <input class='form-control input-bb' type='text' style='text-align:right' name='lain_lain_amount_view' id='lain_lain_amount_view' value='".$lain_lain_total."'/>
                                        <input class='form-control input-bb' hidden type='text' style='text-align:right' name='lain_lain_amount' id='lain_lain_amount' value='".$lain_lain_total."'/>
                                    </th>
                                    <th>
                                        <input class='form-control' type='checkbox' style='text-align:left' name='lain_lain_amount_check' id='lain_lain_amount_check' value='1'/>
                                        <input class='form-control input-bb' type='hidden' name='item_total' id='item_total' value='".$no."'/>
                                    </th>
                                    <th>
                                    </th>
                                    </tr>
                                   
                                    ";
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card-footer text-muted">
        <div class="form-actions float-right">
            <button type="reset" name="Reset" class="btn btn-danger" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
            <button type="submit" name="Save" class="btn btn-primary" title="Save"><i class="fa fa-check"></i> Simpan</button>
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