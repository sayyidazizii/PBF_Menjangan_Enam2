@inject('AcctJournalVoucher', 'App\Http\Controllers\AcctJournalVoucherController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('js')
<script>
	$(document).ready(function(){
        var elements = {!! json_encode($acctjournalvoucherelements) !!};

        if(!elements || elements==''){
            elements = [];
        }
    });
    
    function function_elements_add(name, value){
        $.ajax({
            type: "POST",
            url : "{{route('elements-add-journal')}}",
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

</script>
@stop

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('journal') }}">Daftar Jurnal Umum</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Jurnal Umum</li>
    </ol>
  </nav>

@stop

@section('content')

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
            Form Tambah Jurnal Umum
        </h5>
        <div class="float-right">
            <button onclick="location.href='{{ url('journal') }}'" name="back" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('add-journal-array')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            {{-- <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">Nama Proyek</a>
                    <select class="selection-search-clear" name="project_id" style="width: 100% !important">
                        @foreach($coreproject as $project)
                            <option value="{{$project['project_id']}}">{{$project['project_name']}}</option>
                        @endforeach
                    </select>
                </div>
            </div> --}}
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group form-md-line-input">
                        <section class="control-label">Tanggal
                        </section>
                        <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="journal_voucher_date" id="journal_voucher_date" onChange="function_elements_add(this.name, this.value);" value="{{$acctjournalvoucherelements == null ? '' : $acctjournalvoucherelements['journal_voucher_date']}}" style="width: 15rem;"/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Uraian</a>
                        <input class="form-control input-bb" type="text" name="journal_voucher_description" id="journal_voucher_description" onChange="function_elements_add(this.name, this.value);" value="{{$acctjournalvoucherelements == null ? '' : $acctjournalvoucherelements['journal_voucher_description']}}"/>
                    </div>
                </div>
            </div>
            <br/>
            <h3 class="page-title">
                Detail Jurnal Umum
            </h3>
            <div class="row form-group">
                <div class="col-md-6">
                    <a class="text-dark">No. Perkiraan</a>
                    <select class="selection-search-clear" name="account_id" style="width: 100% !important">
                        @foreach($acctaccount as $code)
                            <option value="{{$code['account_id']}}">{{$code['account_code']}}-{{$code['account_name']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>	
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Jumlah</a>
                        <input class="form-control input-bb" type="text" name="journal_voucher_amount" id="journal_voucher_amount" value=""/>
                    </div>
                </div>
                <div class="col-md-6">
                    <a class="text-dark">Debet / Kredit</a>
                    <select class="selection-search-clear" name="journal_voucher_status" style="width: 100% !important">
                        @foreach($accountstatus as $status)
                            <option value="{{$status['account_default_status']}}">{{$status['account_default_name']}}</option>
                        @endforeach
                    </select>
                </div>
            </div>	
            <div class = "row form-group">
                <div class="col-md-12 ">
                    <a class="text-dark">Deskripsi</a>
                    <div class="">
                        <textarea rows="3" type="text" class="form-control input-bb" name="journal_voucher_description_item" id="journal_voucher_description_item" onChange="function_elements_add(this.name, this.value);" ></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <button type='submit' name="Save" class="btn btn-primary" title="Save">Add</button>
            </div>
        </div>
    </form>
    </div>
    <div class="card border border-dark">
        <div class="card-header border-dark bg-dark">
            <h5 class="mb-0 float-left">
                Form Tambah Jurnal Umum
            </h5>
        </div>
        <form method="post" action="{{route('process-add-journal')}}" enctype="multipart/form-data">
        @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover table-full-width">
                                <thead>
                                    <tr>
                                        <td width="10%">Tanggal</td>
                                        <td width="20%">No. Perkiraan</td>
                                        <td width="30%">Nama Perkiraan</td>
                                        <td width="15%">Debit</td>
                                        <td width="15%">Kredit</td>
                                        <td width="10%">Aksi</td>
                                    </tr>
                                </thead>
                                <tbody>

                                <?php
                                print_r($acctjournalvoucherelements);
                                    if(empty($acctjournalvoucheritem)){
                                    } else {
                                        foreach ($acctjournalvoucheritem as $key => $val) {
                                            // print_r($acctjournalvoucheritem);
                                            // echo"<br/>";
                                            echo "
                                            <tr>
                                                <td>".$val['journal_voucher_date']."</td>
                                                <td>".$AcctJournalVoucher->getAccountCode($val['account_id'])."</td>
                                                <td>".$AcctJournalVoucher->getAccountName($val['account_id'])."</td>";
                                                if($val['journal_voucher_status']==1){
                                                echo"
                                                    <td style='text-align:right'>".number_format($val['journal_voucher_amount'], 2)."</td>
                                                    <td></td>";
                                                } else {
                                                    echo"
                                                    <td></td>
                                                    <td style='text-align:right'>".number_format($val['journal_voucher_amount'], 2)."</td>";
                                                }
                                                ?>
                                                <td>
                                                    <a href="{{route('delete-journal-array', ['record_id' => $key])}}" name='Reset' class='btn btn-danger btn-sm' onClick='javascript:return confirm(\"apakah yakin ingin dihapus ?\")'></i> Hapus</a>
                                                </td>
                                                <?php 
                                                echo"
                                            </tr>
                                            ";
                                        }
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-muted">
                <div class="form-actions float-right">
                    <button type="reset" name="Reset" class="btn btn-danger" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
                    <button type="submit" name="Find" class="btn btn-primary" title="Search Data"><i class="fa fa-check"></i> Simpan</button>
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