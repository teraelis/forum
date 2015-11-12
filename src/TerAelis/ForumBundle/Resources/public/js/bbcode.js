$(document).ready(function() {
    function wrapText(openTag, closeTag) {
        return function(textArea) {
            var len = textArea.val().length;
            var start = textArea[0].selectionStart;
            var end = textArea[0].selectionEnd;
            var selectedText = textArea.val().substring(start, end);
            var replacement = openTag + selectedText + closeTag;
            textArea.val(textArea.val().substring(0, start) + replacement + textArea.val().substring(end, len));
        }
    }

    var buttons = {
        'bold': function() {return wrapText("[b]", "[/b]")},
        'underline': function() {return wrapText("[u]", "[/u]")},
        'italic': function() {return wrapText("[i]", "[/i]")},
        'strike': function() {return wrapText("[s]", "[/s]")},
        'center': function() {return wrapText("[center]", "[/center]")},
        'justify': function() {return wrapText("[justify]", "[/justify]")},
        'img': function() {return wrapText("[img]", "[/img]");},
        'link': function() {return wrapText("[url=\"link_to_your_page\"]", "[/url]")},
        'quote': function() {return wrapText("[quote]", "[/quote]")},
        'color': function() {return wrapText("[color=\"#888888\"]", "[/color]")},
        'size': function() {return wrapText("[size=\"18\"]", "[/size]")},
        'soundcloud': function() {return wrapText("[soundcloud]", "[/soundcloud]")},
        'toancre': function() {return wrapText("[toancre=\"nom_de_l_ancre\"]", "[/toancre]")},
        'ancre': function() {return wrapText("[ancre]", "[/ancre]")},
        'spoiler': function() {return wrapText("[spoiler]", "[/spoiler]")}
    };

    $('.bbcode').each(function(key, bbcodeContainer) {
        var textarea = $(bbcodeContainer).find('textarea')[0];
        $(bbcodeContainer).find('.bbcode-btn').each(function(k, v) {
            $(v).click(function(event) {
                event.preventDefault();
                for(var type in buttons) {
                    if ($(this).hasClass(type)) {
                        buttons[type]()($(textarea));
                    }
                };
            });
        });
    });
});