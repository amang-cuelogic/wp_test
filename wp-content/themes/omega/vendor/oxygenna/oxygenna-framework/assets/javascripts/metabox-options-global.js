(function($){
    $(document).ready(function() {
        // var tooltips = $( "[data-description]" ).tooltip( { items: '[data-description]' } );
        var tooltips = $( ".description" ).tooltip({
            content: function () {
                return $(this).prop('title');
            },
            position: {
                my: "center bottom-20",
                at: "center top",
            },
            hide: {
                delay: 2000
            }
        });
    });
})(jQuery);