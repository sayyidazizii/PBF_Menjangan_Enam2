@inject('PORR', 'App\Http\Controllers\PurchaseOrderReturnReportController')

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Print</title>
    <link rel=”stylesheet” href="https://maxcdn.bootstrapcdn.com/bootstrap /3.3.7/css/bootstrap.min.css ">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>

<body>
    <table class="" cellspacing="0" border="0">
        <thead>
            <tr>
                <td colspan="2" style="text-align:center;">
                    <div style="font-weight:bold">Laporan Retur Pembelian
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="text-align:center;">
                    <div>

                    </div>
                </td>
            </tr>
        </thead>
    </table>
    <table cellspacing="0" cellpadding="0" border="0">
        <tr>
            <td>
                <table cellspacing="0" cellpadding="0" border="0">
                    <tr style="text-align: left;">
                        <td>Customer </td>
                        <td colspan="4">: PBF KOP MENJANGAN ENAM</td>
                        <td></td>
                    </tr>
                    <tr style="text-align: left;">
                        <td>Periode</td>
                        <td width="100%">: {{ $start_date }} - {{ $end_date }} </td>
                    </tr>
                </table>
            </td>
            <td></td>
        </tr>

    </table>
    <table class="" cellspacing="0" border="1">
        <tbody>
            <tr style='text-align:center'>
                <th style="text-align: center;" width="5%">No</th>
                <th style="text-align: center;" width="20%">Purchase Order Return No</th>
                <th style="text-align: center;" width="15%">No Faktur Pajak</th>
                <th style="text-align: center;" width="15%">Nomor Batch</th>
                <th style="text-align: center;" width="8%">Qty</th>
                <th style="text-align: center;" width="8%">Harga</th>
                <th style="text-align: center;" width="8%">Discount</th>
                <th style="text-align: center;" width="11%">Jumlah</th>
                <th style="text-align: center;" width="11%">ED</th>
            </tr>
            @php
                $path = 'resources/assets/img/ttd.png';
                $total = 0;
                $no = 1;
                if (empty($purchaseorderreturn)) {
                    echo "
                                <tr>
                                    <td colspan='6' align='center'>Data Kosong</td>
                                </tr>
                            ";
                } else {
                    foreach ($purchaseorderreturn as $key => $val) {
                        $amount = $val['quantity_return'] * $PORR->getCost($val['purchase_order_item_id']);
                        $jumlah = $amount - $PORR->getDiscountAmount($val['purchase_order_item_id']);
                        $total += $jumlah;
                        echo "
                                <tr>
                                    <td style=\"text-align:center;\" >$no </td>
                                    <td style=\"text-align:right;\" >" .
                            $val['purchase_order_return_no'] .
                            " </td>
                                    <td style=\"text-align:right;\" >" .
                            $val['faktur_tax_no'] .
                            " </td>
                                    <td style=\"text-align:right;\" >" .
                            $val['item_batch_number'] .
                            " </td>
                                    <td style=\"text-align:right;\" >" .
                            $val['quantity_return'] .
                            " </td>
                                    <td style=\"text-align:right;\" >" .
                            $PORR->getCost($val['purchase_order_item_id']) .
                            " </td>
                                    <td style=\"text-align:right;\" >" .
                            $PORR->getDiscount($val['purchase_order_item_id']) .
                            " % </td>
                                    <td style=\"text-align:right;\" >" .
                            $jumlah .
                            " </td>
                                    <td style=\"text-align:center;\" >" .
                            $val['item_expired_date'] .
                            " </td>
                                </tr>
                            ";
                        $no++;
                    }

                    echo "
                        <tr>
                            <td colspan=\"7\" style=\"text-align: center;font-weight: bold\";>Total</td>
                            <td style=\"text-align: right;\";>".$total."</td>
                            <td></td>
                        </tr>
                    ";
                }
            @endphp

        </tbody>
    </table>

    {{-- <table style="text-align: left;" cellspacing="20";>
        <tr>
            <th width="25%">Semarang , {{ date('d M Y') }} Hormat Kami</th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <th><img width="60"; height="60"; src="{{ $path }}"></th>
            <th></th>
            <th></th>
        </tr>
        <tr>
            <th>Isti Ramadhani S.Farm.,Apt</th>
            <th></th>
            <th></th>
        </tr>
    </table> --}}

    @section('css')

    @stop

    @section('js')

    @stop
</body>

</html>
