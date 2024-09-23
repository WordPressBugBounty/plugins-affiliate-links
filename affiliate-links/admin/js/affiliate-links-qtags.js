(function () {
    QTags.addButton('affiliate-links-button', 'Affiliate Link', affiliateLinksQTagsButton, '', '', 'Add Affiliate Link');

    function affiliateLinksQTagsButton(e, c, ed) {
        var URL, t = this;
        if (typeof afLink !== 'undefined') {
            afLink.open(ed.id);
        }
    }
})();