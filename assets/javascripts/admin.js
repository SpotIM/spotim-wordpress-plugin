jQuery( document ).ready(function ( $ ) {
    var cancelImportProcess = false;

    owVariables.pageNumber = parseInt( owVariables.pageNumber, 10 );

    // Import
    $( '.sync-button' ).on( 'click', function( event ) {
        var $importButton = $(this),
            $parentElement = $importButton.parent(),
            $messageField = $importButton.siblings( '.description' ),
            $errorsField = $importButton.siblings( '.errors' ),
            owIdInputValue = $importButton.data( 'spot-id' ).trim(),
            importTokenInputValue = $importButton.data( 'import-token' ).trim(),
            postsPerRequestValue = parseInt( $importButton.data( 'posts-per-request' ) );

        $parentElement.addClass( 'in-progress' );

        // Empty message field from any text and reset css.
        $messageField
            .removeClass( 'red-color' )
            .empty();

        // Disable the import button.
        $( '.sync-button' ).attr( 'disabled', true );

        var data = {
            'action': 'start_import',
            'spotim_spot_id': owIdInputValue,
            'spotim_import_token': importTokenInputValue,
            'spotim_posts_per_request': postsPerRequestValue,
            'security' : owVariables.sync_nonce,

            // pageNumber is defined in options class,
            // inject from admin_javascript > owVariables.
            'spotim_page_number': owVariables.pageNumber
        };

        if($importButton.hasClass('force'))
            data.force = true;

        importCommentsToWP( data, $( '.sync-button' ), $messageField, $errorsField );

        event.preventDefault();
    });

    // Cancel import
    $( '#cancel_import_link' ).on( 'click', function( event ) {
        var cancelImportLink = $(this),
            $messageField = cancelImportLink.siblings( '.description' ),
            $parentElement = cancelImportLink.parent(),
            data = {
                'action': 'cancel_import',
                'spotim_page_number': 0,
                'security' : owVariables.sync_nonce,
            };

        $parentElement.removeClass( 'in-progress' );
        cancelImportProcess = true;

        $messageField
            .removeClass( 'red-color' )
            .text( owVariables.cancelImportMessage );

        $.post( ajaxurl, data, function() {
            window.location.reload( true );
        }, 'json' )
        .fail(function() {
            window.location.reload( true );
        });


        event.preventDefault();
    });

    // Checks for page number to be above zero to trigger #import_button
    if ( !! owVariables.pageNumber ) {
        $( '#import_button' ).trigger( 'click' );
    }

    // Import Commets to WordPress
    function importCommentsToWP( params, $importButton, $messageField, $errorsField ) {
        $.post( ajaxurl, params, function( response ) {
            if ( cancelImportProcess ) {
                return;
            }

            delete params.force;

            switch( response.status ) {
                case 'refresh':
                    importCommentsToWP( params, $importButton, $messageField, $errorsField );
                    break;
                case 'continue':
                    params.spotim_page_number = params.spotim_page_number + 1;
                    importCommentsToWP( params, $importButton, $messageField, $errorsField );
                    break;
                case 'success':
                    // Enable the import button and hide cancel link
                    $importButton
                        .attr( 'disabled', false )
                        .parent()
                            .removeClass( 'in-progress' );

                    // Reset page number to zero
                    owVariables.pageNumber = 0;
                    break;
                case 'error':
                    var displayErrorLog = false;

                    if ( 'undefined' !== typeof response.message ) {

                        $messageField.text( response.message );
                        $messageField.addClass( 'red-color' );

                        // Enable the import button and hide cancel link
                        $importButton
                            .attr( 'disabled', false )
                            .parent()
                            .removeClass( 'in-progress' );

                        return;
                    }

                    // Append to error box
                    if ( 'undefined' !== typeof response.messages ) {
                        for ( var message of response.messages ) {
                            // Check if message is not empty, display error log
                            if ( message.trim() ) {
                                displayErrorLog = true;
                            }
                            $errorsField.append(
                                $( '<p>' ).text( message )
                            );
                        }
                    }

                    if ( displayErrorLog ) {
                        $errorsField.removeClass( 'spotim-hide' );
                    }

                    // Keep importing, don't stop.
                    params.spotim_page_number = params.spotim_page_number + 1;
                    importCommentsToWP( params, $importButton, $messageField, $errorsField );

                    // Enable the import button and hide cancel link
                    // $importButton
                    //     .attr( 'disabled', false )
                    //     .parent()
                    //         .removeClass( 'in-progress' );
                    break;
            }

            // Show response message inside message field.
            $messageField.text( response.message );

        }, 'json' )
        .fail(function( response ) {
            $messageField.addClass( 'red-color' );

            // Enable the import button and hide cancel link.
            $importButton
                .attr( 'disabled', false )
                .parent()
                    .removeClass( 'in-progress' );

            // Show response message inside message field.
            $messageField.text( owVariables.errorMessage );
        });
    }

});
