// Customised system based on the proof of concept workings of
// https://github.com/robertlabrie/bootstrap-star-rating

(function ( $ ) {

    $.fn.rating = function( method, options ) {
        method = method || 'create';
        // This is the easiest way to have default options.
        var settings = $.extend({
            // These are the defaults.
            limit: 5,
            value: 0,
            glyph: "icon-star",
            offglyph: "icon-ban",
            emptyglyph: false,
            coloroff: "gray",
            coloron: "gold",
            size: "1.0em",
            padding: "0 3px",
            cursor: "default",
            readonly: false,
            onClick: function () {},
            endofarray: "idontmatter"
        }, options );
        var style = "";
        style = style + "font-size:" + settings.size + "; ";
        style = style + "color:" + settings.coloroff + "; ";
        style = style + "cursor:" + settings.cursor + "; ";
        style = style + "padding:" + settings.padding + "; ";

        if (method == 'create') {
            // Initialize the data-rating property
            this.each(function() {
                attr = $(this).attr('data-rating');
                if (attr === undefined || attr === false) { $(this).attr('data-rating',settings.value); }
            });
            // Clear out old ratings
            this.empty();

            // Add 'no rating' glyph
            this.append('<span title="' + get_string('removerating', 'artefact.comment') + '" tabindex="0" data-value="0" class="ratingicon icon ' + settings.offglyph + '" style="' + style + '" aria-hidden="false"></span>');

            // Loop through the glyphs
            for (var i = 0; i < settings.limit; i++) {
                this.append('<span  title="' + get_string('ratingoption', 'artefact.comment', i+1, settings.limit) + '" tabindex="0" data-value="' + (i+1) + '" class="ratingicon icon ' + settings.glyph + '" style="' + style + '" aria-hidden="false"></span>');
            }

            // Paint the glyphs
            this.each(function() { paint($(this)); });
        }

        if (method == 'set') {
            this.attr('data-rating',options);
            this.each(function() { paint($(this)); });
        }

        if (method == 'get') {
            return this.attr('data-rating');
        }

        // Register the click events
        this.find("span.ratingicon").on("click", function() {
            if (settings.readonly !== true) {
                rating = $(this).attr('data-value')
                $(this).parent().attr('data-rating',rating);
                paint($(this).parent());
                settings.onClick.call( $(this).parent() );
            }
        });

        function paint(div) {
            rating = parseInt(div.attr('data-rating'));
            // If there is an input in the div lets set it's value
            div.parent().find("input").val(rating);
            div.find("span.ratingicon").each(function() {
                // Now paint the glyphs
                var rating = parseInt($(this).parent().attr('data-rating'));
                var value = parseInt($(this).attr('data-value'));
                if (value > rating || (value == 0 && rating > 0)) {
                    $(this).css('color',settings.coloroff);
                    if (settings.emptyglyph) {
                        if ($(this).hasClass(settings.glyph)) {
                            // need to add the '-o' before the end for thumbs
                            if (settings.glyph == 'icon-thumbs-up') {
                                $(this).removeClass(settings.glyph).addClass('icon-thumbs-o-up');
                            }
                            else {
                                $(this).removeClass(settings.glyph).addClass(settings.glyph + '-o');
                            }
                        }
                    }
                }
                else {
                    $(this).css('color',settings.coloron);
                }
            });
        }
    };
}( jQuery ));
