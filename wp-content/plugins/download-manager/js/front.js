jQuery(function ($) {
    $('body').on('click', '.wpdm-download-locked.pop-over' ,function () {

        var $dc = $($(this).attr('href'));
        if ($(this).attr('data-ready') == undefined) {

            $(this).popover({
                placement: 'bottom',
                html: true,
                content: function () {

                    return $dc.html();


                }
            });
            $(this).attr('data-ready', 'hide');
        }

        if ($(this).attr('data-ready') == 'hide'){
            $(this).popover('show');
            $(this).attr('data-ready', 'show');
        } else if ($(this).attr('data-ready') == 'show'){
            $(this).popover('hide');
            $(this).attr('data-ready', 'hide');
        }


    return false;
    });

    $('body').on('click', '.wpdm-indir' , function(e){
        e.preventDefault();
        $('#xfilelist').load(location.href, {action: 'wpdmfilelistcd',pid: $(this).data('pid'), cd: $(this).data('dir')});
    });

    $('body').on('click', '.po-close' ,function () {

        $('.wpdm-download-link').popover('hide');
        $('.wpdm-download-link').attr('data-ready','hide');

    });


    $('body').on('click', '.wpdm-btn-play' ,function (e) {
        e.preventDefault();
        var player = $('#'+$(this).data('player'));
        player.attr('src', $(this).data('song')+"&play=song.mp3");
        player.slideDown();
        //audiojs.create(player);
    });


    // Uploading files
    var file_frame, dfield;


    jQuery('body').on('click', '.wpdm-media-upload' , function( event ){
        event.preventDefault();
        dfield = jQuery(jQuery(this).attr('rel'));

        // If the media frame already exists, reopen it.
        if ( file_frame ) {
            file_frame.open();
            return;
        }

        // Create the media frame.
        file_frame = wp.media.frames.file_frame = wp.media({
            title: jQuery( this ).data( 'uploader_title' ),
            button: {
                text: jQuery( this ).data( 'uploader_button_text' )
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });

        // When an image is selected, run a callback.
        file_frame.on( 'select', function() {
            // We set multiple to false so only get one image from the uploader
            attachment = file_frame.state().get('selection').first().toJSON();
            dfield.val(attachment.url);

        });

        // Finally, open the modal
        file_frame.open();
    });

    try{
        /*
        FB.login(function(response) {
            if (response.session) {

                var user_id = response.session.uid;
                var page_id = "40796308305"; //coca cola
                var fql_query = "SELECT uid FROM page_fan WHERE page_id = "+page_id+"and uid="+user_id;
                var the_query = FB.Data.query(fql_query);

                the_query.wait(function(rows) {

                    if (rows.length == 1 && rows[0].uid == user_id) {
                        $("#container_like").show();

                        //here you could also do some ajax and get the content for a "liker" instead of simply showing a hidden div in the page.

                    } else {
                        $("#container_notlike").show();
                        //and here you could get the content for a non liker in ajax...
                    }
                });


            } else {
                // user is not logged in
            }
        });
        */
    }catch(err){}

});