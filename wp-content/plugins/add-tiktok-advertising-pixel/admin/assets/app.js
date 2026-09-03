jQuery(document).ready(function () {
    jQuery('.ttap-alert').on('click', '.closebtn', function () {
        jQuery(this).closest('.ttap-alert').fadeOut(); //.css('display', 'none');
    });
    jQuery('.promotion-container').on('click', 'input', function() {
        jQuery(this).parent().parent().find('.promotion').slideToggle();
    });

    jQuery("#fs_connect button[type=submit]").on("click", function(e) {
        console.log("open verify window")
        window.open('https://better-robots.com/subscribe.php?plugin=tiktok-pixel','tiktok-pixel','resizable,height=400,width=700');
    });

    
});