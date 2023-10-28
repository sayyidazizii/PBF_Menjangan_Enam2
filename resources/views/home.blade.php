@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />

<!-- jQuery -->
<script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
<!-- BS JavaScript -->
<script type="text/javascript" src="js/bootstrap.js"></script>
<!-- Have fun using Bootstrap JS -->
<script type="text/javascript">
    $(window).on('load', function() {
        jQuery.noConflict();
        $('#alert').modal('show');

        function close()
        {
            jQuery.noConflict();
            $('#alert').modal('hide');
        }
    });
</script>


{{-- @section('content_header')
    
Dashboard

@stop --}}

@section('content')

    <br>
    <div class="modal fade bs-modal-md text-dark" id="alert" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header"  style='text-align:left !important'>
                    <h4>Notifikasi</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col">
                            <table class="table table-bordered table-hover">
                                <p>Purchase Invoice</p>
                                <tr>
                                    <td>Nomor invoice</td>
                                    <td>Jatuh Tempo</td>
                                </tr>
                                @foreach ($purchaseinvoice as $item)
                                    <tr>
                                        <td>{{ $item->purchase_invoice_no }}</td>
                                        <td>{{ $item->purchase_invoice_due_date }}</td>
                                    </tr>
                                @endforeach
                            </table>
                            {{ $purchaseinvoice->links() }}
                        </div>
                        <div class="col">
                            <table class="table table-bordered table-hover">
                                <p>Sales Invoice</p>
                                <tr>
                                    <td>Nomor invoice</td>
                                    <td>Jatuh Tempo</td>
                                </tr>
                                @foreach ($salesinvoice as $item)
                                    <tr>
                                        <td>{{ $item->sales_invoice_no }}</td>
                                        <td>{{ $item->sales_invoice_due_date }}</td>
                                    </tr>
                                @endforeach
                            </table>
                            {{ $salesinvoice->links() }}
                        </div>
                    </div>
                </div>
                    <div class="modal-footer">
                        <button class="btn btn-default" data-dismiss="modal" aria-label="Close">close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="card border border-dark">
        <div class="card-header border-dark bg-dark">
            <h5 class="mb-0 float-left">
                Menu Utama
            </h5>
        </div>
        <div class="card-body">

            <div class="row">
                <div class='col-md-6'>
                    <div class="card" style="height: 280px;">
                        <div class="card-header bg-secondary">
                            Pembelian
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php foreach($menus as $menu){
                            if($menu['id_menu']==221){
                    ?>
                                <li class="list-group-item main-menu-item"
                                    onClick="location.href='{{ route('purchase-order') }}'"> <i
                                        class="fa fa-angle-right"></i> Purchase Order</li>
                                <?php   }
                            if($menu['id_menu']==222){
                    ?>
                                <li class="list-group-item main-menu-item"
                                    onClick="location.href='{{ route('purchase-order-approval') }}'"> <i
                                        class="fa fa-angle-right"></i> Persetujuan Purchase Order</li>
                                <?php 
                            }
                        } 
                    ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class="card" style="height: 280px;">
                        <div class="card-header bg-info">
                            Gudang
                        </div>
                        <div class="card-body scrollable">
                            <ul class="list-group">
                                <?php foreach($menus as $menu){
                                if($menu['id_menu']==13){
                        ?>
                                <li class="list-group-item main-menu-item-b"
                                    onClick="location.href='{{ route('goods-received-note') }}'"> <i
                                        class="fa fa-angle-right"></i> Penerimaan Barang</li>
                                <?php   }
                                if($menu['id_menu']==124){
                        ?>
                                <li class="list-group-item main-menu-item-b"
                                    onClick="location.href='{{ route('warehouse-transfer') }}'"> <i
                                        class="fa fa-angle-right"></i> Transfer Gudang</li>
                                <?php   }
                                if($menu['id_menu']==125){
                        ?>
                                <li class="list-group-item main-menu-item-b"
                                    onClick="location.href='{{ route('warehouse-transfer-received-note') }}'"> <i
                                        class="fa fa-angle-right"></i> Penerimaan Transfer Gudang</li>
                                <?php   }
                                if($menu['id_menu']==127){
                        ?>
                                <li class="list-group-item main-menu-item-b"
                                    onClick="location.href='{{ route('warehouse-out-requisition') }}'"> <i
                                        class="fa fa-angle-right"></i> Pengeluaran Barang Gudang</li>
                                <?php   }
                                if($menu['id_menu']==128){
                        ?>
                                <li class="list-group-item main-menu-item-b"
                                    onClick="location.href='{{ route('warehouse-out-approval') }}'"> <i
                                        class="fa fa-angle-right"></i> Persetujuan Pengeluaran Barang Gudang</li>
                                <?php   }
                                if($menu['id_menu']==14){
                        ?>
                                <li class="list-group-item main-menu-item-b"
                                    onClick="location.href='{{ route('item-stock') }}'"> <i class="fa fa-angle-right"></i>
                                    Stock Barang</li>
                                <?php 
                                }
                            } 
                        ?>
                            </ul>
                        </div>
                    </div>
                </div>
                {{-- <div class='col-md-4'>
                <div class="card" style="height: 280px;">
                    <div class="card-header bg-secondary">
                    Produksi
                    </div>
                    <div class="card-body">
                    <ul class="list-group">
                    <?php foreach($menus as $menu){
                            if($menu['id_menu']==51){
                    ?>
                        <li class="list-group-item main-menu-item-b" onClick="location.href='{{route('grading')}}'"> <i class="fa fa-angle-right"></i> Grading Barang</li>
                    <?php 
                            }
                        } 
                    ?>     
                    </ul>
                </div>
                </div>
            </div> --}}
            </div>
            <div class="row">
                <div class='col-md-4'>
                    <div class="card" style="height: 280px;">
                        <div class="card-header bg-info">
                            Penjualan
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php foreach($menus as $menu){
                            if($menu['id_menu']==321){
                    ?>
                                <li class="list-group-item main-menu-item"
                                    onClick="location.href='{{ route('sales-order') }}'"> <i class="fa fa-angle-right"></i>
                                    Sales Order</li>
                                <?php   }
                            if($menu['id_menu']==322){
                    ?>
                                <li class="list-group-item main-menu-item"
                                    onClick="location.href='{{ route('sales-order-approval') }}'"> <i
                                        class="fa fa-angle-right"></i> Persetujuan Sales Order</li>
                                <?php   }
                            if($menu['id_menu']==331){
                    ?>
                                <li class="list-group-item main-menu-item"
                                    onClick="location.href='{{ route('sales-invoice') }}'"> <i
                                        class="fa fa-angle-right"></i> Sales Order Invoice</li>
                                <?php 
                            }
                        } 
                    ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class="card" style="height: 280px;">
                        <div class="card-header bg-secondary">
                            Ekspedisi
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php foreach($menus as $menu){
                            if($menu['id_menu']==421){
                    ?>
                                <li class="list-group-item main-menu-item-b"
                                    onClick="location.href='{{ route('sales-delivery-order') }}'"> <i
                                        class="fa fa-angle-right"></i> Sales Delivery Order</li>
                                <?php   }
                            if($menu['id_menu']==422){
                    ?>
                                <li class="list-group-item main-menu-item-b"
                                    onClick="location.href='{{ route('sales-delivery-note') }}'"> <i
                                        class="fa fa-angle-right"></i> Sales Delivery Note</li>
                                <?php 
                            }
                        } 
                    ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class="card" style="height: 280px;">
                        <div class="card-header bg-info">
                            Keuangan
                        </div>
                        <div class="card-body">
                            <ul class="list-group">
                                <?php foreach($menus as $menu){
                            if($menu['id_menu']==611){
                    ?>
                                <li class="list-group-item main-menu-item"
                                    onClick="location.href='{{ route('cash-receipt') }}'"> <i
                                        class="fa fa-angle-right"></i> Penerimaan Kas</li>
                                <?php   }
                            if($menu['id_menu']==612){
                    ?>
                                <li class="list-group-item main-menu-item"
                                    onClick="location.href='{{ route('cash-disbursement') }}'"> <i
                                        class="fa fa-angle-right"></i> Pengeluaran Kas</li>
                                <?php   }
                            if($menu['id_menu']==613){
                    ?>
                                <li class="list-group-item main-menu-item"
                                    onClick="location.href='{{ route('purchase-payment') }}'"> <i
                                        class="fa fa-angle-right"></i> Pelunasan Hutang</li>
                                <?php   }
                            if($menu['id_menu']==614){
                    ?>
                                <li class="list-group-item main-menu-item"
                                    onClick="location.href='{{ route('sales-collection') }}'"> <i
                                        class="fa fa-angle-right"></i> Pelunasan Piutang</li>
                                <?php 
                            }
                        } 
                    ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </form>
    </div>
    <br>
    <br>



    @include('footer')




    <div class="modal fade bs-modal-md " id="addtstock" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md">

            <div class="modal-content">
                <div class="modal-header" style='text-align:left !important'>
                    <h4>Notice Jatuh Tempo</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col">
                            <table border="1">
                                <p>Purchase Invoice</p>
                                <tr>
                                    <td>Nomor invoice</td>
                                    <td>Jatuh Tempo</td>
                                </tr>
                                @foreach ($purchaseinvoice as $item)
                                    <tr>
                                        <td>{{ $item->purchase_invoice_no }}</td>
                                        <td>{{ $item->purchase_invoice_due_date }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                        <div class="col">
                            <table border="1">
                                <p>Sales Invoice</p>
                                <tr>
                                    <td>Nomor invoice</td>
                                    <td>Jatuh Tempo</td>
                                </tr>
                                @foreach ($salesinvoice as $item)
                                    <tr>
                                        <td>{{ $item->sales_invoice_no }}</td>
                                        <td>{{ $item->sales_invoice_due_date }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                    <br>
                    <div class="modal-footer">
                        {{-- <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm" style="margin-right: -3%">Tambah</button> --}}
                    </div>
                </div>
            </div>
        </div>
    @stop

    @section('css')

    @stop

    @section('js')

    @stop
