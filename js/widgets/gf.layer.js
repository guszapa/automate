;(function($){

$.widget('gf.layer', {

    options: {
        type       : 'thin',
        draggable  : false,
        fixed      : false,
        position   : null,
        title      : null,
        autoOpen   : true,
        effect     : 'fade',
        closeButton: true,
        modal      : false
    },

    _create: function(){

        // save original element
        this._cachedElement = this.element.clone();

        // protect contents
        var _title = this.options.title || $(this.element).attr('title');
        var _text = $(this.element).html();

        // setup element
        this.element.addClass('gf-widget-layout').empty().removeAttr('title')

        if(this.options.modal){
            $('<div>').addClass('gf-layer-overlay').overlay()
            .appendTo(this.element).bind('click', $.proxy(this._triggerClose, this));
        }

        var $layer = $('<div>').addClass('gf-layer gf-layer-' + this.options.type).appendTo(this.element);

        $layer.position(
            $.extend({
                at: 'center center',
                my: 'center center',
                offset: '0 0',
                of: '.gf-attach-element'
            }, this.options.position || {})
        );


        // draggable enable/disable
        if(this.options.draggable) this.element.children('.gf-layer').css('cursor', 'move').draggable();
        
        if (this.options.fixed) {
            $layer.css('position', 'fixed');
        }

        // frames
        this.topElement    = $('<div>').addClass('gf-layer-top').appendTo(this.element.children('.gf-layer'));
        this.middleElement = $('<div>').addClass('gf-layer-middle').appendTo(this.element.children('.gf-layer'));
        this.bottomElement = $('<div>').addClass('gf-layer-bottom').appendTo(this.element.children('.gf-layer'));

        $('<a>').addClass('gf-widget-close').bind('click', $.proxy(this._triggerClose, this)).appendTo(this.topElement);

        // text decoration
        $('<div>').addClass('gf-layer-title').text(_title).appendTo(this.element.children('.gf-layer').find('.gf-layer-middle'));
        $('<div>').addClass('gf-layer-description').html(_text).appendTo(this.element.children('.gf-layer').find('.gf-layer-middle'));

        if(this.options.autoOpen) {
            this.open('slow');
        }
    },

    _triggerClose: function(){
        this.close();
    },

    destroy: function(){
        this._cachedElement.after(this.element);
        this.element.remove();
        $.Widget.prototype.destroy.apply(this, arguments);
    },
    
    open: function(duration){
        var dfd = $.Deferred();
        this.element.children('.gf-layer-overlay').overlay('show');
        this.element.children('.gf-layer').show(this.options.effect, duration, $.proxy(function(){
            dfd.resolve('open');
        }, this));
        this._trigger('open', null, [dfd]);
    },

    close: function(duration){
        var dfd = $.Deferred();
        this.element.children('.gf-layer-overlay').overlay('hide');
        this.element.children('.gf-layer').hide(this.options.effect, duration, $.proxy(function(){
            dfd.resolve('close');
        }, this));
        this._trigger('close', null, [dfd]);
    }
});

})(jQuery);