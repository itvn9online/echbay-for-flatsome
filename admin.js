if (document.domain == 'localhost' && typeof jQuery == 'function') {
    jQuery('#target_eb_plugins_iframe').height(600).attr({
        height: 600
    });
}

//
jQuery('.click-select-this-text').focus(function () {
    jQuery(this).select();
}).attr({
    'title': 'Copy this text and paste to Port or Page'
});
