<footer class="main-footer text-center">
    {{-- @yield('footer') --}}
    Copyright © 2023 Cipta Solutindo Tech.
</footer>
<?php 
use Carbon\Carbon;  
use App\Models\PurchaseInvoice;
use App\Models\SalesInvoice;
    $startDate = Carbon::today();
    $endDate = Carbon::today()->addDays(7);

    $purchaseinvoice = PurchaseInvoice::select('*')
    ->whereBetween('purchase_invoice_due_date' ,[$startDate, $endDate])
    ->simplePaginate(3);

    $salesinvoice = SalesInvoice::select('*')
    ->whereBetween('sales_invoice_due_date' ,[$startDate, $endDate])
    ->simplePaginate(3);

    $countPurchaseInv = count($purchaseinvoice);
    $countinvoiceInv = count($salesinvoice);

    $Count =  $countPurchaseInv + $countinvoiceInv;
    // var_dump($Count);
?>


<div class="modal fade bs-modal-md " id="addtstock" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md">

        <div class="modal-content">
            <div class="modal-header" style='text-align:left !important'>
                <h4>Notifikasi Jatuh Tempo</h4>
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