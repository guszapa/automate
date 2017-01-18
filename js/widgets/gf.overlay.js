;(function($){

$.widget('gf.overlay', {

    options: {
        duration: 250,
        hideOpacity: 0,
        showOpacity: 0.7
    },

    _create: function(){
        this.element.addClass('gf-overlay').css('opacity', this.options.hideOpacity).height($(document).height());
        $(window).bind('resize', $.proxy(function () {
            $(this).width($(document).width());
            $(this).height($(document).height());
        }, this));
    },

    destroy: function(){
        this.element.hide();
        this.element.remove();
        $.Widget.prototype.destroy.apply(this, arguments);
    },

    show: function(){
        this.element.fadeTo(this.options.duration, this.options.showOpacity);
    },

    hide: function(){
        this.element.fadeTo(this.options.duration, this.options.hideOpacity);
    }

});

})(jQuery);