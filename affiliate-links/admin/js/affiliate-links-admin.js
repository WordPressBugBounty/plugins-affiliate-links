(function ($) {
    // handle embed form controls
    ALEmbedForm();
    // handle html/shortcode copy to clipboard
    ALHandleEmbedCopy();
    // handle link copy to clipboard
    ALHandleLinkCopy();
})(jQuery);

function ALEmbedForm() {

    var htmlForm = jQuery('.affiliate_links_embed_html');
    var SCForm = jQuery('.affiliate_links_embed_shortcode');
    var htmlProto = jQuery('.affiliate_links_proto_html');
    var htmlProtoLink = jQuery('a', htmlProto);
    var embedHtml = '';
    var embedShortcode = '';

    jQuery('.affiliate_links_control[type=checkbox]').on('change', function () {

        var _self = jQuery(this);
        var attr = _self.data('attr');
        var value = _self.data('value');

        if (_self.is(':checked')) {
            htmlProtoLink.attr(attr, value);
        } else {
            htmlProtoLink.removeAttr(attr);
        }

        embedHtml = htmlProto.html();
        embedShortcode = shortcodeHTML(htmlProto);
        htmlForm.val(embedHtml);
        SCForm.val(embedShortcode);

    });

    jQuery('.affiliate_links_control[type=text]').on('keyup keydown input', function () {

        var _self = jQuery(this);
        var attr = _self.data('attr');
        var value = _self.val();

        if (typeof value == 'undefined' || value == '' || value == null) {
            if (attr == 'anchor') {
                htmlProtoLink.text('Click here!');
            } else {
                htmlProtoLink.removeAttr(attr);
            }
        } else {
            if (attr == 'anchor') {
                htmlProtoLink.text(value);
            } else {
                htmlProtoLink.attr(attr, value);
            }
        }

        embedHtml = htmlProto.html();
        embedShortcode = shortcodeHTML(htmlProto);
        htmlForm.val(embedHtml);
        SCForm.val(embedShortcode);

    });

    function shortcodeHTML(htmlProto) {

        var shortCodeProto = htmlProto.clone();

        jQuery(shortCodeProto)
            .find('a')
            .removeAttr('href')
            .attr('id', afLinksAdmin.linkId);

        return shortCodeProto.html()
            .replace('</a>', '[/af_link]')
            .replace('<a', '[af_link')
            .replace(">", "]");
    }

}

function ALHandleEmbedCopy() {

    var btns = jQuery('.affiliate_links_copy');

    btns.each(function () {

        var btn = jQuery(this);
        var textareaClass = btn.data('source');

        btn.on('click', function (e) {

            e.preventDefault();

            var copyTextarea = document.querySelector('.' + textareaClass);
            copyTextarea.select();

            try {
                document.execCommand('copy');
            } catch (err) {
                console.log('Unable to copy');
            }

        });

    });

}

function ALHandleLinkCopy() {

    var btn = jQuery('.affiliate_links_copy_button');

    btn.on('click', function (e) {

        e.preventDefault();

        var copyLink = document.querySelector('.affiliate_links_copy_link');

        try {
            window.getSelection().removeAllRanges();
            var range = document.createRange();
            range.selectNode(copyLink);
            window.getSelection().addRange(range);
            document.execCommand('copy');
            window.getSelection().removeAllRanges();
        } catch (err) {
            console.log('Unable to copy');
        }

    });

}

var afLink;

(function ($) {

    var SCForm,
        htmlProto,
        htmlProtoLink,
        embedHtml,
        embedShortcode = '',
        editor = {},
        inputs = {};

    afLink = {
        textarea: '',

        open: function (editorId) {
            editor = window.tinymce.get(editorId);
            //editor = ed;
            afLink.range = null;

            if (editorId) {
                window.wpActiveEditor = editorId;
            }

            if (!window.wpActiveEditor) {
                return;
            }

            this.textarea = $('#' + window.wpActiveEditor).get(0);

            if (!afLink.isMCE() && document.selection) {
                this.textarea.focus();
                this.range = document.selection.createRange();
            }

            inputs.backdrop.show();
            inputs.wrap.show();
            afLink.refresh();
        },

        isMCE: function () {
            //var editor = window.tinymce.get( window.wpActiveEditor );
            return editor && !editor.isHidden();
        },

        close: function () {
            $(document.body).removeClass('modal-open');
            inputs.backdrop.hide();
            inputs.wrap.hide();
            $(document).trigger('wplink-close', inputs.wrap);
        },

        update: function () {
            if (afLink.isMCE()) {
                afLink.mceUpdate();
            } else {
                afLink.htmlUpdate();
            }
        },

        htmlUpdate: function () {
            var attrs, text, html, begin, end, cursor, selection,
                textarea = afLink.textarea;

            if (!textarea) {
                return;
            }

            html = this.getShortcodeLink();

            // W3C
            begin = textarea.selectionStart;
            end = textarea.selectionEnd;
            cursor = begin + html.length;

            textarea.value = (
                textarea.value.substring(0, begin) +
                html +
                textarea.value.substring(end, textarea.value.length)
            );

            // Update cursor position
            textarea.selectionStart = textarea.selectionEnd = cursor;

            afLink.close();
            textarea.focus();

        },

        mceUpdate: function () {
            editor.execCommand('mceInsertContent', false, this.getShortcodeLink());
            this.close();
        },

        getShortcodeLink: function () {
            return $('#af-link-shortcode').val();
        },

        shortcodeHTML: function (embedHtml) {
            embedHtml = embedHtml.replace('</a>', '[/af_link]');
            embedHtml = embedHtml.replace('<a', '[af_link');
            embedHtml = embedHtml.replace(">", "]");
            return embedHtml;
        },

        updateLinkId: function () {
            var attr = inputs.links.find(':selected').data('attr');
            var value = inputs.links.find(':selected').data('value');

            if (!jQuery('.affiliate_links_control[data-attr=anchor]').val().length) {
                htmlProtoLink.text(inputs.links.find(':selected').text());
            }

            htmlProtoLink.removeAttr(attr);
            htmlProtoLink.attr(attr, value);

            embedHtml = htmlProto.html();

            embedShortcode = afLink.shortcodeHTML(embedHtml);
            SCForm.val(embedShortcode);
        },

        refresh: function () {

            $(inputs.textFields).each(function () {
                $(this).val('');
                var attr = $(this).data('attr');
                htmlProtoLink.removeAttr(attr);

            });

            $(inputs.checkboxes).each(function () {
                $(this).prop('checked', false);
                var attr = $(this).data('attr');
                htmlProtoLink.removeAttr(attr);
            });

            htmlProtoLink.text(inputs.links.find(':selected').text());

            afLink.updateLinkId();
        },

        init: function () {

            inputs.wrap = $('#af-link-wrap');

            SCForm = inputs.wrap.find('.affiliate_links_embed_shortcode');
            htmlProto = inputs.wrap.find('.affiliate_links_proto_html');
            htmlProtoLink = inputs.wrap.find('a', htmlProto);

            inputs.dialog = $('#af-link');
            inputs.backdrop = $('#af-link-backdrop');
            inputs.submit = $('#af-link-submit');
            inputs.close = $('#af-link-close');

            inputs.links = inputs.wrap.find('#links.affiliate_links_control');
            inputs.textFields = inputs.wrap.find('.affiliate_links_control[type=text]');
            inputs.checkboxes = inputs.wrap.find('.affiliate_links_control[type=checkbox]');

            inputs.submit.on('click', function (event) {
                event.preventDefault();
                afLink.update();
            });

            inputs.close.add(inputs.backdrop).add('#af-link-cancel a').on('click', function (event) {
                event.preventDefault();
                afLink.close();
            });
            if (inputs.links.length) {
                afLink.updateLinkId();
            }

            inputs.links.on('change', afLink.updateLinkId);

            inputs.checkboxes.on('change', function () {

                var _self = $(this);
                var attr = _self.data('attr');
                var value = _self.data('value');

                if (_self.is(':checked')) {
                    htmlProtoLink.attr(attr, value);
                } else {
                    htmlProtoLink.removeAttr(attr);
                }

                embedHtml = htmlProto.html();
                embedShortcode = afLink.shortcodeHTML(embedHtml);
                SCForm.val(embedShortcode);

            });

            inputs.textFields.on('keyup keydown input', function () {

                var _self = $(this);
                var attr = _self.data('attr');
                var value = _self.val();

                if (typeof value == 'undefined' || value == '' || value == null) {
                    if (attr == 'anchor') {
                        htmlProtoLink.text(inputs.links.find(':selected').text());
                    } else {
                        htmlProtoLink.removeAttr(attr);
                    }
                } else {
                    if (attr == 'anchor') {
                        htmlProtoLink.text(value);
                    } else {
                        htmlProtoLink.attr(attr, value);
                    }
                }

                embedHtml = htmlProto.html();
                embedShortcode = afLink.shortcodeHTML(embedHtml);
                SCForm.val(embedShortcode);

            });
            jQuery('.affiliate_links_control').trigger('change');
            jQuery('.affiliate_links_control').trigger('input');
        }
    };
    $(document).ready(afLink.init);
})(jQuery);
