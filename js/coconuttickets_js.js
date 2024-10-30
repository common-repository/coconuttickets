/**
 * Created by Clive on 16/03/2017.
 */
jQuery(document).ready(function() {
    jQuery(document).on( 'click', '.coconuttickets_dismiss .notice-dismiss', function() {
        jQuery.ajax({
            url: ajaxurl,
            data: {
                action: 'coconuttickets_accepted_notice'
            }
        });

    });
});

