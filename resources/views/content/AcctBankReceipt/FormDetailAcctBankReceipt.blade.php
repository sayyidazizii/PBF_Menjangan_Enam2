@inject('AcctBankReceipt', 'App\Http\Controllers\AcctBankReceiptController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>
    function changeDate(name, value){
        var bank_bank_receipt_date    =   document.getElementById("bank_bank_receipt_date").value;
        $('#rbank_eceipt_date_table').html(bank_bank_receipt_date);
    }
    
	function processAddArrayAcctReceiptItem(){
		var account_id_item					= document.getElementById("account_id_item").value;
		var bank_receipt_item_amount				= document.getElementById("bank_receipt_item_amount").value;
		var bank_receipt_item_title				= document.getElementById("bank_receipt_item_title").value;
        console.log(account_id_item);
        console.log(bank_receipt_item_amount);
        console.log(bank_receipt_item_title);

		
        $.ajax({
        type: "POST",
        url : "{{route('add-bank-receipt-array')}}",
        data: {
            'account_id_item'					: account_id_item,
            'bank_receipt_item_amount' 				: bank_receipt_item_amount, 
            'bank_receipt_item_title' 				: bank_receipt_item_title,
            '_token'                            : '{{csrf_token()}}'
        },
        success: function(msg){
            location.reload();
        }
    });
	}
</script>
@stop

@section('content_header')

<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('bank-receipt') }}">Daftar Penerimaan Bank</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail Penerimaan Bank</li>
    </ol>
  </nav>

@stop

@section('content')
<?php 
?>
<h3 class="page-title">
    Detail Penerimaan Bank
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
            Form Detail
        </h5>
        <div class="form-actions float-right">
            <a onclick="location.href='{{ url('bank-receipt') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</a>
        </div>
    </div>

        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-4">
                    <div class="form-group form-md-line-input">
                        <section class="control-label">Nama Penerima
                        </section>
                        <input class="form-control input-bb" type="text" name="check_receipt_date" id="check_receipt_date" value="{{$acctreceiptdetail == null ? '' : $AcctBankReceipt->getCustomerName($acctreceiptdetail['customer_id'])}}" readonly/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group form-md-line-input">
                        <section class="control-label">Tanggal Penerimaan
                        </section>
                        <input class="form-control input-bb" type="text" name="bank_receipt_date" id="bank_receipt_date" value="{{$acctreceiptdetail == null ? '' : $acctreceiptdetail['bank_receipt_date']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-4">
                    <a class="text-dark">No Penerimaan</a>
                    <input class="form-control input-bb" type="text" name="bank_receipt_no" id="bank_receipt_no" value="{{$acctreceiptdetail == null ? '' : $acctreceiptdetail['bank_receipt_no']}}" readonly/>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">No. Perkiraan</a>
                    <input class="form-control input-bb" type="text" name="account_code" id="account_code" value="{{$acctreceiptdetail == null ? '' : $acctreceiptdetail['account_code'].' - '. $acctreceiptdetail['account_name']}}" readonly/>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Judul</a>
                        <input class="form-control input-bb" type="text" name="bank_receipt_title" id="bank_receipt_title" value="{{$acctreceiptdetail == null ? '' : $acctreceiptdetail['bank_receipt_title']}}" readonly/>
                    </div>
                </div>
            </div>	
            <div class = "row form-group">
                <div class="col-md-12 ">
                    <a class="text-dark">Deskripsi</a>
                    <div class="">
                        <textarea rows="3" type="text" class="form-control input-bb" name="bank_receipt_description" onChange="function_elements_add(this.name, this.value);" id="bank_receipt_description"  readonly>{{$acctreceiptdetail == null ? '' : $acctreceiptdetail['bank_receipt_description']}}</textarea>
                    </div>
                </div>
            </div>
            <br/>
            <br/>
            <br/>
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
                            <th style='text-align:center'>No. Perkiraan</th>
                            <th style='text-align:center'>Deskripsi</th>
                            <th style='text-align:center'>Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $bank_receipt_amount_total = 0;
                            if(empty($acctreceiptitem)){
                                echo "<tr><th colspan='5' style='text-align  : center !important;'>Data Kosong</th></tr>";
                            } else {
                                $no =1;
                                foreach ($acctreceiptitem AS $key => $val){
                                    echo"
                                        <tr>
                                            <td style='text-align  : center'>".$no."</td>
                                            <td style='text-align  : left !important;'>".$val['account_code']." - ".$val['account_name']."</td>
                                            <td style='text-align  : left !important;'>".$val['bank_receipt_item_title']."</td>
                                            <td style='text-align  : right !important;'>".$val['bank_receipt_item_amount']."</td>
                                            ";
                                            echo"
                                        </tr>
                                    ";
                                    $bank_receipt_amount_total += $val['bank_receipt_item_amount'];
                                    $no++;
                                }
                            }
                        ?>
                        <tr>
                                <td style='text-align  : center !important;' colspan='3'><b>Total</b>
                                </td>
                                <td class="span1" style='text-align  : right !important;' >
                                    <b><?php echo $bank_receipt_amount_total;?></b>
                                </td>
                                <input type='hidden' name='bank_receipt_amount_total' id='bank_receipt_amount_total' value='<?php echo $bank_receipt_amount_total; ?>'/>
                            </tr>	
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
