@inject('AcctDisbursement', 'App\Http\Controllers\AcctDisbursementController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>
    function changeDate(name, value){
        var disbursement_date    =   document.getElementById("disbursement_date").value;
        $('#disbursement_date_table').html(disbursement_date);
    }
    
	function processAddArrayAcctDisbursementItem(){
		var account_id_item					= document.getElementById("account_id_item").value;
		var disbursement_item_amount		= document.getElementById("disbursement_item_amount").value;
		var disbursement_item_title			= document.getElementById("disbursement_item_title").value;
        console.log(account_id_item);
        console.log(disbursement_item_amount);
        console.log(disbursement_item_title);

		
        $.ajax({
        type: "POST",
        url : "{{route('add-disbursement-array')}}",
        data: {
            'account_id_item'					: account_id_item,
            'disbursement_item_amount' 			: disbursement_item_amount, 
            'disbursement_item_title' 			: disbursement_item_title,
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
        <li class="breadcrumb-item"><a href="{{ url('disbursement') }}">Daftar Pengeluaran Kas dan Bank</a></li>
        <li class="breadcrumb-item active" aria-current="page">Detail Pengeluaran Kas dan Bank</li>
    </ol>
  </nav>

@stop

@section('content')
<?php 
?>
<h3 class="page-title">
    Detail Pengeluaran Kas dan Bank
</h3>
<br/>
@if(session('msg'))
<div class="alert alert-info" role="alert">
    {{session('msg')}}
</div>
@endif

<form method="post" action="{{route('process-void-disbursement')}}" enctype="multipart/form-data">
    @csrf
    <div class="card border border-dark">
    <div class="card-header border-dark bg-dark">
        <h5 class="mb-0 float-left">
            Form Detail
        </h5>
        <div class="form-actions float-right">
            <a onclick="location.href='{{ url('disbursement') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</a>
        </div>
    </div>

        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group form-md-line-input">
                        <section class="control-label">Tanggal Pengeluaran
                        </section>
                        <input class="form-control input-bb" type="text" name="disbursement_date" id="disbursement_date" value="{{$acctdisbursementdetail['disbursement_date']}}" readonly/>
                    </div>
                </div>
                <div class="col-md-6">
                    <a class="text-dark">No Pengeluaran</a>
                    <input class="form-control input-bb" type="text" name="disbursement_no" id="disbursement_no" value="{{$acctdisbursementdetail['disbursement_no']}}" readonly/>
                    <input class="form-control input-bb" type="hidden" name="disbursement_id" id="disbursement_id" value="{{$acctdisbursementdetail['disbursement_id']}}" readonly/>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">No. Perkiraan</a>
                    <input class="form-control input-bb" type="text" name="account_code" id="account_code" value="{{$acctdisbursementdetail['account_code'].' - '. $acctdisbursementdetail['account_name']}}" readonly/>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Judul</a>
                        <input class="form-control input-bb" type="text" name="disbursement_title" id="disbursement_title" value="{{$acctdisbursementdetail['disbursement_title']}}" readonly/>
                    </div>
                </div>
            </div>	
            <div class = "row form-group">
                <div class="col-md-12 ">
                    <a class="text-dark">Deskripsi</a>
                    <div class="">
                        <textarea rows="3" type="text" class="form-control input-bb" name="disbursement_description" onChange="function_elements_add(this.name, this.value);" id="disbursement_description"  readonly>{{$acctdisbursementdetail['disbursement_description']}}</textarea>
                    </div>
                </div>
            </div>
            <br/>
            <br/>
            <br/>
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
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            $disbursement_amount_total = 0;
                            if(empty($acctdisbursementitem)){
                                echo "<tr><th colspan='5' style='text-align  : center !important;'>Data Kosong</th></tr>";
                            } else {
                                $no =1;
                                foreach ($acctdisbursementitem AS $key => $val){
                                    echo"
                                        <tr>
                                            <td style='text-align  : center'>".$no."</td>
                                            <td style='text-align  : left !important;'>".$AcctDisbursement->getAccountName($val['account_id_item'])."</td>
                                            <td style='text-align  : left !important;'>".$val['disbursement_item_title']."</td>
                                            <td style='text-align  : right !important;'>".$val['disbursement_item_amount']."</td>
                                            ";
                                            echo"
                                        </tr>
                                    ";
                                    $disbursement_amount_total += $val['disbursement_item_amount'];
                                    $no++;
                                }
                            }
                        ?>
                        <tr>
                                <td style='text-align  : center !important;' colspan='3'><b>Total</b>
                                </td>
                                <td class="span1" style='text-align  : right !important;' >
                                    <b><?php echo $disbursement_amount_total;?></b>
                                </td>
                                <input type='hidden' name='disbursement_amount_total' id='disbursement_amount_total' value='<?php echo $disbursement_amount_total; ?>'/>
                            </tr>	
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card-footer text-muted">
        <div class="form-actions float-right">
            <button type="reset" name="Reset" class="btn btn-danger" href='#remark' data-toggle='modal' ><i class="fa fa-times"></i> Batal</button>
        </div>
    </div>
    
</div>

    <div class="modal fade bs-modal-lg" id="remark" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"  style='text-align:left !important'>
                    <h4>Pembatalan Pengeluaran Kas dan Bank</h4>
                </div>
                
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">	
                            <a class="text-dark">Keterangan</a>
                            <div class="">
                                <textarea rows="3" type="text" class="form-control input-bb" name="voided_remark" onChange="function_elements_add(this.name, this.value);" id="voided_remark"></textarea>
                            </div>	
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>
@stop

@section('footer')
    
@stop

@section('css')
    
@stop
