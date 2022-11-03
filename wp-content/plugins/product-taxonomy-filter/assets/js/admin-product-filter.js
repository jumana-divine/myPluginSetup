jQuery(document).ready(function($){
        $('#import_product_taxonomy input.button').attr('disabled','disabled');
        $('input:file').change(function(){
            if ($(this).val()){
                $('input:submit').removeAttr('disabled'); 
            }
            else {
                $('input:submit').attr('disabled','disabled     ');
            }
        });
});