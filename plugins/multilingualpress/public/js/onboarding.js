( function ( $ ) {

    'use strict';

    $( function () {

        $('.notice-dismiss').click( function ( e ) {
            e.preventDefault();

            if($(this).parent().data('action') === 'mlp_action_dismiss') {

                $.ajax( {
                    url: ajaxurl,
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'onboarding_plugin'
                    }
                } )
                    .done( function ( data ) {} )
                    .fail( function () {} );
            }
        } );
    } );

}( jQuery ) );

