/**
 * jQuery-UI Widget zur Anzeige und zum Betrieb des GlÃ¼cksrads
 *
 * Events:
 * =======
 *
 * wheelbeforestartrotate: Parameter(info, item|undefined, options)
 *      -> triggers before wheel is started to rotating
 *
 * wheelafterstartrotate: Parameter(info, item|undefined, options)
 *      -> triggers after wheel is started to rotate
 *
 * wheelfinish: Parameter(info, item, options)
 *      -> triggers after rotation is complete
 *
 */
;(function($){

$.widget('gf.wheel', {

    options: {
        url          : '/path/to/go',
        imgPath      : '/resource/to/go',
        types        : ['common','uncommon','rare'],
        debug        : false,
        size         : 20,
        blankImg     : '/img/blank.gif',
        smallImg     : [72, 52],
        largeImg     : [125, 125],
        payoutAmount : 0,
        rotateAmount : 0,
        errorCode   : {
            0: 'ERROR',
            1: 'RESET',
            2: 'ANNOTATION',
            3: 'SUCCESS'
        }
    },

    _create: function(){
        $('#rotateButton').show();
        this._info = {};
    },

    destroy: function(){
        $.Widget.prototype.destroy.apply(this, arguments);
    },

    continueRotation: function(startPosition, delay, maxRuntime)
    {
        this._startPosition = Number(startPosition);
        this._delay = Number(delay);
        this._maxRuntime = Number(maxRuntime);

        this._info = $.extend(this._info || {}, {
            currentPosition: this._startPosition,
            delay: this._delay,
            maxRuntime: this._maxRuntime,
            duration: 0,
            steps: 0,
            maxSteps: null
        });
        this.element.queue('rotate', $.proxy(this._addRotateEffect, this)).dequeue('rotate');
    },

    // LÃ¤sst das GlÃ¼cksrad rotieren
    rotate: function()
    {
        if(this._info.items == undefined) throw new Error('no wheel info loaded');
        return $.ajax({
            element: this.element,
            dataType: 'xml',
            type: 'get',
            data: {act: 'start', id: this._id},
            url: this.options.url,
            cache: false,
            beforeSend: $.proxy(this._rotateBeforeSendHandler, this),
            success: $.proxy(this._rotateSuccessHandler, this),
            complete: $.proxy(this._rotateCompleteHandler, this)
        });
    },

    _messageResponse: function(message, status) {
        status = Number(status);
        if (message.length > 0) {
            if (this.options.errorCode[status] != undefined) {
                $(document.createElement('div'))
                .attr('title', this.options.errorCode[status])
                .text(message).appendTo('body').layer({
                    position: {
                        at: 'center center',
                        my: 'center center',
                        offset: "0 30",
                        of: this.element
                    },
                    autoOpen: false,
                    modal: true
                }).layer('open', 1000)
                .bind('layerclose', function(event, dfd){
                    $(event.target).detach();
                });
            }
            return true;
        }
        return false;
    },

    _rotateBeforeSendHandler: function(){
        this._trigger('beforestartrotate', null, [this._info, this._info.items[Number(this._info.currentPosition) - 1], this.options]);
    },

    _rotateSuccessHandler: function(data){
        var $msg    = $(data).find('message');

        var message = $msg.text();
        var status  = $msg.attr('status');
        if (message.length > 0) {
            this._messageResponse(message, status);
            
            if (status == 1) {
                this.load();
            }
            return;
        }

        this._startPosition = Number($(data).find('data').attr('startPosition'));
        this._delay = Number($(data).find('data').attr('delay'));
        this._maxRuntime = Number($(data).find('data').attr('maxRuntime'));

        this._info = $.extend(this._info || {}, {
            currentPosition: this._startPosition,
            delay: this._delay,
            maxRuntime: this._maxRuntime,
            duration: 0,
            steps: 0,
            maxSteps: null
        });
        this.element.queue('rotate', $.proxy(this._addRotateEffect, this)).dequeue('rotate');
    },

    _rotateCompleteHandler: function(){
        this._trigger('afterstartrotate', null, [this._info, this._info.items[Number(this._info.currentPosition) - 1], this.options]);
    },

    _numberToString: function(num){
        return num<10?'0'+String(num):String(num);
    },

    _addDelay: function(delay){
        this._info.delay += delay;
    },

    _checkRuntime: function(){
        return this._info.maxRuntime > this._info.duration || this._info.maxSteps > this._info.steps;
    },

    _calculateNextStep: function(){
        this._info.duration += this._info.delay;
        this._addDelay(Math.abs(Math.tan(this._info.delay/this._info.maxRuntime)*this._info.maxRuntime)/this._delay);
        this._info.steps++;
    },

    _nextItem: function(){
        this.element.delay(this._info.delay, 'rotate').queue('rotate', $.proxy(this._addRotateEffect, this)).dequeue('rotate');
    },

    _finish: function(){
        this._trigger('finish', null, [this._info, this._info.items[Number(this._info.currentPosition) - 1], this.options]);
    },

    forcePosition: function(position)
    {
        this._info = $.extend(this._info || {}, {
            currentPosition: position,
            delay: 0,
            maxRuntime: 0,
            duration: 0,
            steps: 0,
            maxSteps: 0
        });
        this.element.queue('rotate', $.proxy(this._addRotateEffect, this)).dequeue('rotate');
    },

    _addRotateEffect: function(){
        var prev = this._info.currentPosition;
        var curr = 1;

        if(prev < this.options.size){
            curr = ++this._info.currentPosition;
        }

        else{
            this._info.currentPosition = 1;
        }

        this._calculateNextStep();

        $(this.element).children('.wof-animation')
                .removeClass('wof-anim-' + this._numberToString(prev))
                .addClass('wof-anim-' + this._numberToString(curr));

        if(this._checkRuntime()){
            if(this._info.maxSteps != null) this._info.delay += 200;
            this._nextItem();
        }

        else if(this._info.maxSteps == null){
            this._info.maxSteps = this._info.steps + 5;
            this._nextItem();
        }

        else{
            this._finish();
        }
    },

    // BefÃ¼lle das GlÃ¼cksrad mit neuen Items und Platten
    load: function(){
        this._info.items = [];
        return $.ajax({
            element: this.element,
            dataType: 'xml',
            type: 'get',
            data: {act: 'load'},
            url: this.options.url,
            cache: false,
            success: $.proxy(this._loadItemsSuccessHandler, this)
        });
    },

    _loadItemsSuccessHandler: function(data){
        var $msg    = $(data).find('message');

        var message = $msg.text();
        var status  = $msg.attr('status');
        if (message.length > 0) {
            if (status == 1) {
                this._trigger('messagereset', null, [$(data).find('stock'), $(data).find('history')]);
                this.load();
            } else {
                this._messageResponse(message, status);
            }
            return;
        }

        this._id               = $(data).find('wheel').attr('id');
        this._loadItemDeferrer = $.Deferred();
        this._lastItemNumber   = $(data).find('plate').length;

        $(data).find('wheel>plate').each($.proxy(this._includeEachPlates, this));

        var $prices = $(data).find('history>price');
        var prices  = [];

        if ($prices.length > 0) {
            $prices.each(function(i, n) {
                prices.push({
                    time   : $(n).attr('time'),
                    date   : $(n).attr('date'),
                    rarity : $(n).attr('rarity'),
                    image  : $(n).attr('image'),
                    uuid   : $(n).attr('uuid'),
                    text   : $(n).text()
                });
            });
        }
        this._trigger('afterload', null, [this._info, this.options, this._loadItemDeferrer, data]);
    },

    _includeEachPlates: function(index, plate){
        var id = $(plate).attr('id');
        var $item = $(plate).find('item');
        var plateName = $item.attr('plate');
        var loca = $item.find('loca').text();
        var hash = $item.find('img').attr('src');

        $.proxy(this._includePlate, this)(id, plateName, loca, hash);
    },
    
    _includePlate: function(id, plateName, loca, hash){
        var $li = this.element.find('li.wof-' + id);
        var $img = $(new Image()).attr({
            title: loca,
            src: this.options.imgPath + '/' + hash + '-small.png'
        }).addClass('gf-wheel-item').hide();

        // LÃ¶schen von CSS-Klassen
        $li.removeClass('wof-' + this.options.types.join(' wof-'));

        // Auswahl des richtigen Elements im GlÃ¼cksrad
        $li.addClass('wof-' + plateName).empty();
        if(this.options.debug){
            $li.html('<span class="gf-wheel-debug-number">' + id + '</span>');
        }

        if($.browser.msie){
            $img.css({
                backgroundImage: 'url(' + $img.attr('src') + ')',
                width: this.options.smallImg[0]+"px",
                height: this.options.smallImg[1]+"px"
            }).attr({src: this.options.blankImg});
        }

        $img.appendTo($li);

        this._info.items.push({
            id: id,
            plate: plateName,
            loca: loca,
            hash: hash
        });

        $img.bind('load', $.proxy(function(){
            if(this._lastItemNumber == this._info.items.length){
                this._loadItemDeferrer.resolve('loaded');
            }
        }, this));
    },

    // Generiert einen neuen GlÃ¼cksradaufbau
    generate: function(){
        this._info.items = [];
        return $.ajax({
            element: this.element,
            dataType: 'xml',
            type: 'get',
            data: {act: 'generate'},
            url: this.options.url,
            cache: false,
            success: $.proxy(this._loadItemsSuccessHandler, this)
        });
    },

    // BefÃ¼lle das GlÃ¼cksrad mit neuen Items und Platten nach dem Gewinn
    payout: function(){
        this._info.items = [];
        return $.ajax({
            element: this.element,
            dataType: 'xml',
            type: 'get',
            data: {act: 'payout'},
            url: this.options.url,
            cache: false,
            success: $.proxy(this._loadItemsSuccessHandler, this)
        });
    }
});

})(jQuery);