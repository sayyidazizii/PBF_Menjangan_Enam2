@inject('AcctBankReceipt', 'App\Http\Controllers\AcctBankReceiptController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>
    
	$(document).ready(function(){
        $("#account_id_item").select2("val", "0");
        
        var elements = {!! json_encode($acctreceiptelements) !!};
        
        if(!elements || elements==''){
            elements = [];
        }
        
        if(!elements['account_id']){
            $("#account_id").select2("val", "0");
        }

        if(!elements['customer_id']){
            $("#customer_id").select2("val", "0");
        }
    
        if(!elements['bank_receipt_title']){
            $("#bank_receipt_title").val('');
        }
    });
    
    function function_elements_add(name, value){
        $.ajax({
            type: "POST",
            url : "{{route('elements-add-bank-receipt')}}",
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

    function changeDate(name, value){
        var bank_receipt_date    =   document.getElementById("bank_receipt_date").value;
        $('#receipt_date_table').html(bank_receipt_date);

        function_elements_add(name, value);
    }
    
	function processAddArrayAcctReceiptItem(){
		var account_id_item					= document.getElementById("account_id_item").value;
		var bank_receipt_item_amount	    = document.getElementById("bank_receipt_item_amount").value;
		var bank_receipt_item_title		    = document.getElementById("bank_receipt_item_title").value;
        console.log(account_id_item);
        console.log(bank_receipt_item_amount);
        console.log(bank_receipt_item_title);

		
        $.ajax({
        type: "POST",
        url : "{{route('add-bank-receipt-array')}}",
        data: {
            'account_id_item'					    : account_id_item,
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
        <li class="breadcrumb-item active" aria-current="page">Tambah Penerimaan Bank</li>
    </ol>
</nav>

@stop

@section('content')
<?php 
$project_type_id = Session::get('receiptprojecttype');
?>
<form method="post" action="{{route('process-add-bank-receipt')}}" enctype="multipart/form-data">
    @csrf
<h3 class="page-title">
    <b>Form Tambah Penerimaan Bank</b>
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
        <div class="form-actions float-right">
            <a onclick="location.href='{{ url('bank-receipt') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</a>
        </div>
    </div>

        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">Nama Pelanggan<a class='red'> *</a></a>
                    {!! Form::select('customer_id',  $corecustomer, $acctreceiptelements == null ? '' :  $acctreceiptelements['customer_id'], ['class' => 'selection-search-clear select-form', 'id' => 'customer_id', 'onchange' => 'function_elements_add(this.name, this.value)']) !!}
                </div>
                <div class="col-md-6">
                    <a class="text-dark">Tanggal Penerimaan<a class='red'> *</a></a>
                    <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="bank_receipt_date" id="bank_receipt_date" onChange="changeDate(this.name, this.value);" value="{{$acctreceiptelements == null ? '' : $acctreceiptelements['bank_receipt_date']}}" style="width: 15rem;"/>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">No. Perkiraan</a>
                    {!! Form::select('account_id',  $acctaccount, $acctreceiptelements == null ? '' :  $acctreceiptelements['account_id'], ['class' => 'selection-search-clear select-form', 'id' => 'account_id', 'onchange' => 'function_elements_add(this.name, this.value)']) !!}
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Judul</a>
                        <input class="form-control input-bb" type="text" name="bank_receipt_title" id="bank_receipt_title" onChange="function_elements_add(this.name, this.value)" value="{{$acctreceiptelements == null ? '' : $acctreceiptelements['bank_receipt_title']}}"/>
                    </div>
                </div>
            </div>	
            <div class = "row form-group">
                <div class="col-md-12 ">
                    <a class="text-dark">Deskripsi</a>
                    <div class="">
                        <textarea rows="3" type="text" class="form-control input-bb" name="bank_receipt_description" onChange="function_elements_add(this.name, this.value);" id="bank_receipt_description" >{{$acctreceiptelements == null ? '' : $acctreceiptelements['bank_receipt_description']}}</textarea>
                    </div>
                </div>
            </div>
            <br/>
            <br/>
            <br/>
            <div class="row">
                <h5 class="form-section"><b>Detail Data Penerimaan</b></h5>
            </div>
            <hr style="margin:0;">
            <br/>
            
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">No. Perkiraan</a>
                    {!! Form::select('account_id_item',  $acctaccount, 0, ['class' => 'selection-search-clear select-form', 'id' => 'account_id_item']) !!}
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Judul</a>
                        <input class="form-control input-bb" type="text" name="bank_receipt_item_title" id="bank_receipt_item_title" value=""/>
                    </div>
                </div>
            </div>
                        
            <div class = "row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Jumlah</a>
                        <input class="form-control input-bb" type="text" name="bank_receipt_item_amount" id="bank_receipt_item_amount" value=""/>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <a name="Save" class="btn btn-primary btn-sm" title="Save" onclick='processAddArrayAcctReceiptItem()'>Tambah</a>
            </div>
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
                            <th style='text-align:center'>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $bank_receipt_amount_total = 0;
                            if(!is_array($acctreceiptitem)){
                                echo "<tr><th colspan='5' style='text-align  : center !important;'>Data Kosong</th></tr>";
                            } else {
                                $no =1;
                                foreach ($acctreceiptitem AS $key => $val){
                                    echo"
                                        <tr>
                                            <td style='text-align  : center'>".$no."</td>
                                            <td style='text-align  : left !important;'>".$AcctBankReceipt->getAccountName($val['account_id_item'])."</td>
                                            <td style='text-align  : left !important;'>".$val['bank_receipt_item_title']."</td>
                                            <td style='text-align  : right !important;'>".$val['bank_receipt_item_amount']."</td>";
                                        ?>
                                            <td style='text-align  : center'>
                                                <a href="{{route('delete-bank-receipt-array', ['record_id' => $key])}}" name='Reset' class='btn btn-danger btn-sm' onClick='javascript:return confirm(\"apakah yakin ingin dihapus ?\")'></i> Hapus</a>
                                            </td>
                                            <?php
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
                                <td style='text-align  : center !important;'>
                                </td>
                                <input type='hidden' name='bank_receipt_amount_total' id='bank_receipt_amount_total' value='<?php echo $bank_receipt_amount_total; ?>'/>
                            </tr>	
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="card-footer text-muted">
        <div class="form-actions float-right">
            <button type="reset" name="Reset" class="btn btn-danger btn-sm" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
            <button type="submit" name="Save" class="btn btn-primary btn-sm" title="Save"><i class="fa fa-check"></i> Simpan</button>
        </div>
    </div>
</div>
</form>
<br>
<br>

@include('footer')

@stop

@section('css')
    
@stop
