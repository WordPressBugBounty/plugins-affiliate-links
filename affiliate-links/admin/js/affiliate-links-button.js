(function() {
    tinymce.create('tinymce.plugins.affiliate_links', {
        init : function(ed, url) {
            ed.addCommand('affiliate_links', function() {
                afLink.open(ed.id);
            });

            ed.addButton('affiliate_links', {
                title: 'Add Affiliate Link',
                icon: 'af_link',
                text: 'AFLink',
                cmd : 'affiliate_links'
            });
        }
    });

    // Register plugin
    tinymce.PluginManager.add('affiliate_links', tinymce.plugins.affiliate_links);
})();