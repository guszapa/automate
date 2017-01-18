/*
 *  jQuery Ellipsis
 *  Mnigrele/Emateu: 8 abril 2011. Aportes de Ekupelian
 *  No copyright
 *
 */

(function($){

    $.fn.ellipsis = function(conf) {
        return this.each(function() {
            setup($(this), conf);
        });
    };

    function setup(element$, conf) {

        conf = $.extend({
            lines: 2
        }, conf || {} );

        var wrapper = {};
        wrapper.$ = element$;
        wrapper.display = wrapper.$.css("display");
        wrapper.$.css("display", "block");
        wrapper.text = $.trim(wrapper.$.text());
        wrapper.textArr = wrapper.text.split(" ");
        wrapper.width = wrapper.$.width();
        wrapper.lineHeight = wrapper.$.css("line-height");

        var line_height = parseInt(wrapper.lineHeight, 10);

        if ( (wrapper.$.height()/line_height) <= 2 ) {
            return false;
        }

        var transWrapper = $("<span>").css({
            "font-size": wrapper.$.css("font-size"),
            "line-height": wrapper.lineHeight,
            "font-weight": wrapper.$.css("font-weight"),
            "position": "absolute",
            "top" : "-99999px"
        })
        .appendTo("body");

        var length = wrapper.textArr.length;
        for (var i = 1; i <= length; i += 1) {
            if ( transWrapper.text(wrapper.textArr.slice(0, i).join(" ")).width() >= (wrapper.width*conf.lines) ) {
                wrapper.textArr = $.merge(wrapper.textArr.slice(0, i), []);
                break;
            }
        }

        transWrapper.width(wrapper.width).css("display", "block");
        var k = 1;
        while ( parseInt(transWrapper.height()/line_height) > conf.lines ) {
            transWrapper.text( wrapper.textArr.slice(0, -k).join(" ")+"..." );
            k += 1;
        }

        wrapper.$.text( wrapper.textArr.slice(0, -k).join(" ")+"..." );
        wrapper.$.css("display", wrapper.display);
        transWrapper.remove();

        return true;
    }

}(jQuery));