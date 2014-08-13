/**
 * Oxygenna.com
 *
 * $Template:: *(TEMPLATE_NAME)*
 * $Copyright:: *(COPYRIGHT)*
 * $Licence:: *(LICENCE)*
 */
(function( $ ){
    $(document).ready(function($){
        // get the select box we need to toggle options with
        var $selectContainer = $( '#' + theme + '_show_header' );
        var toggleElementsSelector = '#page_header_heading, #page_header_section';
        $selectContainer.change(function(){
            // show selected options
            var selectVal = $selectContainer.find( '[name="' + theme + '-options[' + theme + '_show_header]"]' ).val();
            if( selectVal,selectVal === 'show') {
                $( toggleElementsSelector ).fadeIn();
            }
            else {
                $( toggleElementsSelector ).fadeOut();
            }
        }).trigger('change');
    });
})( jQuery );