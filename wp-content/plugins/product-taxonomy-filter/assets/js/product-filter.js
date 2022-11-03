function product_taxonomy_ajax_action(value, url) {
	var preloader = $('#loading-image');
 	var selected_brand = $('#filter_dropdown').val();
   	$.ajax({
	  type: "POST",
	  url: url,
	  data: {action: 'product_taxonomy_filter_action', 'data':selected_brand},
	  beforeSend:function(xhr){
			preloader.show(); 
		},
	  	success:function(data){
			$('.filter_response').html(data); 
			preloader.hide();
		},
		  error:function(e,data){
		  	console.log(data);
		      alert("something wrong"+ JSON.stringify(e)); // this will alert an error
	  	}
	});
 };

jQuery(document).ready(function($) {
	var defaultoutheightimg = -1;
	$(".product").each(function(index) {
		if($(this).outerHeight() > defaultoutheightimg) 
			defaultoutheightimg = $(this).height(); 
		});
	$(".product").height(defaultoutheightimg);
});