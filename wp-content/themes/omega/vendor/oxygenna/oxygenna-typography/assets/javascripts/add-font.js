/**
 * Oxygenna.com
 *
 * $Template:: *(TEMPLATE_NAME)*
 * $Copyright:: *(COPYRIGHT)*
 * $Licence:: *(LICENCE)*
 */

 /*global jQuery: false, self: false */
'use strict';
(function($) {
    $(document).ready(function($) {
        $('#save-font').click( function() {
            if( $('input:checkbox:checked.variants').length > 0 && $('input:checkbox:checked.elements').length > 0 ) {
                var position = $(':input[name="position"]').val();
                var font = {
                    family: $(':input[name="family"]').val(),
                    provider: $(':input[name="provider"]').val(),
                    extracss: $('#extracss').val()
                };

                font.variants = $('input:checkbox:checked.variants').map(function () {
                    return this.value;
                }).get();

                font.elements = $('input:checkbox:checked.elements').map(function () {
                    return this.value;
                }).get();

                font.subsets = $('input:checkbox:checked.subsets').map(function () {
                    return this.value;
                }).get();

                self.parent.saveFont( font, position );
                self.parent.tb_remove();
            }
            else {
                alert( 'You must select a variant & element to use' );
            }
            return false;
        });

        $('#cancel').click( function() {
            self.parent.tb_remove();
        });

        $('#default-css').click( function() {
            $('#extracss').val( $(':input[name="default-font-css"]').val() );
            return false;
        });
    });
})(jQuery);