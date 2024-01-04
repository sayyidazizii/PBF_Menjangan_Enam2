@inject('SystemUser', 'App\Http\Controllers\SystemUserController')

@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

@section('content_header')
    
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
      <li class="breadcrumb-item active" aria-current="page">Daftar Debug log</li>
    </ol>
  </nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Daftar Debug log</b> <small>Mengelola Debug log </small>
</h3>
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
        </div>
    </div>

    <div class="card-body">
        @php
            $permissions = array(
                '102'   => 'dashboard',
                '103'   => 'customer',
                '104'   => 'deposit',
                '105'   => 'withdrawal',
                '106'   => 'transfer',
                '107'   => 'other_transaction',
                '108'   => 'report_bank_account',
                '109'   => 'customer_transaction',
                '110'   => 'report_promotion',
                '111'   => 'report_store',
                '112'   => 'store_transaction',
                '113'   => 'deposit_withdrawal',
                '114'   => 'role',
                '115'   => 'user',
                '116'   => 'store',
                '117'   => 'bank_name',
                '118'   => 'bank_account',
                '119'   => 'user_log',
                '120'   => 'promotion',
                '121'   => 'archive',
                '122'   => 'setting'
            );

            //Contoh admin dengan role_id 1 memiliki activity id di bawah ini 

            //activity id admin
            $roleActivityMappings = array(
                1 => 102,
                2 => 103,
                3 => 104
            );

                foreach ($roleActivityMappings as $key => $value) {
                    $activityId = $roleActivityMappings[$key];
                    $activityName = $permissions[$activityId];
                    echo "
                        <table>
                        <tr>
                            <td>$activityName</td>
                        </tr>
                        </table> ";
                }
        @endphp
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