@inject('SalesOrder', 'App\Http\Controllers\SalesOrderController')
@extends('adminlte::page')

@section('title', 'PBF | Koperasi Menjangan Enam')
<link rel="shortcut icon" href="{{ asset('resources/assets/logo_pbf.ico') }}" />
@section('js')
<script>
    function elements_add(name, value){
        $.ajax({
            type: "POST",
            url : "{{route('elements-add-sales-order')}}",
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
        

        var elements = {!! json_encode($salesorderelements) !!};

        if(!elements || elements==''){
            elements = [];
        }

        if(!elements['warehouse_id']){
            $("#warehouse_id").select2("val", "0");
        }

        if(!elements['item_type_id']){
            $("#item_type_id").select2("val", "0");
        }
        
        if(!elements['customer_id']){
            $("#customer_id").select2("val", "0");
        }

        // if(!elements['purchase_order_no']){
        //     $("#purchase_order_no").select2("val", "0");
        // }
        // if(!elements['purchase_order_due_date']){
        //     $("#purchase_order_due_date").select2("val", "0");
        // }


        // $("#item_stock_id").select2("val", "0");
        // $("#item_type_id").change(function(){
		// 	var item_type_id 	= $("#item_type_id").val();
        //         $.ajax({
        //             type: "POST",
        //             url : "{{route('select-data-stock-sales-order')}}",
        //             dataType: "html",
        //             data: {
        //                 'item_type_id'	: item_type_id,
        //                 '_token'        : '{{csrf_token()}}',
        //             },
        //             success: function(return_data){ 
		// 			$('#item_stock_id').html(return_data);
        //                 console.log(return_data);
        //             },
        //             error: function(data)
        //             {
        //                 console.log(data);

        //             }
        //         });
		// });

        
        if(!elements['sales_order_type_id']){
            $("#sales_order_type_id").select2("val", "0");
            document.getElementById("receipt-image").style.visibility = "hidden";
        }else{
            if(elements['sales_order_type_id']==1){
                document.getElementById("receipt-image").style.visibility = "visible";
            }else{
                document.getElementById("receipt-image").style.visibility = "hidden";
            }
        }

        $("#item_category_id").select2("val", "0");
        $("#item_unit_id").select2("val", "0");

        $("#sales_order_type_id").change(function(){
			var sales_order_type_id 	    = $("#sales_order_type_id").val();

            if(sales_order_type_id==1){
                document.getElementById("receipt-image").style.visibility = "visible";
            }else{
                document.getElementById("receipt-image").style.visibility = "hidden";
            }

		});
        $("#price").change(function(){
			var price 	    = $("#price").val();
			var quantity 	= $("#quantity").val();
            var total_price = price*quantity;
            $("#total_price").val(total_price);
            $("#total_price_view").val(toRp(total_price));
            var discount_percentage_item 	                = $("#discount_percentage_item").val();
            var discount_percentage_item_b 	                = $("#discount_percentage_item_b").val();
			var total_price 	                            = $("#total_price").val();
            var discount_percentage_item 	                = $("#discount_percentage_item").val();
            var discount_percentage_item_b 	                = $("#discount_percentage_item_b").val();
            var ppn_out_percentage 	                        = $("#ppn_out_percentage").val();

            var discount_item                               = discount_percentage_item * 1/100;
            var discount_item_b                             = discount_percentage_item_b * 1/100;
            var ppn_item                                    = ppn_out_percentage * 1/100;
            var discount_amount_item                        = discount_item * total_price;
            var total_price_after_discount_item_a 	        = total_price - discount_amount_item;
            var discount_amount_item_b                      = discount_item_b * total_price_after_discount_item_a;
            var total_price_after_discount_item_b 	        = total_price_after_discount_item_a - discount_amount_item_b;
            var ppn_amount_item                             = ppn_item * total_price_after_discount_item_b;
            var total_price_after_ppn_amount                = ppn_amount_item + total_price_after_discount_item_b;

            $("#discount_amount_item").val(discount_amount_item);
            $("#discount_amount_item_b").val(discount_amount_item_b);
            console.log(discount_amount_item);
            console.log(discount_amount_item_b);
            $("#discount_amount_item_view").val(toRp(discount_amount_item + discount_amount_item_b));
            $("#subtotal_after_discount_item_a").val(total_price_after_discount_item_a);
            console.log(total_price_after_discount_item_a);
            $("#subtotal_after_discount_item_view_a").val(toRp(total_price_after_discount_item_a));
            $("#subtotal_after_discount_item_b").val(total_price_after_discount_item_b);
            console.log(total_price_after_discount_item_b);
            $("#subtotal_after_discount_item_view_b").val(toRp(total_price_after_discount_item_b));

            $("#total_price_after_ppn_amount_view").val(total_price_after_ppn_amount);
            $("#total_price_after_ppn_amount").val(total_price_after_ppn_amount);
            $("#ppn_amount_item").val(ppn_amount_item);
            console.log(ppn_amount_item);
		});   

        $(document).ready(function(){
            var price 	    = 0;
			var quantity 	= $("#quantity").val();
            var total_price = price*quantity;
            var ppn_amount_item = 0;

            $("#total_price").val(total_price);
            $("#total_price_view").val(toRp(total_price));

            
        $("#quantity").change(function(){
			var price 	    = $("#price").val();
			var quantity 	= $("#quantity").val();
            var total_price = price*quantity;
            $("#total_price").val(total_price);
            $("#total_price_view").val(toRp(total_price));
            var discount_percentage_item 	                = $("#discount_percentage_item").val();
            var discount_percentage_item_b 	                = $("#discount_percentage_item_b").val();
			var total_price 	                            = $("#total_price").val();
            var discount_percentage_item 	                = $("#discount_percentage_item").val();
            var discount_percentage_item_b 	                = $("#discount_percentage_item_b").val();
            var ppn_out_percentage 	                        = $("#ppn_out_percentage").val();

            var discount_item                               = discount_percentage_item * 1/100;
            var discount_item_b                             = discount_percentage_item_b * 1/100;
            var ppn_item                                    = ppn_out_percentage * 1/100;
            var discount_amount_item                        = discount_item * total_price;
            var total_price_after_discount_item_a 	        = total_price - discount_amount_item;
            var discount_amount_item_b                      = discount_item_b * total_price_after_discount_item_a;
            var total_price_after_discount_item_b 	        = total_price_after_discount_item_a - discount_amount_item_b;
            var ppn_amount_item                             = ppn_item * total_price_after_discount_item_b;
            var total_price_after_ppn_amount                = ppn_amount_item + total_price_after_discount_item_b;

            $("#discount_amount_item").val(discount_amount_item);
            $("#discount_amount_item_b").val(discount_amount_item_b);
            console.log(discount_amount_item);
            console.log(discount_amount_item_b);
            $("#discount_amount_item_view").val(toRp(discount_amount_item + discount_amount_item_b));
            $("#subtotal_after_discount_item_a").val(total_price_after_discount_item_a);
            console.log(total_price_after_discount_item_a);
            $("#subtotal_after_discount_item_view_a").val(toRp(total_price_after_discount_item_a));
            $("#subtotal_after_discount_item_b").val(total_price_after_discount_item_b);
            console.log(total_price_after_discount_item_b);
            $("#subtotal_after_discount_item_view_b").val(toRp(total_price_after_discount_item_b));

            $("#total_price_after_ppn_amount_view").val(total_price_after_ppn_amount);
            $("#total_price_after_ppn_amount").val(total_price_after_ppn_amount);
            $("#ppn_amount_item").val(ppn_amount_item);
            console.log(ppn_amount_item);
            
		});  
    });    

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
	});



    $(document).ready(function(){
        var discount_percentage_item 	                = $("#discount_percentage_item").val();
            var discount_percentage_item_b 	                = $("#discount_percentage_item_b").val();
			var total_price 	                            = $("#total_price").val();
            var ppn_out_percentage 	                        = $("#ppn_out_percentage").val();
            var discount_item                               = discount_percentage_item * 1/100;
            var discount_item_b                             = discount_percentage_item_b * 1/100;
            var ppn_item                                    = ppn_out_percentage * 1/100;
            var discount_amount_item                        = discount_item * total_price;
            var total_price_after_discount_item_a 	        = total_price - discount_amount_item;
            var discount_amount_item_b                      = discount_item_b * total_price_after_discount_item_a;
            var total_price_after_discount_item_b 	        = total_price_after_discount_item_a - discount_amount_item_b;
            var ppn_amount_item                             = ppn_item * total_price_after_discount_item_b;
            var total_price_after_ppn_amount                = ppn_amount_item + total_price_after_discount_item_b;

            $("#discount_amount_item").val(discount_amount_item);
            $("#discount_amount_item_b").val(discount_amount_item_b);
            console.log(discount_amount_item);
            console.log(discount_amount_item_b);

            $("#discount_amount_item_view").val(toRp(discount_amount_item + discount_amount_item_b));

            $("#subtotal_after_discount_item_a").val(total_price_after_discount_item_a);
            console.log(total_price_after_discount_item_a);
            $("#subtotal_after_discount_item_view_a").val(toRp(total_price_after_discount_item_a));

            $("#subtotal_after_discount_item_b").val(total_price_after_discount_item_b);
            console.log(total_price_after_discount_item_b);
            $("#subtotal_after_discount_item_view_b").val(toRp(total_price_after_discount_item_b));

            $("#total_price_after_ppn_amount_view").val(total_price_after_ppn_amount);
            $("#total_price_after_ppn_amount").val(total_price_after_ppn_amount);
            $("#ppn_amount_item").val(ppn_amount_item);
            console.log(ppn_amount_item);

        $("#discount_percentage_item").change(function(){
            var discount_percentage_item 	                = $("#discount_percentage_item").val();
            var discount_percentage_item_b 	                = $("#discount_percentage_item_b").val();
			var total_price 	                            = $("#total_price").val();
            var ppn_out_percentage 	                        = $("#ppn_out_percentage").val();
            var discount_item                               = discount_percentage_item * 1/100;
            var discount_item_b                             = discount_percentage_item_b * 1/100;
            var ppn_item                                    = ppn_out_percentage * 1/100;
            var discount_amount_item                        = discount_item * total_price;
            var total_price_after_discount_item_a 	        = total_price - discount_amount_item;
            var discount_amount_item_b                      = discount_item_b * total_price_after_discount_item_a;
            var total_price_after_discount_item_b 	        = total_price_after_discount_item_a - discount_amount_item_b;
            var ppn_amount_item                             = ppn_item * total_price_after_discount_item_b;
            var total_price_after_ppn_amount                = ppn_amount_item + total_price_after_discount_item_b;

            $("#discount_amount_item").val(discount_amount_item);
            $("#discount_amount_item_b").val(discount_amount_item_b);
            console.log(discount_amount_item);
            console.log(discount_amount_item_b);

            $("#discount_amount_item_view").val(toRp(discount_amount_item + discount_amount_item_b));

            $("#subtotal_after_discount_item_a").val(total_price_after_discount_item_a);
            console.log(total_price_after_discount_item_a);
            $("#subtotal_after_discount_item_view_a").val(toRp(total_price_after_discount_item_a));

            $("#subtotal_after_discount_item_b").val(total_price_after_discount_item_b);
            console.log(total_price_after_discount_item_b);
            $("#subtotal_after_discount_item_view_b").val(toRp(total_price_after_discount_item_b));

            $("#total_price_after_ppn_amount_view").val(total_price_after_ppn_amount);
            $("#total_price_after_ppn_amount").val(total_price_after_ppn_amount);
            $("#ppn_amount_item").val(ppn_amount_item);
            console.log(ppn_amount_item);


		});

        $("#discount_percentage_item_b").change(function(){
            var discount_percentage_item 	                = $("#discount_percentage_item").val();
            var discount_percentage_item_b 	                = $("#discount_percentage_item_b").val();
			var total_price 	                            = $("#total_price").val();
            var ppn_out_percentage 	                        = $("#ppn_out_percentage").val();
            var discount_item                               = discount_percentage_item * 1/100;
            var discount_item_b                             = discount_percentage_item_b * 1/100;
            var ppn_item                                    = ppn_out_percentage * 1/100;
            var discount_amount_item                        = discount_item * total_price;
            var total_price_after_discount_item_a 	        = total_price - discount_amount_item;
            var discount_amount_item_b                      = discount_item_b * total_price_after_discount_item_a;
            var total_price_after_discount_item_b 	        = total_price_after_discount_item_a - discount_amount_item_b;
            var ppn_amount_item                             = ppn_item * total_price_after_discount_item_b;
            var total_price_after_ppn_amount                = ppn_amount_item + total_price_after_discount_item_b;

            $("#discount_amount_item").val(discount_amount_item);
            $("#discount_amount_item_b").val(discount_amount_item_b);
            console.log(discount_amount_item);
            console.log(discount_amount_item_b);

            $("#discount_amount_item_view").val(toRp(discount_amount_item + discount_amount_item_b));

            $("#subtotal_after_discount_item_a").val(total_price_after_discount_item_a);
            console.log(total_price_after_discount_item_a);
            $("#subtotal_after_discount_item_view_a").val(toRp(total_price_after_discount_item_a));

            $("#subtotal_after_discount_item_b").val(total_price_after_discount_item_b);
            console.log(total_price_after_discount_item_b);
            $("#subtotal_after_discount_item_view_b").val(toRp(total_price_after_discount_item_b));

            $("#total_price_after_ppn_amount_view").val(total_price_after_ppn_amount);
            $("#total_price_after_ppn_amount").val(total_price_after_ppn_amount);
            $("#ppn_amount_item").val(ppn_amount_item);
            console.log(ppn_amount_item);


		});
    });





    //discount nota
    $(document).ready(function(){
        var discount_percentage 	            = $("#discount_percentage").val();
			var total_price_all 	                = $("#total_price_all").val();

            var ppn_out_percentage 	                = $("#ppn_out_percentage").val();
			//var total_price_after_discount          = $("#subtotal_after_discount").val();

            var discount                            = discount_percentage * 1/100;
            var discount_amount                     = discount * total_price_all;
            var total_price_after_discount	        = total_price_all - discount_amount;
            

    
            var ppn_out_amount                      = 0;
            var total_price_after_ppn_out           = Number(total_price_after_discount) + ppn_out_amount;


            $("#discount_amount").val(discount_amount);
            console.log(discount_amount);
            $("#discount_amount_view").val(toRp(discount_amount));

            $("#subtotal_after_discount").val(total_price_after_discount);
            console.log(total_price_after_discount);
            $("#subtotal_after_discount_view").val(toRp(total_price_after_discount));

            $("#ppn_out_amount").val(ppn_out_amount);
            console.log(ppn_out_amount);
            $("#ppn_out_amount_view").val(toRp(ppn_out_amount));
            


            $("#subtotal_after_ppn_out").val(total_price_after_ppn_out);
            console.log(total_price_after_ppn_out);
            $("#subtotal_after_ppn_out_view").val(toRp(total_price_after_ppn_out));

        $("#discount_percentage").change(function(){
			var discount_percentage 	            = $("#discount_percentage").val();
			var total_price_all 	                = $("#total_price_all").val();

            var ppn_out_percentage 	                = $("#ppn_out_percentage").val();
			//var total_price_after_discount          = $("#subtotal_after_discount").val();

            var discount                            = discount_percentage * 1/100;
            var discount_amount                     = discount * total_price_all;
            var total_price_after_discount	        = total_price_all - discount_amount;
            

    
            var ppn_out_amount                      = 0;
            var total_price_after_ppn_out           = Number(total_price_after_discount) + ppn_out_amount;


            $("#discount_amount").val(discount_amount);
            console.log(discount_amount);
            $("#discount_amount_view").val(toRp(discount_amount));

            $("#subtotal_after_discount").val(total_price_after_discount);
            console.log(total_price_after_discount);
            $("#subtotal_after_discount_view").val(toRp(total_price_after_discount));

            $("#ppn_out_amount").val(ppn_out_amount);
            console.log(ppn_out_amount);
            $("#ppn_out_amount_view").val(toRp(ppn_out_amount));
            


            $("#subtotal_after_ppn_out").val(total_price_after_ppn_out);
            console.log(total_price_after_ppn_out);
            $("#subtotal_after_ppn_out_view").val(toRp(total_price_after_ppn_out));

		});
    });

    $(document).ready(function(){
        var discount_percentage 	            = $("#discount_percentage").val();
			var total_price_all 	                = $("#total_price_all").val();

            var ppn_out_percentage 	                = $("#ppn_out_percentage").val();
			//var total_price_after_discount          = $("#subtotal_after_discount").val();

            var discount                            = discount_percentage * 1/100;
            var discount_amount                     = discount * total_price_all;
            var total_price_after_discount	        = total_price_all - discount_amount;
            

    
            var ppn_out_amount                      = 0;
            var total_price_after_ppn_out           = Number(total_price_after_discount) + ppn_out_amount;


            $("#discount_amount").val(discount_amount);
            console.log(discount_amount);
            $("#discount_amount_view").val(toRp(discount_amount));

            $("#subtotal_after_discount").val(total_price_after_discount);
            console.log(total_price_after_discount);
            $("#subtotal_after_discount_view").val(toRp(total_price_after_discount));

            $("#ppn_out_amount").val(ppn_out_amount);
            console.log(ppn_out_amount);
            $("#ppn_out_amount_view").val(toRp(ppn_out_amount));
            


            $("#subtotal_after_ppn_out").val(total_price_after_ppn_out);
            console.log(total_price_after_ppn_out);
            $("#subtotal_after_ppn_out_view").val(toRp(total_price_after_ppn_out));



        $("#ppn_out_percentage").change(function(){

			var discount_percentage 	            = $("#discount_percentage").val();
			var total_price_all 	                = $("#total_price_all").val();

            var ppn_out_percentage 	                = $("#ppn_out_percentage").val();
			//var total_price_after_discount          = $("#subtotal_after_discount").val();

            var discount                            = discount_percentage * 1/100;
            var discount_amount                     = discount * total_price_all;
            var total_price_after_discount	        = total_price_all - discount_amount;
            

    
            var ppn_out_amount                      = 0;
            var total_price_after_ppn_out           = Number(total_price_after_discount) + ppn_out_amount;


            $("#discount_amount").val(discount_amount);
            console.log(discount_amount);
            $("#discount_amount_view").val(toRp(discount_amount));

            $("#subtotal_after_discount").val(total_price_after_discount);
            console.log(total_price_after_discount);
            $("#subtotal_after_discount_view").val(toRp(total_price_after_discount));

            $("#ppn_out_amount").val(ppn_out_amount);
            console.log(ppn_out_amount);
            $("#ppn_out_amount_view").val(toRp(ppn_out_amount));
            


            $("#subtotal_after_ppn_out").val(total_price_after_ppn_out);
            console.log(total_price_after_ppn_out);
            $("#subtotal_after_ppn_out_view").val(toRp(total_price_after_ppn_out));

		});
    });


    function processAddArraySalesOrderItem(){
        var item_type_id		                = document.getElementById("item_type_id").value;
        var item_unit_id	                    = document.getElementById("item_unit_id").value;
        // var item_stock_id	                    = document.getElementById("item_stock_id").value;
        var quantity			                = document.getElementById("quantity").value;
        var price			                    = document.getElementById("price").value;
        var total_price			                = document.getElementById("total_price").value;
        var discount_percentage_item            = document.getElementById("discount_percentage_item").value;
        var discount_amount_item		        = document.getElementById("discount_amount_item").value;
        var subtotal_after_discount_item_a	    = document.getElementById("subtotal_after_discount_item_a").value;
        var discount_percentage_item_b          = document.getElementById("discount_percentage_item_b").value;
        var discount_amount_item_b		        = document.getElementById("discount_amount_item_b").value;
        var subtotal_after_discount_item_b	    = document.getElementById("subtotal_after_discount_item_b").value;
        var ppn_amount_item	                    = document.getElementById("ppn_amount_item").value;
        var total_price_after_ppn_amount	    = document.getElementById("total_price_after_ppn_amount").value;

        $.ajax({
            type: "POST",
            url : "{{route('sales-order-add-array')}}",
            data: {
                'item_type_id'                  : item_type_id, 
                'item_unit_id'                  : item_unit_id,
                // 'item_stock_id' 		        : item_stock_id,
                'quantity' 			            : quantity,
                'price' 			            : price,
                'total_price' 		            : total_price,
                'discount_percentage_item' 	    : discount_percentage_item,
                'discount_amount_item' 		    : discount_amount_item,
                'subtotal_after_discount_item_a': subtotal_after_discount_item_a,
                'discount_percentage_item_b' 	: discount_percentage_item_b,
                'discount_amount_item_b' 		: discount_amount_item_b,
                'subtotal_after_discount_item_b': subtotal_after_discount_item_b,
                'ppn_amount_item'               : ppn_amount_item,
                'total_price_after_ppn_amount'  : total_price_after_ppn_amount,
                '_token'                        : '{{csrf_token()}}'
            },
            success: function(msg){
                location.reload();
            }
        });
    }

    function addCustomer(){
        var customer_name 	        = $("#customer_name").val();
        var province_id 	        = $("#province_id").val();
        var city_id 	            = $("#city_id").val();
        var customer_address 	    = $("#customer_address").val();
        var customer_home_phone 	= $("#customer_home_phone").val();
        var customer_mobile_phone1 	= $("#customer_mobile_phone1").val();
        var customer_mobile_phone2 	= $("#customer_mobile_phone2").val();
        var customer_fax_number 	= $("#customer_fax_number").val();
        var customer_email 	        = $("#customer_email").val();
        var customer_contact_person = $("#customer_contact_person").val();
        var customer_id_number 	    = $("#customer_id_number").val();
        var customer_tax_no 	    = $("#customer_tax_no").val();
        var customer_payment_terms 	= $("#customer_payment_terms").val();
        var customer_remark 	    = $("#customer_remark").val();
        $.ajax({
            type: "POST",
            url : "{{route('add-customer-sales-order')}}",
            dataType: "html",
            data: {
                'customer_name'	            : customer_name,
                'province_id'	            : province_id,
                'city_id'	                : city_id,
                'customer_address'	        : customer_address,
                'customer_home_phone'	    : customer_home_phone,
                'customer_mobile_phone1'    : customer_mobile_phone1,
                'customer_mobile_phone2'	: customer_mobile_phone2,
                'customer_fax_number'	    : customer_fax_number,
                'customer_email'	        : customer_email,
                'customer_contact_person'	: customer_contact_person,
                'customer_id_number'	    : customer_id_number,
                'customer_tax_no'	        : customer_tax_no,
                'customer_payment_terms'	: customer_payment_terms,
                'customer_remark'	        : customer_remark,
                '_token'                    : '{{csrf_token()}}',
            },
            success: function(return_data){ 
                $('#customer_id').html(return_data);
                $('#cancel_btn_customer').click();
            },
            error: function(data)
            {
                console.log(data);

            }
        });
    }

    $(document).ready(function(){
        var item_type_id = {!! json_encode($null_item_type_id) !!};
        
        if(item_type_id == null){
            $("#item_type_id").select2("val", "0");
        }
    });

</script>
@stop

@section('content_header')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ url('home') }}">Beranda</a></li>
        <li class="breadcrumb-item"><a href="{{ url('sales-order') }}">Daftar Sales Order</a></li>
        <li class="breadcrumb-item active" aria-current="page">Tambah Sales Order</li>
    </ol>
</nav>
@stop

@section('content')

<h3 class="page-title">
    Form Tambah Sales Order
</h3>
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
<div class="card border border-dark"  style="margin-top: 1%">
    <div class="card-header border-dark bg-dark">
        <h5 class="mb-0 float-left">
            Form Tambah
        </h5>
        <div class="float-right">
            <button onclick="location.href='{{ url('sales-order') }}'" name="Find" class="btn btn-sm btn-info" title="Back"><i class="fa fa-angle-left"></i>  Kembali</button>
        </div>
    </div>

    <form method="post" action="{{route('process-add-sales-order')}}" enctype="multipart/form-data">
        @csrf
        <div class="card-body">
            <div class="row form-group">
                <div class="col-md-6">
                    <section class="control-label">Tanggal SO
                        <span class="required text-danger">
                            *
                        </span>
                    </section>
                    <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="sales_order_date" id="sales_order_date" onChange="elements_add(this.name, this.value);" value="{{$salesorderelements == null ? '' : $salesorderelements['sales_order_date']}}" style="width: 15rem;"/>
                </div>
                <div class="col-md-6">
                    <section class="control-label">Tanggal Pengiriman
                        <span class="required text-danger">
                            *
                        </span>
                    </section>
                    <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="sales_order_delivery_date" id="sales_order_delivery_date" onChange="elements_add(this.name, this.value);" value="{{$salesorderelements == null ? '' : $salesorderelements['sales_order_delivery_date']}}" style="width: 15rem;"/>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-5">
                    <a class="text-dark">Nama Pelanggan<a class='red'> *</a></a>
                    {!! Form::select('customer_id',  $customer, $salesorderelements == null ? '' : $salesorderelements['customer_id'], ['class' => 'selection-search-clear select-form', 'id' => 'customer_id', 'onchange' => 'elements_add(this.name , this.value);']) !!}
                </div>
                <div class="col-md-1 mt-1">
                    <a class="text-dark"></a>
                    <a href='#addcustomer' data-toggle='modal' name="Find" class="btn btn-success add-btn btn-sm" title="Add Data">Tambah</a>
                </div>
                <div class="col-md-6">
                    <a class="text-dark">Jenis Sales Order<a class='red'> *</a></a>
                    {!! Form::select('sales_order_type_id',  $salesordertype, $salesorderelements == null ? '' : $salesorderelements['sales_order_type_id'], ['class' => 'selection-search-clear select-form', 'id' => 'sales_order_type_id', 'onchange' => 'elements_add(this.name , this.value);']) !!}
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-6">
                    <section class="control-label">Nomor PO
                    </section>
                    <input type ="text" class="form-control form-control-inline input-medium date-picker input-date"  name="purchase_order_no" id="purchase_order_no" onChange="elements_add(this.name, this.value);" value="{{$salesorderelements == null ? '' : $salesorderelements['purchase_order_no']}}" style="width: 15rem;"/>
                </div>
                <div class="col-md-6">
                    <section class="control-label">Tanggal Kadaluarsa PO
                    </section>
                    <input type ="date" class="form-control form-control-inline input-medium date-picker input-date" data-date-format="dd-mm-yyyy" type="text" name="purchase_order_due_date" id="purchase_order_due_date" onChange="elements_add(this.name, this.value);" value="{{$salesorderelements == null ? '' : $salesorderelements['purchase_order_due_date']}}" style="width: 15rem;"/>
                </div>
            </div>
            <div class="row form-group" id="receipt-image">
                <div class="col-md-6">
                    <b>File Gambar Kwitansi</b><br/>
                    <input type="file" name="receipt_image" id="receipt_image" value="" accept="image/*"/>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-12 ">
                    <a class="text-dark">Keterangan</a>
                    <div class="">
                        <textarea rows="3" type="text" class="form-control input-bb" name="sales_order_remark" onChange="elements_add(this.name, this.value);" id="sales_order_remark" >{{$salesorderelements == null ? '' : $salesorderelements['sales_order_remark']}}</textarea>
                    </div>
                </div>
            </div>
            
            <h4 class="text-dark" style="margin-top: 3%;"><b>Daftar Barang</b></h4>
            <hr/>
            <div class="row form-group">
                <div class="col-md-4">
                    <a class="text-dark">Nama Barang<a class='red'> *</a></a>
                    {!! Form::select('item_type_id',  $invitemtype, $null_item_type_id, ['class' => 'selection-search-clear select-form', 'id' => 'item_type_id']) !!}
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Stock Tersedia</a>
                        <input class="form-control input-bb" type="text" name="available_stock" id="available_stock" value="" readonly/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Satuan<a class='red'> *</a></a>
                        <select class="selection-search-clear" name="item_unit_id" id="item_unit_id" style="width: 100% !important">
                        </select>
                    </div>
                </div>
                {{-- <div class="col-md-4">
                    <div class="form-group">                     
                            <a class="text-dark">Stock Barang<a class='red'> *</a></a>
                            <select class="selection-search-clear" name="item_stock_id" id="item_stock_id" style="width: 100% !important">
                            </select>
                    </div>
                </div> --}}
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Quantity</a>
                        <a class='red'> *</a>
                        <input class="form-control input-bb" type="text" name="quantity" id="quantity" value=""/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Harga Satuan<a class='red'> *</a></a>
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
            </div>
            <div class="row form-group">
            </div>
            <div class="row form-group">
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Discount A /Barang (%)</a>
                         <a class="text-dark">Satuan<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="discount_percentage_item" id="discount_percentage_item" value=""/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Nominal Discount A</a>
                        <input class="form-control input-bb" type="text" name="discount_amount_item" id="discount_amount_item" value="" readonly/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Total Harga Setelah Discount A</a>
                        <input class="form-control input-bb" type="text" name="subtotal_after_discount_item_view_a" id="subtotal_after_discount_item_view_a" value="" readonly/>
                        <input class="form-control input-bb" type="hidden" name="subtotal_after_discount_item_a" id="subtotal_after_discount_item_a" value=""/>
                    </div>
                </div>
            </div>
            <div class="row form-group">
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Discount B /Barang (%)</a>
                         <a class="text-dark">Satuan<a class='red'> *</a></a>
                        <input class="form-control input-bb" type="text" name="discount_percentage_item_b" id="discount_percentage_item_b" value=""/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Nominal Discount B</a>
                        <input class="form-control input-bb" type="text" name="discount_amount_item_b" id="discount_amount_item_b" value="" readonly/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Total Harga Setelah Discount B</a>
                        <input class="form-control input-bb" type="text" name="subtotal_after_discount_item_view_b" id="subtotal_after_discount_item_view_b" value="" readonly/>
                        <input class="form-control input-bb" type="hidden" name="subtotal_after_discount_item_b" id="subtotal_after_discount_item_b" value=""/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">PPN %</a>
                        <input class="form-control input-bb" type="text"name="ppn_out_percentage" id="ppn_out_percentage" readonly value="{{ $ppnOut['ppn_amount_out'] }}"  placeholder="isi 0 jika kosong"></td>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">PPN Item Amount</a>
                        <input class="form-control input-bb" type="text" name="ppn_amount_item" id="ppn_amount_item" readonly/>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <a class="text-dark">Total After PPN Item </a>
                        <input class="form-control input-bb" type="text" name="total_price_after_ppn_amount_view" id="total_price_after_ppn_amount_view" readonly placeholder="isi 0 jika kosong"></td>
                        <input class="form-control input-bb" type="text" name="total_price_after_ppn_amount" id="total_price_after_ppn_amount" hidden/>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-muted">
            <div class="form-actions float-right">
                <a type="submit" name="Save" class="btn btn-primary btn-sm" title="Save" onclick="processAddArraySalesOrderItem()">Tambah</a>
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
                                <th style='text-align:center'>Barang</th>
                                <th style='text-align:center'>Qty</th>
                                <th style='text-align:center'>Satuan</th>
                                <th style='text-align:center'>Harga Satuan</th>
                                <th style='text-align:center'>Discount A/Barang</th>
                                <th style='text-align:center'>Total Harga A</th>
                                <th style='text-align:center'>Discount B/Barang</th>
                                <th style='text-align:center'>Total Harga B</th>
                                <th style='text-align:center'>PPN</th>
                                <th style='text-align:center'>Total Setelah PPN</th>
                                <th style='text-align:center'>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                                @if(!is_array($salesorderitem))
                                    <tr><th colspan='11' style='text-align  : center !important;'>Data Kosong</th></tr>
                                @else 
                                @php
                                    $no =1;
                                    $total_price = 0;
                                    $total_price_after_discount_item = 0;
                                    $total_item = 0;
                                @endphp
                                    @foreach ($salesorderitem AS $key => $val)
                                        
                                            <tr>
                                                <td style='text-align  : center'>{{$no}}</td>
                                                <td style='text-align  : left !important;'>{{$SalesOrder->getItemTypeName($val['item_type_id'])}}</td>
                                                <td style='text-align  : right !important;'>{{$val['quantity']}}</td>
                                                <td style='text-align  : left !important;'>{{$SalesOrder->getItemUnitName($val['item_unit_id'])}}</td>
                                                <td style='text-align  : right !important;'>{{number_format($val['price'],2,',','.')}}</td>
                                                <td style='text-align  : right !important;'>{{number_format($val['discount_amount_item'],2,',','.')}}</td>
                                                <td style='text-align  : right !important;'>{{number_format($val['subtotal_after_discount_item_a'],2,',','.')}}</td>
                                                <td style='text-align  : right !important;'>{{number_format($val['discount_amount_item_b'],2,',','.')}}</td>
                                                <td style='text-align  : right !important;'>{{number_format($val['subtotal_after_discount_item_b'],2,',','.')}}</td>
                                                <td style='text-align  : right !important;'>{{number_format($val['ppn_amount_item'],2,',','.')}}</td>
                                                <td style='text-align  : right !important;'>{{number_format($val['total_price_after_ppn_amount'],2,',','.')}}</td>
                                                <td style='text-align  : center'>
                                                    <a href="{{route('sales-order-delete-array', ['record_id' => $key])}}" name='Reset' class='btn btn-danger btn-sm' onClick='javascript:return confirm(\"apakah yakin ingin dihapus ?\")'></i> Hapus</a>
                                                </td>
                                                
                                                
                                            </tr>
                                        @php
                                            $no++;
                                            $total_price_after_discount_item += $val['total_price_after_ppn_amount'];
                                            $total_item+=$val['quantity'];
                                        @endphp
                                        
                                        @endforeach
                                        <th style='text-align  : center' colspan='2'>Total</th>
                                        <th style='text-align  : right' >{{$total_item}}</th>
                                        <th colspan='7'></th>
                                        <th style='text-align  : right'>{{number_format($total_price_after_discount_item,2,',','.')}}</th>
                                        <th>
                                            <input class='form-control input-bb' type='hidden' name='total_price_all' id='total_price_all' value='{{$total_price_after_discount_item}}'/>
                                            <input class='form-control input-bb' type='hidden' name='total_item_all' id='total_item_all' value='{{$total_item}}'/>
                                        </th>
                                        <tr>
                                            <td style='text-align  : center' colspan='2'><b>Discount Nota (%)</b></td>
                                            <td style='text-align  : center'><b>:</b></td>
                                            <td colspan='6'></td>
                                            <td>
                                                <input style='text-align  : right' type="text" class="form-control" name="discount_percentage" id="discount_percentage" value="0"  placeholder="isi 0 jika kosong"></td>
                                            <td>
                                                <input type="hidden" class="form-control" name="discount_amount" id="discount_amount" readonly>
                                                <input type="hidden" class="form-control" name="subtotal_after_discount" id="subtotal_after_discount" readonly>
                                                <input style='text-align  : right;  font-weight: bold;' type="text" class="form-control" name="discount_amount_view" id="discount_amount_view" readonly>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td style='text-align  : center' colspan='2'><b>Harga Setelah Discount Nota </td>
                                            <td style='text-align  : center'><b>:</b></td>
                                            <td colspan='7'></td>
                                            <td style='text-align  : center'>
                                                <input style='text-align  : right;  font-weight: bold;' type="text" class="form-control" name="subtotal_after_discount_view" id="subtotal_after_discount_view" readonly>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr hidden>
                                            <td  style='text-align  : center' colspan='2'><b>PPN Keluar (%)</b></td>
                                            <td  style='text-align  : center'><b>:</b></td>
                                            <td  colspan='6'></td>
                                            <td>
                                                <input  style='text-align  : right' type="text" class="form-control" name="ppn_out_percentage" id="ppn_out_percentage" value="0" placeholder="isi 0 jika kosong"></td>
                                            <td>
                                                <input  type="" class="form-control" name="ppn_out_amount" id="ppn_out_amount" readonly>
                                                <input  style='text-align  : right;  font-weight: bold;' type="text" class="form-control" name="ppn_out_amount_view" id="ppn_out_amount_view" readonly>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td style='text-align  : center' colspan='2'><b>Total Harga Akhir </td>
                                            <td style='text-align  : center'><b>:</b></td>
                                            <td colspan='7'></td>
                                            <td style='text-align  : center'>
                                                <input type="hidden" class="form-control" name="subtotal_after_ppn_out" id="subtotal_after_ppn_out" readonly>
                                                <input style='text-align  : right;  font-weight: bold;' type="text" class="form-control" name="subtotal_after_ppn_out_view" id="subtotal_after_ppn_out_view" readonly>
                                            </td>
                                            <td></td>
                                        </tr>
                            @endif
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
<br/>

<div class="modal fade bs-modal-lg" id="addcustomer" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header"  style='text-align:left !important'>
                <h4>Form Tambah Pelanggan</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">	
                        <div class="form-group">	
                            <a class="text-dark">Nama Pelanggan</a>
                            <input class="form-control input-bb" type="text" name="customer_name" id="customer_name" value=""/>
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
                        <a class="text-dark">Alamat Pelanggan</a>
                        <div class="">
                            <textarea rows="3" type="text" class="form-control input-bb" name="customer_address" onChange="elements_add(this.name, this.value);" id="customer_address" ></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Telp. Rumah</a>
                            <input class="form-control input-bb" type="text" name="customer_home_phone" id="customer_home_phone" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">No HP 1</a>
                            <input class="form-control input-bb" type="text" name="customer_mobile_phone1" id="customer_mobile_phone1" value=""/>
                        </div>
                    </div>	
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">No HP 2</a>
                            <input class="form-control input-bb" type="text" name="customer_mobile_phone2" id="customer_mobile_phone2" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">No Fax</a>
                            <input class="form-control input-bb" type="text" name="customer_fax_number" id="customer_fax_number" value=""/>
                        </div>
                    </div>	
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Email</a>
                            <input class="form-control input-bb" type="text" name="customer_email" id="customer_email" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Contact Person</a>
                            <input class="form-control input-bb" type="text" name="customer_contact_person" id="customer_contact_person" value=""/>
                        </div>
                    </div>	
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Nomor ID</a>
                            <input class="form-control input-bb" type="text" name="customer_id_number" id="customer_id_number" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">No Tax</a>
                            <input class="form-control input-bb" type="text" name="customer_tax_no" id="customer_tax_no" value=""/>
                        </div>
                    </div>	
                    <div class="col-md-6">		
                        <div class="form-group">		
                            <a class="text-dark">Syarat Pembayaran</a>
                            <input class="form-control input-bb" type="text" name="customer_payment_terms" id="customer_payment_terms" value=""/>
                        </div>
                    </div>	
                </div>
                <div class="row">
                    <div class="col-md-12 ">
                        <a class="text-dark">Keterangan</a>
                        <div class="">
                            <textarea rows="3" type="text" class="form-control input-bb" name="customer_remark" onChange="elements_add(this.name, this.value);" id="customer_remark" ></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal" id='cancel_btn_customer'>Batal</button>
                <a class="btn btn-primary" onClick="addCustomer()">Simpan</a>
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