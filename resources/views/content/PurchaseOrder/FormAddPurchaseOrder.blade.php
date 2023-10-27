@inject('PurchaseOrder', 'App\Http\Controllers\PurchaseOrderController')
@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />
@section('js')
<script>
    function elements_add(name, value){
        $.ajax({
            type: "POST",
            url : "{{route('elements-add-purchase-order')}}",
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

	function toRp(number) {
		var number = number.toString(), 
		rupiah = number.split('.')[0], 
		cents = (number.split('.')[1] || '') +'00';
		rupiah = rupiah.split('').reverse().join('')
			.replace(/(\d{3}(?!$))/g, '$1.')
			.split('').reverse().join('');
		return rupiah + ',' + cents.slice(0, 2);
	}

	$(document).ready(function(){
        var elements = {!! json_encode($purchaseorderelements) !!};

        if(!elements || elements==''){
            elements = [];
        }

        if(!elements['warehouse_id']){
            $("#warehouse_id").select2("val", "0");
        }
        
        if(!elements['supplier_id']){
            $("#supplier_id").select2("val", "0");
        }
        
        $("#item_category_id").select2("val", "0");
        $("#item_unit_id").select2("val", "0");
        $("#province_id").change(function(){
			var province_id 	= $("#province_id").val();
                $.ajax({
                    type: "POST",
                    url : "{{route('purchase-order-city')}}",
                    dataType: "html",
                    data: {
                        'province_id'	: province_id,
                        '_token'        : '{{csrf_token()}}',
                    },
                    success: function(return_data){ 
					    $('#city_id').html(return_data);
                        console.log(return_data);
                    },
                    error: function(data)
                    {
                        console.log(data);

                    }
                });

		});    
        $("#item_category_id").change(function(){
			var item_category_id 	= $("#item_category_id").val();
                $.ajax({
                    type: "POST",
                    url : "{{route('purchase-order-type')}}",
                    dataType: "html",
                    data: {
                        'item_category_id'			    : item_category_id,
                        '_token'                        : '{{csrf_token()}}',
                    },
                    success: function(return_data){ 
					    $('#item_type_id').html(return_data);
                        console.log(return_data);
                    },
                    error: function(data)
                    {
                        console.log(data);

                    }
                });

		});  

        $("#item_type_id").change(function(){
			var item_type_id 	= $("#item_type_id").val();
                $.ajax({
                    type: "POST",
                    url : "{{route('available-stock-sales-order')}}",
                    dataType: "html",
                    data: {
                        'item_type_id'	: item_type_id,
                        '_token'    : '{{csrf_token()}}',
                    },
                    success: function(return_data){ 
					$('#available_stock').val(return_data);
                        console.log(return_data);
                    },
                    error: function(data)
                    {
                        console.log(data);
                    }
                });
		});   

        $("#item_type_id").change(function(){
			var item_type_id 	= $("#item_type_id").val();
                $.ajax({
                    type: "POST",
                    url : "{{route('select-data-unit')}}",
                    dataType: "html",
                    data: {
                        'item_type_id'	: item_type_id,
                        '_token'        : '{{csrf_token()}}',
                    },
                    success: function(return_data){ 
					$('#item_unit_id').html(return_data);
                        console.log(return_data);
                    $("#item_unit_id").val(return_data);
                        console.log(return_data);        
                    },
                    error: function(data)
                    {
                        console.log(data);

                    }
                });
		});
            
        $("#price").change(function(){
			var price 	                    = $("#price").val();
			var quantity 	                = $("#quantity").val();
            var discount_percentage_item    = $("#discount_percentage_item").val();
            var total_price_after_discount  = $("#subtotal_after_discount_item").val();
            var total_price = price*quantity;
            $("#total_price").val(total_price);
            $("#total_price_view").val(toRp(total_price));

            // if(discount_percentage_item = 0 || discount_percentage_item = null){
            // var total_price_after_discount      =(total_price);
            // var discount_percentage_item		= 0;
            // }


            
            var discount_percentage_item 	= $("#discount_percentage_item").val();
			var total_price 	            = $("#total_price").val();
            var discount_item                = discount_percentage_item * 1/100;
            var discount_amount_item         = discount_item * total_price;
            var total_price_after_discount_item 	        = total_price - discount_amount_item;
            $("#discount_amount_item").val(discount_amount_item);
            console.log(discount_amount_item);
            $("#discount_amount_item_view").val(toRp(discount_amount_item));

            $("#subtotal_after_discount_item").val(total_price_after_discount_item);
            console.log(total_price_after_discount_item);
            $("#subtotal_after_discount_item_view").val(toRp(total_price_after_discount_item));


		});   


        $(document).ready(function(){
            var price 	    = 0;
			var quantity 	= $("#quantity").val();
            var total_price = price*quantity;
            $("#total_price").val(total_price);
            $("#total_price_view").val(toRp(total_price));


        $("#quantity").change(function(){
			var price 	    = $("#price").val();
			var quantity 	= $("#quantity").val();
            var total_price = price*quantity;
            $("#total_price").val(total_price);
            $("#total_price_view").val(toRp(total_price));


            var discount_percentage_item 	= $("#discount_percentage_item").val();
			var total_price 	            = $("#total_price").val();
            var discount_item                = discount_percentage_item * 1/100;
            var discount_amount_item         = discount_item * total_price;
            var total_price_after_discount_item 	        = total_price - discount_amount_item;
            $("#discount_amount_item").val(discount_amount_item);
            console.log(discount_amount_item);
            $("#discount_amount_item_view").val(toRp(discount_amount_item));

            $("#subtotal_after_discount_item").val(total_price_after_discount_item);
            console.log(total_price_after_discount_item);
            $("#subtotal_after_discount_item_view").val(toRp(total_price_after_discount_item));

		}); 
    });

    //add discount
        $(document).ready(function(){
        $("#discount_percentage_item").change(function(){
			var discount_percentage_item 	= $("#discount_percentage_item").val();
			var total_price 	            = $("#total_price").val();
            var discount_item                = discount_percentage_item * 1/100;
            var discount_amount_item         = discount_item * total_price;
            var total_price_after_discount_item 	        = total_price - discount_amount_item;
            $("#discount_amount_item").val(discount_amount_item);
            console.log(discount_amount_item);
            $("#discount_amount_item_view").val(toRp(discount_amount_item));

            $("#subtotal_after_discount_item").val(total_price_after_discount_item);
            console.log(total_price_after_discount_item);
            $("#subtotal_after_discount_item_view").val(toRp(total_price_after_discount_item));

		});
    });

    $(document).ready(function(){
        $("#discount_percentage").change(function(){
			var discount_percentage 	= $("#discount_percentage").val();
			var total_price_all 	        = $("#total_price_all").val();
            var discount                = discount_percentage * 1/100;
            var discount_amount         = discount * total_price_all;
            var total_price_after_discount	        = total_price_all - discount_amount;
            $("#discount_amount").val(discount_amount);
            console.log(discount_amount);
            $("#discount_amount_view").val(toRp(discount_amount));

            $("#subtotal_after_discount").val(total_price_after_discount);
            console.log(total_price_after_discount);
            $("#subtotal_after_discount_view").val(toRp(total_price_after_discount));

		});
    });





        $(document).ready(function(){
            var ppn_in_percentage 	= $("#ppn_in_percentage").val();
			var total_price 	    = $("#total_amount").val();
            var ppn_in_amount         = ppn_in_percentage * total_price / 100;
            var total_price_after_ppn_in = Number(total_price) + ppn_in_amount;
            $("#ppn_in_amount").val(ppn_in_amount);
            $("#ppn_in_amount_view").val(toRp(ppn_in_amount));
            console.log(ppn_in_amount);

            $("#subtotal_after_ppn_in").val(total_price_after_ppn_in);
            console.log(total_price_after_ppn_in);
            $("#subtotal_after_ppn_in_view").val(toRp(total_price_after_ppn_in));



        $("#ppn_in_percentage").change(function(){
			var ppn_in_percentage 	= $("#ppn_in_percentage").val();
			var total_price 	    = $("#total_amount").val();
            var ppn_in_amount         = ppn_in_percentage * total_price / 100;
            var total_price_after_ppn_in = Number(total_price) + ppn_in_amount;
            $("#ppn_in_amount").val(ppn_in_amount);
            $("#ppn_in_amount_view").val(toRp(ppn_in_amount));
            console.log(ppn_in_amount);

            $("#subtotal_after_ppn_in").val(total_price_after_ppn_in);
            console.log(total_price_after_ppn_in);
            $("#subtotal_after_ppn_in_view").val(toRp(total_price_after_ppn_in));

		});
    });
	});
    
    
    function processAddArrayPurchaseOrderItem(){
        var item_category_id	                = document.getElementById("item_category_id").value;
        var item_type_id		                = document.getElementById("item_type_id").value;
        var item_unit_id	                    = document.getElementById("item_unit_id").value;
        var quantity			                = document.getElementById("quantity").value;
        var price			                    = document.getElementById("price").value;
        var total_price			                = document.getElementById("subtotal_after_discount_item").value;
        var discount_percentage_item			= document.getElementById("discount_percentage_item").value;
        var discount_amount_item			    = document.getElementById("discount_amount_item").value;

        
            $.ajax({
                type: "POST",
                url : "{{route('purchase-order-add-array')}}",
                data: {
                    'item_category_id'	            : item_category_id,
                    'item_type_id' 		            : item_type_id, 
                    'item_unit_id' 		            : item_unit_id,
                    'quantity' 			            : quantity,
                    'price' 			            : price,
                    'total_price' 		            : total_price,
                    'discount_percentage_item' 		: discount_percentage_item,
                    'discount_amount_item' 		    : discount_amount_item,
                    '_token'                        : '{{csrf_token()}}'
                },
                success: function(msg){
                    location.reload();
                }
            });

    }

    function addSupplier(){
        var supplier_name 	        = $("#supplier_name").val();
        var province_id 	        = $("#province_id").val();
        var city_id 	            = $("#city_id").val();
        var supplier_address 	    = $("#supplier_address").val();
        var supplier_home_phone 	= $("#supplier_home_phone").val();
        var supplier_mobile_phone1 	= $("#supplier_mobile_phone1").val();
        var supplier_mobile_phone2 	= $("#supplier_mobile_phone2").val();
        var supplier_fax_number 	= $("#supplier_fax_number").val();
        var supplier_email 	        = $("#supplier_email").val();
        var supplier_contact_person = $("#supplier_contact_person").val();
        var supplier_id_number 	    = $("#supplier_id_number").val();
        var supplier_tax_no 	    = $("#supplier_tax_no").val();
        var supplier_payment_terms 	= $("#supplier_payment_terms").val();
        var supplier_remark 	    = $("#supplier_remark").val();
        $.ajax({
            type: "POST",
            url : "{{route('purchase-order-add-supplier')}}",
            dataType: "html",
            data: {
                'supplier_name'	            : supplier_name,
                'province_id'	            : province_id,
                'city_id'	                : city_id,
                'supplier_address'	        : supplier_address,
                'supplier_home_phone'	    : supplier_home_phone,
                'supplier_mobile_phone1'    : supplier_mobile_phone1,
                'supplier_mobile_phone2'	: supplier_mobile_phone2,
                'supplier_fax_number'	    : supplier_fax_number,
                'supplier_email'	        : supplier_email,
                'supplier_contact_person'	: supplier_contact_person,
                'supplier_id_number'	    : supplier_id_number,
                'supplier_tax_no'	        : supplier_tax_no,
                'supplier_payment_terms'	: supplier_payment_terms,
                'supplier_remark'	        : supplier_remark,
                '_token'                    : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                $('#supplier_id').html(return_data);
                $('#cancel_btn_supplier').click();
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }

    function addWarehouse(){
        var warehouse_code 	        = $("#warehouse_code").val();
        var warehouse_name 	        = $("#warehouse_name").val();
        var warehouse_address 	    = $("#warehouse_address").val();
        var warehouse_location_id 	= $("#warehouse_location_id").val();
        var warehouse_phone 	    = $("#warehouse_phone").val();
        var warehouse_remark 	    = $("#warehouse_remark").val();
        $.ajax({
            type: "POST",
            url : "{{route('purchase-order-add-warehouse')}}",
            dataType: "html",
            data: {
                'warehouse_code'	    : warehouse_code,
                'warehouse_name'	    : warehouse_name,
                'warehouse_address'	    : warehouse_address,
                'warehouse_location_id'	: warehouse_location_id,
                'warehouse_phone'	    : warehouse_phone,
                'warehouse_remark'      : warehouse_remark,
                '_token'                : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                $('#warehouse_id').html(return_data);
                $('#cancel_btn_warehouse').click();
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }

    function addCategory(){
        var item_category_name 	= $("#item_category_name").val();
        $.ajax({
            type: "POST",
            url : "{{route('inv-item-add-category')}}",
            dataType: "html",
            data: {
                'item_category_name'	: item_category_name,
                '_token'                : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                $('#item_category_id').html(return_data);
                $('#modal_item_category_id').html(return_data);
                $('#cancel-btn-category').click();
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }

    function addType(){
        var item_type_name 	        = $("#item_type_name").val();
        var item_category_id 	    = $("#modal_item_category_id").val();
        var item_type_expired_time 	= $("#item_type_expired_time").val();
        $.ajax({
            type: "POST",
            url : "{{route('inv-item-add-type')}}",
            dataType: "html",
            data: {
                'item_type_name'	        : item_type_name,
                'item_category_id'	        : item_category_id,
                'item_type_expired_time'	: item_type_expired_time,
                '_token'                    : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                $('#item_type_id').html(return_data);
                $('#cancel-btn-type').click();
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }

    function addUnit(){
        var item_unit_code              = $("#item_unit_code").val();
        var item_unit_name              = $("#item_unit_name").val();
        var item_unit_default_quantity  = $("#item_unit_default_quantity").val();
        var item_unit_remark            = $("#item_unit_remark").val();
        $.ajax({
            type: "POST",
            url : "{{route('inv-item-add-unit')}}",
            dataType: "html",
            data: {
                'item_unit_code'	            : item_unit_code,
                'item_unit_name'	            : item_unit_name,
                'item_unit_default_quantity'	: item_unit_default_quantity,
                'item_unit_remark'	            : item_unit_remark,
                '_token'                        : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                $('#item_unit_id').html(return_data);
                $('#cancel-btn-unit').click();
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
        <li class="breadcrumb-item"><a href="{{ url('purchase-order') }}">Daftar Purchase Order</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Purchase Order</li>
    </ol>
</nav>

@stop

@section('content')

<h3 class="page-title">
    <b>Form Tambah Purchase Order</b>
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
    <div class="card border border-dark" style="margin-top: -1%">
    <div class="card-header border-dark bg-dark">
        <h5 class="mb-0 float-left">
            Form Tambah
        </h5>
        <div class="float-right">
            <button onclick="location.href='{{ url('purchase-order') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-add-purchase-order')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <section class="control-label">Tanggal PO
                        <span class="required text-danger">
                            *
                        </span>
                    </section>
                    <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="purchase_order_date" id="purchase_order_date" onChange="elements_add(this.name, this.value);" value="{{$purchaseorderelements == null ? '' : $purchaseorderelements['purchase_order_date']}}" style="width: 15rem;"/>
                </div>
                <div class="col-md-6">
                    <section class="control-label">Tanggal Pengiriman
                        <span class="required text-danger">
                            *
                        </span>
                    </section>
                    <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="purchase_order_shipment_date" id="purchase_order_shipment_date" onChange="elements_add(this.name, this.value);" value="{{$purchaseorderelements == null ? '' : $purchaseorderelements['purchase_order_shipment_date']}}" style="width: 15rem;"/>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-5">
                    <a class="text-dark">Gudang<a class='red'> *</a></a>
                    {!! Form::select('warehouse_id',  $warehouse, $purchaseorderelements == null ? '' : $purchaseorderelements['warehouse_id'] , ['class' => 'selection-search-clear select-form', 'id' => 'warehouse_id', 'onchange' => 'elements_add(this.name , this.value);']) !!}
                </div>
                <div class="col-md-1" style="margin-top: 0.3%">
                    <a class="text-dark"></a>
                    <a href='#addwarehouse' data-toggle='modal' name="Find" class="btn btn-success add-btn btn-sm" title="Add Data">Tambah</a>
                </div>
                <div class="col-md-5">
                    <a class="text-dark">Pemasok<a class='red'> *</a></a>
                    {!! Form::select('supplier_id',  $supplier, $purchaseorderelements == null ? '' : $purchaseorderelements['supplier_id'], ['class' => 'selection-search-clear select-form', 'id' => 'supplier_id', 'onchange' => 'elements_add(this.name , this.value);']) !!}
                </div>
                <div class="col-md-1" style="margin-top: 0.3%">
                    <a class="text-dark"></a>
                    <a href='#addsupplier' data-toggle='modal' name="Find" class="btn btn-success add-btn btn-sm" title="Add Data">Tambah</a>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12 ">
                    <a class="text-dark">Keterangan</a>
                    <div class="">
                        <textarea rows="3" type="text" class="form-control input-bb" name="purchase_order_remark" onChange="elements_add(this.name, this.value);" id="purchase_order_remark" >{{$purchaseorderelements == null ? '' : $purchaseorderelements['purchase_order_remark']}}</textarea>
                    </div>
                </div>
            </div>
            
            <br/>
            <h4 class="text-dark"><b>Daftar Barang</b></h4>
            <hr/>
            <div class="row form-group">
                <div class="col-md-5">
                    <a class="text-dark">Kategori Barang<a class='red'> *</a></a>
                    {!! Form::select('item_category_id',  $itemcategory, 0, ['class' => 'selection-search-clear select-form', 'id' => 'item_category_id']) !!}
                </div>
                <div class="col-md-1" style="margin-top: 0.3%">
                    <a class="text-dark"></a>
                    <a href='#addcategory' data-toggle='modal' name="Find" class="btn btn-success add-btn btn-sm" title="Add Data">Tambah</a>
                </div>
                <div class="col-md-5">
                    <a class="text-dark">Nama Barang<a class='red'> *</a></a>
                    {!! Form::select('item_type_id',  $itemtype, 0, ['class' => 'selection-search-clear select-form', 'id' => 'item_type_id']) !!}
                </div>
                <div class="col-md-1" style="margin-top: 0.3%">
                    <a class="text-dark"></a>
                    <a href='#addtype' data-toggle='modal' name="Find" class="btn btn-success add-btn btn-sm" title="Add Data">Tambah</a>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-5">
                    <a class="text-dark">Satuan<a class='red'> *</a></a>
                    <select class="selection-search-clear" name="item_unit_id" id="item_unit_id" style="width: 100% !important">
                    </select>
                </div>
                <div class="col-md-1" style="margin-top: 0.3%">
                    <a class="text-dark"></a>
                    <a href='#addunit' data-toggle='modal' name="Find" class="btn btn-success add-btn btn-sm" title="Add Data">Tambah</a>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Quantity</a>
                        <input class="form-control input-bb" type="text" name="quantity" id="quantity" value=""/>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Harga Satuan</a>
                        <input class="form-control input-bb" type="text" name="price" id="price" value=""/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Total Harga</a>
                        <input class="form-control input-bb" type="text" name="total_price_view" id="total_price_view" value="" readonly/>
                        <input class="form-control input-bb" type="hidden" name="total_price" id="total_price" value=""/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Discount/Barang (%)</a>
                        <input class="form-control input-bb" type="text" name="discount_percentage_item" id="discount_percentage_item" />
                    </div>
                </div>
                <div hidden class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Nominal Discount</a>
                        <input class="form-control input-bb" type="hidden" name="discount_amount_item" id="discount_amount_item"  readonly/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Nominal Discount</a>
                        <input class="form-control input-bb" type="text" name="discount_amount_item_view" id="discount_amount_item_view"  readonly/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Total Harga Setelah Discount</a>
                        <input class="form-control input-bb" type="text" name="subtotal_after_discount_item_view" id="subtotal_after_discount_item_view" value="" readonly/>
                        <input class="form-control input-bb" type="hidden" name="subtotal_after_discount_item" id="subtotal_after_discount_item" value=""/>
                    </div>
                </div>


                {{-- <div class="col-md-6">
                    <div class="form-group">
                        <a class="text-dark">Total Harga</a>
                        <input class="form-control input-bb" type="text" name="total_price_view" id="total_price_view" value="" readonly/>
                        <input class="form-control input-bb" type="hidden" name="total_price" id="total_price" value=""/>
                    </div>
                </div> --}}
            </div>
        </div>
        <div class="card-footer text-muted" style="margin-top: -2%">
            <div class="form-actions float-right">
                <a type="submit" name="Save" class="btn btn-primary btn-sm" title="Save" onclick="processAddArrayPurchaseOrderItem()">Tambah</a>
            </div>
        </div>
    </div>

    <div class="card border border-dark" style="margin-top: 2%">
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
                                <th style='text-align:center'>Kategori Barang</th>
                                <th style='text-align:center'>Nama Barang</th>
                                <th style='text-align:center'>Satuan</th>
                                <th style='text-align:center'>Quantity</th>
                                <th style='text-align:center'>Harga Satuan</th>
                                <th style='text-align:center'>Discount</th>
                                <th style='text-align:center'>Nominal Discount</th>
                                <th style='text-align:center'>Total Harga</th>
                                <th style='text-align:center'>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                                @if(!is_array($purchaseorderitem))
                                    <tr><th colspan='8' style='text-align  : center !important;'>Data Kosong</th></tr>
                                @else
                                    @php
                                        $no =1;
                                        $total_price = 0;
                                        $total_item = 0;
                                    @endphp
                                    @foreach ($purchaseorderitem AS $key => $val)
                                        <tr>
                                                <td style='text-align  : center'>{{$no}}</td>
                                                <td style='text-align  : left !important;'>{{$PurchaseOrder->getItemCategoryName($val['item_category_id'])}}</td>
                                                <td style='text-align  : left !important;'>{{$PurchaseOrder->getItemTypeName($val['item_type_id'])}}</td>
                                                <td style='text-align  : left !important;'>{{$PurchaseOrder->getItemUnitName($val['item_unit_id'])}}</td>
                                                <td style='text-align  : right !important;'>{{$val['quantity']}}</td>
                                                <td style='text-align  : right !important;'>{{number_format($val['price'],2,',','.')}}</td>
                                                <td style='text-align  : right !important;'>{{$val['discount_percentage']}} %</td>
                                                <td style='text-align  : right !important;'>{{number_format($val['discount_amount'],2,',','.')}}</td>
                                                <td style='text-align  : right !important;'>{{number_format($val['total_price'],2,',','.')}}</td>
                                                
                                                <td style='text-align  : center'>
                                                    <a href="{{route('purchase-order-delete-array', ['record_id' =>$key])}}" name='Reset' class='btn btn-danger btn-sm' onClick='javascript:return confirm(\"apakah yakin ingin dihapus ?\")'></i> Hapus</a>
                                                </td>
                                                
                                            </tr>
                                            
                                        @php
                                            $no++;
                                            $total_price+=$val['total_price'];
                                            $total_item+=$val['quantity'];    
                                        @endphp
                                        @endforeach
                                        <th style='text-align  : center' colspan='2'>Total</th>
                                        <th style='text-align  : center' colspan=''>:</th>
                                        <th style='text-align  : center' colspan=''></th>
                                        <th style='text-align  : right'>{{$total_item}}</th>
                                        <th style='text-align  : center' colspan='3'></th>
                                        <th style='text-align  : right'>{{number_format($total_price,2,',','.')}}</th>
                                        <th>
                                            <input class='form-control input-bb' type='hidden' name='total_amount' id='total_amount' value='{{$total_price}}'/>
                                            <input class='form-control input-bb' type='hidden' name='total_item_all' id='total_item_all' value='{{$total_item}}'/>
                                        </th>
                                        <tr>
                                            <td style='text-align  : center' colspan='2'><b>PPN Masuk (%)</b></td>
                                            <td style='text-align  : center'><b>:</b></td>
                                            <td colspan='4'></td>
                                            <td>
                                                <input style='text-align  : right' type="text" class="form-control" name="ppn_in_percentage" value="{{ $ppnIn['ppn_amount_in'] }}" id="ppn_in_percentage"></td>
                                            <td>
                                                <input type="hidden" class="form-control" name="ppn_in_amount" id="ppn_in_amount"  readonly>
                                                <input style='text-align  : right;  font-weight: bold;' type="text" class="form-control" name="ppn_in_amount_view" id="ppn_in_amount_view"   readonly>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td style='text-align  : center' colspan='2'><b>Total Harga Akhir </td>
                                            <td style='text-align  : center'><b>:</b></td>
                                            <td colspan='5'></td>
                                            <td style='text-align  : center'>
                                                <input type="hidden" class="form-control" name="subtotal_after_ppn_in" id="subtotal_after_ppn_in" readonly>
                                                <input style='text-align  : right;  font-weight: bold;' type="text" class="form-control" name="subtotal_after_ppn_in_view" id="subtotal_after_ppn_in_view" readonly>
                                            </td>
                                            <td></td>
                                        </tr>
                                @endif
                    
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card-footer text-muted" style="margin-top: -1%">
            <div class="form-actions float-right">
                <button type="reset" name="Reset" class="btn btn-danger btn-sm" onClick="window.location.reload();"><i class="fa fa-times"></i> Batal</button>
                <button type="submit" name="Save" class="btn btn-primary btn-sm" title="Save"><i class="fa fa-check"></i> Simpan</button>
            </div>
        </div>
    </div>
</form>
<br/>

<div class="modal fade bs-modal-lg" id="addsupplier" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"  style='text-align:left !important'>
                <h4>Form Tambah Pemasok</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Nama Pemasok</a>
                            <input class="form-control input-bb" type="text" name="supplier_name" id="supplier_name" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Provinsi<a class='red'> *</a></a>
                            {!! Form::select('province_id',  $coreprovince, 0, ['class' => 'selection-search-clear select-form', 'id' => 'province_id']) !!}
                        </div>
                    </div>
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Kota<a class='red'> *</a></a>
                            {!! Form::select('city_id',  $corecity, 0, ['class' => 'selection-search-clear select-form', 'id' => 'city_id']) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 ">
                        <a class="text-dark">Alamat Pemasok</a>
                        <div class="">
                            <textarea rows="3" type="text" class="form-control input-bb" name="supplier_address" onChange="elements_add(this.name, this.value);" id="supplier_address" ></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Telp. Rumah</a>
                            <input class="form-control input-bb" type="text" name="supplier_home_phone" id="supplier_home_phone" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">No HP 1</a>
                            <input class="form-control input-bb" type="text" name="supplier_mobile_phone1" id="supplier_mobile_phone1" value=""/>
                        </div>
                    </div>	
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">No HP 2</a>
                            <input class="form-control input-bb" type="text" name="supplier_mobile_phone2" id="supplier_mobile_phone2" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">No Fax</a>
                            <input class="form-control input-bb" type="text" name="supplier_fax_number" id="supplier_fax_number" value=""/>
                        </div>
                    </div>	
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Email</a>
                            <input class="form-control input-bb" type="text" name="supplier_email" id="supplier_email" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Contact Person</a>
                            <input class="form-control input-bb" type="text" name="supplier_contact_person" id="supplier_contact_person" value=""/>
                        </div>
                    </div>	
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Nomor ID</a>
                            <input class="form-control input-bb" type="text" name="supplier_id_number" id="supplier_id_number" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">No Tax</a>
                            <input class="form-control input-bb" type="text" name="supplier_tax_no" id="supplier_tax_no" value=""/>
                        </div>
                    </div>	
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Syarat Pembayaran</a>
                            <input class="form-control input-bb" type="text" name="supplier_payment_terms" id="supplier_payment_terms" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-12 ">
                        <a class="text-dark">Keterangan</a>
                        <div class="">
                            <textarea rows="3" type="text" class="form-control input-bb" name="supplier_remark" onChange="elements_add(this.name, this.value);" id="supplier_remark" ></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" id='cancel_btn_supplier'>Batal</button>
                <a class="btn btn-primary btn-sm" onClick="addSupplier()">Simpan</a>
            </div>
        </div>
    </div>
</div>


<div class="modal fade bs-modal-lg" id="addcategory" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"  style='text-align:left !important'>
                <h4>Form Tambah Kategori Barang</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">		
                        <a class="text-dark">Kategori Barang</a>
                        <input class="form-control input-bb" type="text" name="item_category_name" id="item_category_name" value=""/>
                    </div>	
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" id='cancel-btn-category'>Batal</button>
                <a class="btn btn-primary btn-sm" onClick="addCategory()">Simpan</a>
            </div>
        </div>
    </div>
</div>


<div class="modal fade bs-modal-lg" id="addtype" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"  style='text-align:left !important'>
                <h4>Form Tambah Nama Barang</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Nama Nama Barang</a>
                            <input class="form-control input-bb" type="text" name="item_type_name" id="item_type_name" value=""/>
                        </div>
                    </div>	
                    <div class="col-md-5">	
                        <div class="form-group">	
                            <a class="text-dark">Kategori<a class='red'> *</a></a>
                            {!! Form::select('modal_item_category_id',  $itemcategory, 0, ['class' => 'selection-search-clear select-form', 'id' => 'modal_item_category_id']) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Waktu Kadaluarsa (hari)</a>
                            <input class="form-control input-bb" type="text" name="item_type_expired_time" id="item_type_expired_time" value=""/>
                        </div>
                    </div>	
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" id='cancel-btn-type'>Batal</button>
                <a class="btn btn-primary btn-sm" onClick="addType()">Simpan</a>
            </div>
        </div>
    </div>
</div>


<div class="modal fade bs-modal-lg" id="addunit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"  style='text-align:left !important'>
                <h4>Form Tambah Satuan Barang</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Kode Satuan Barang</a>
                            <input class="form-control input-bb" type="text" name="item_unit_code" id="item_unit_code" value=""/>
                        </div>
                    </div>	
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Nama Satuan Barang</a>
                            <input class="form-control input-bb" type="text" name="item_unit_name" id="item_unit_name" value=""/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Default Quantity</a>
                            <input class="form-control input-bb" type="text" name="item_unit_default_quantity" id="item_unit_default_quantity" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Keterangan</a>
                            <input class="form-control input-bb" type="text" name="item_unit_remark" id="item_unit_remark" value=""/>
                        </div>
                    </div>		
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" id='cancel-btn-unit'>Batal</button>
                <a class="btn btn-primary btn-sm" onClick="addUnit()">Simpan</a>
            </div>
        </div>
    </div>
</div>


<div class="modal fade bs-modal-lg" id="addwarehouse" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"  style='text-align:left !important'>
                <h4>Form Tambah Gudang</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Kode Gudang</a>
                            <input class="form-control input-bb" type="text" name="warehouse_code" id="warehouse_code" value=""/>
                        </div>
                    </div>	
                    <div class="col-md-6">
                        <div class="form-group">	
                            <a class="text-dark">Nama Gudang</a>
                            <input class="form-control input-bb" type="text" name="warehouse_name" id="warehouse_name" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-12 ">
                        <a class="text-dark">Alamat</a>
                        <div class="">
                            <textarea rows="3" type="text" class="form-control input-bb" name="warehouse_address" onChange="elements_add(this.name, this.value);" id="warehouse_address" ></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Lokasi<a class='red'> *</a></a>
                            {!! Form::select('warehouse_location_id',  $location, 0, ['class' => 'selection-search-clear select-form', 'id' => 'warehouse_location_id']) !!}
                        </div>
                    </div>
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">No Telp</a>
                            <input class="form-control input-bb" type="text" name="warehouse_phone" id="warehouse_phone" value=""/>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 ">
                        <a class="text-dark">Keterangan</a>
                        <div class="">
                            <textarea rows="3" type="text" class="form-control input-bb" name="warehouse_remark" onChange="elements_add(this.name, this.value);" id="warehouse_remark" ></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-sm" data-dismiss="modal" id='cancel_btn_warehouse'>Batal</button>
                <a class="btn btn-primary btn-sm" onClick="addWarehouse()">Simpan</a>
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