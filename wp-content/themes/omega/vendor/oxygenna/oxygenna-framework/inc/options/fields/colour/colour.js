jQuery(document).ready(function($) {
    // set color picker input
    $('.colour-option').each(function(){
        var $input = $(this);
        $input.wpColorPicker({
            change: function(event, ui){
                $input.trigger('colourChange');
            }
        });
    });
});