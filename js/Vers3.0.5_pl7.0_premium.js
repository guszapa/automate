/**
 * Overlay
 */
Zet.declare('Overlay', {
    defineBody : function(that){
        /**
         * jQuery Overlay
         *
         * @property {jQuery}
         */
        var _overlay;

        /**
         * jQuery Black Overlay
         *
         * @property {jQuery}
         */
        var _blackOverlay;

        /**
         * The content
         *
         * @property {Mixed}
         */
        var _content;

        /**
         * Css prefix of the class used for the overlay
         *
         * @property {String}
         */
        var _cssPrefix;

        Zet.public({
            /**
             * Create a new overlay
             *
             * @param   {String} cssPrefix
             * @param   {String} zIndex
             * @returns {void}
             */
            construct: function(cssPrefix, zIndex)
            {
                if (cssPrefix == undefined) {
                    cssPrefix = 'overlay';
                }

                _cssPrefix = cssPrefix;

                if (zIndex == undefined) {
                    zIndex = 9999;
                }

                _overlay = jQuery('\n\
                    <div class="' + _cssPrefix + '-container"\n\
                        style="z-index: ' + (zIndex + 1) + ';">\n\
                        <div class="' + _cssPrefix + '-top"></div>\n\
                        <div class="' + _cssPrefix + '-bottom"></div>\n\
                        <div class="' + _cssPrefix + '-middle"></div>\n\
                        <div class="' + _cssPrefix + '-content"></div>\n\
                    </div>\n\
                ');

                div = jQuery('<div class="close"/>').bind('click', that.hide);

                _overlay.append(div);


                _blackOverlay = jQuery('<div style="\n\
                    z-index: ' + zIndex + ';\n\
                    position: absolute;\n\
                    left: 0;\n\
                    top: 0;\n\
                    width: 100%;\n\
                    height: 100%;\n\
                    background-color: #000000;\n\
                "/>');

                _overlay.hide();
                _blackOverlay.hide()
                             .bind('click', that.hide);

                jQuery('body').append(_blackOverlay)
                              .append(_overlay);
            },

            /**
             * Get the overlay
             *
             * @returns {jQuery}
             */
            getOverlay: function()
            {
                return _overlay;
            },

            /**
             * Show the overlay
             *
             * @returns {void}
             */
            show: function()
            {
                _overlay.find('div.' + _cssPrefix + '-content').append(_content);
                _overlay.css('top', (jQuery(window).scrollTop() + 20) + 'px');

                jQuery(window).scroll(function(){
                    _overlay.stop().animate({'top': (jQuery(window).scrollTop() + 20) + 'px'}, 'slow');
                });

                that.setBlackBackgroundDimensions();
                _blackOverlay.fadeTo('slow', 0.7);

                _overlay.show();
            },

            /**
             * Hide the overlay
             *
             * @returns {void}
             */
            hide: function()
            {
                _blackOverlay.fadeTo('slow', 0.0, function(){_blackOverlay.hide();});
                jQuery(window).unbind('scroll');

                _overlay.hide();
            },

            /**
             * Set the content
             *
             * @param   {Mixed} content
             * @returns {void}
             */
            setContent: function(content)
            {
                _content = content;
                return that;
            },

            /**
             * Set the content height
             *
             * @returns {void}
             */
            setContentHeight: function(height, diffHeight, animate)
            {
                var topHeight    = parseInt(_overlay.find('div.' + _cssPrefix + '-top').css('height'));
                var bottomHeight = parseInt(_overlay.find('div.' + _cssPrefix + '-bottom').css('height'));

                if (diffHeight == undefined) {
                    diffHeight = 58;
                }

                var oldHeight = parseInt(_overlay.find('div.' + _cssPrefix + '-content').css('height'));

                if (isNaN(oldHeight)) {
                    animate = false;
                }

                var from  = {property: oldHeight};
                var to    = {property: height};
                var raise = (height > oldHeight);

                if (from === to) {
                    return;
                }

                if (animate) {
                    jQuery(from).animate(to, {
                        duration: 250,
                        step: function(){
                            var currentHeight = Math.round(this.property);

                            if ((raise && currentHeight > height) || (!raise && currentHeight < height)) {
                                return;
                            }

                            _overlay.find('div.' + _cssPrefix + '-middle').css('height', (currentHeight - diffHeight) + 'px');
                            _overlay.find('div.' + _cssPrefix + '-content').css('height', currentHeight + 'px');
                            _overlay.css('height', (currentHeight + topHeight + bottomHeight - diffHeight) + 'px');
                        },
                        complete: function(){
                            _overlay.find('div.' + _cssPrefix + '-middle').css('height', (height - diffHeight) + 'px');
                            _overlay.find('div.' + _cssPrefix + '-content').css('height', height + 'px');
                            _overlay.css('height', (height + topHeight + bottomHeight - diffHeight) + 'px');
                        }
                    });
                } else {
                    _overlay.find('div.' + _cssPrefix + '-middle').css('height', (height - diffHeight) + 'px');
                    _overlay.find('div.' + _cssPrefix + '-content').css('height', height + 'px');
                    _overlay.css('height', (height + topHeight + bottomHeight - diffHeight) + 'px');
                }
            },

            /**
             * Set the background dimensions of the black overlay
             *
             * @returns {void}
             */
            setBlackBackgroundDimensions: function()
            {
                var contentWidth = Math.max(
                    jQuery(document).width(),
                    jQuery(window).width(),
                    document.documentElement.clientWidth
                );

                var contentHeight = Math.max(
                    jQuery(document).height(),
                    jQuery(window).height(),
                    document.documentElement.clientHeight
                );

                _blackOverlay.css('width', contentWidth + 'px');
                _blackOverlay.css('height', contentHeight + 'px');
            }
        });
    }
});

/**
 * overlay
 */
Zet.declare('Premium.Hint' , {
    superclass: Overlay,

    defineBody: function(that){

        /**
         * Translated strings
         *
         * @property {Object}
         */
        var _translations;

        /**
         * Game base link
         *
         * @property {String}
         */
        var _baseLink;

        Zet.public({

            /**
             * Create a new hint
             *
             * @param   {String} baseLink
             * @returns {void}
             */
            construct: function(baseLink)
            {
                _baseLink = baseLink;
                that.inherited(['hint', 10001]);
            },

            /**
             * Hide the overlay
             *
             * @returns {void}
             */
            hide: function()
            {
                that.inherited();
                jQuery('.hint-container').remove();
            },

            /**
             * Set up the hint
             *
             * @param   {String}       itemName
             * @returns {Premium.Hint}
             */
            init: function(itemName)
            {
                div = jQuery('<div/>');
                div.append(jQuery('<span/>').text(_translations.buySuccessful))
                   .append(jQuery('<span/>').text(' ' + itemName))
                   .append(jQuery('<br/>'))
                   .append(jQuery('<br/>'))
                   .append(jQuery('<span/>').text(_translations.buySuccessfulActivationHint))
                   .append(jQuery('<br/>'))
                   .append(jQuery('<br/>'))
                   .append(jQuery('<input type="checkbox" id="premiumHint"/>')
                       .bind('click', function(e){
                           if (jQuery('input#premiumHint:checked').val() === 'on') {
                               jQuery('div#saveHint').css('visibility', 'visible');
                           } else {
                               jQuery('div#saveHint').css('visibility', 'hidden');
                           }
                       })
                   )
                   .append(jQuery('<label for="premiumHint" class="premiumHint"/>').text(_translations.doNotShowHintAgain))
                   .append(jQuery('<br/>'))
                   .append(jQuery('<br/>'))
                   .append(jQuery('<div id="saveHint"/>')
                       .css('text-align', 'center')
                       .css('visibility', 'hidden')
                       .append(jQuery('<a href="#" class="awesome-button"/>')
                           .text(_translations.save)
                           .bind('click', function(e){
                               e.preventDefault();
                               var uri = _baseLink + '&a=disablePremiumHint&aj=1';
                               jQuery.get(uri, function(data){
                                   console.log(data);
                               })

                               that.hide();
                           })
                       )
                   )
                ;

                that.setContent(div);
                return that;
            },

            /**
             * Show the hint
             *
             * @returns {void}
             */
            show: function()
            {
                that.inherited();
                that.setContentHeight(jQuery('div.hint-content > div').height(), 20);
            },

            /**
             * Set the translations
             *
             * @param   {Object}       translations
             * @returns {Premium.Hint}
             */
            setTranslations: function(translations)
            {
                _translations = translations;
                return that;
            }
        });
    }
});

/**
 * Premium usage overlay
 */
Zet.declare('Premium.Overlay' , {
    superclass: Overlay,

    defineBody: function(that){
        /**
         * Items
         *
         * @property {Array}
         */
        var _items;

        /**
         * Premium shop
         *
         * @property {Premium.Shop}
         */
        var _shop;

        /**
         * Wether the shop was already initialized
         *
         * @property {Boolean}
         */
        var _shopInitialized;
        
        /**
         * shop overlay layout
         * 
         * Available: standard, sideShop
         */
        var _shopType;

        Zet.public({
            /**
             * Create a new item
             *
             * @param   {Array}   items
             * @param   {Integer} currentBalance
             * @param   {Boolean} rtl
             * @param   {String}  baseLink
             * @param   {Object}  translations
             * @param   {Boolean} showHint
             * @returns {void}
             */
            construct: function(stock, items, currentBalance, rtl, baseLink, paymentLink, translations, showHint)
            {
                _items = items;
                _shop = Premium.Shop(stock, items, null, null, null, currentBalance, rtl, baseLink, paymentLink, translations, showHint);
                _shop.setItemShowObserver(that.contentHeightChange);
                that.inherited();
            },
                    
            setShopType: function(type)
            {
                _shopType = type;
            },

            /**
             * Show the overlay
             *
             * @param   {Object} params
             * @returns {void}
             */
            show: function(params)
            {
                _shop.setParams(params);

                if (!_shopInitialized) {
                    switch (_shopType) {
                        case 'sideShop':
                            _shop.initSideMenu(that.getOverlay().find('div.overlay-content'), false);
//                          _shop.initSideMenu(that.getOverlay(), false);
                            jQuery('.overlay-container').addClass('premiumShop sideMenu');
                            break;

                        case 'standard': 
                        default:
                            _shop.init(that.getOverlay().find('div.overlay-content'), false);
                            break;
                    }

                    _shopInitialized = true;
                }

                that.inherited();
                that.setContentHeight(_shop.getDetailsHeight());
            },

            setItemStock: function(uuid, localStock, globalStock)
            {
                _shop.setItemStock(uuid, localStock, globalStock, _shopInitialized);
            },

            /**
             * Observer for content height changes
             *
             * @returns {void}
             */
            contentHeightChange: function()
            {
                that.setContentHeight(_shop.getDetailsHeight());
            }
        });
    }
});

/**
 * Premium shop
 */
Zet.declare('Premium.Shop' , {
    defineBody: function(that){
        /**
         * Shop container
         *
         * @property {Object}
         */
        var __shopContainer;

        /**
         * Stock
         *
         * @property {Object}
         */
        var _stock;

        /**
         * Items
         *
         * @property {Object}
         */
        var _items;

        /**
         * Categories
         *
         * @property {Object}
         */
        var _categories;

        /**
         * Category names
         *
         * @property {Object}
         */
        var _categoryNames;

        /**
         * Wether this is LTR or RTL
         *
         * @property {Boolean}
         */
        var _rtl;

        /**
         * Wether the successful hint is shown
         *
         * @property {Boolean}
         */
        var _showHint;

        /**
         * Wether the shop is read only or functional (admin tool option)
         *
         * @property {Boolean}
         */
        var _readOnly = false;

        /**
         * Translated strings
         *
         * @property {Object}
         */
        var _translations;

        /**
         * Itemlist container
         *
         * @property {jQuery}
         */
        var _itemListContainer;

        /**
         * Item details container
         *
         * @property {jQuery}
         */
        var _itemDetailsContent;

        /**
         * Extended description container
         *
         * @property {jQuery}
         */
        var _extendedDescriptionContainer

        /**
         * Currently active item uuid
         *
         * @property {String}
         */
        var _currentItemUuid;

        /**
         * Initial active category
         *
         * @property {String}
         */
        var _initActiveCategory;

        /**
         * Initial active uuid
         *
         * @property {String}
         */
        var _initActiveUuid;

        /**
         * Game base link
         *
         * @property {String}
         */
        var _baseLink;

        /**
         * Payment link
         *
         * @property {String}
         */
        var _paymentLink;

        /**
         * Gradient fade object
         *
         * @property {String}
         */
        var _fade;

        /**
         * Item show observer
         *
         * @property {Function}
         */
        var _itemShowObserver = null;

        /**
         * Wether a buy is in progress
         *
         * @property {Boolean}
         */
        var _buyInProgress = false;

        /**
         * Current balance
         *
         * @property {Integer}
         */
        var _currentBalance;

        /**
         * Activation parameters
         *
         * @property {Object}
         */
        var _params = {};

        /**
         * Wether packs can be activated and shows stock info
         *
         * @property {Boolean}
         */
        var _packOverride = false;

        /**
         * shop overlay layout
         * 
         * Available: standard, sideShop
         */
        var _shopType;

        Zet.public({
            /**
             * Create a new premium shop with categories
             *
             * @param   {Object}  stock
             * @param   {Object}  categories
             * @param   {Object}  categoryNames
             * @param   {String}  activeCategory
             * @param   {String}  activeUuid
             * @param   {Integer} currentBalance
             * @param   {Boolean} rtl
             * @param   {String}  baseLink
             * @param   {Object}  translations
             * @param   {Boolean} showHint
             * @returns {void}
             */
            construct: function(stock, categories, categoryNames, activeCategory, activeUuid, currentBalance, rtl, baseLink, paymentLink, translations, showHint)
            {
                if (categoryNames === null) {
                    _items      = categories;
                    _categories = null;
                } else {
                    var category, uuid;
                    _items = {};

                    for (category in categories) {
                        for (uuid in categories[category]) {
                            _items[uuid] = categories[category][uuid];
                        }
                    }

                    _categories = categories;
                }
                
                _stock              = stock;
                _categoryNames      = categoryNames;
                _initActiveCategory = activeCategory;
                _initActiveUuid     = activeUuid;
                _currentBalance     = currentBalance;
                _rtl                = rtl;
                _baseLink           = baseLink;
                _paymentLink        = paymentLink;
                _translations       = translations;
                _fade               = Gradient.Fade()
                _showHint           = showHint;
            },
            
            readOnly: function()
            {
                _readOnly = true;
                jQuery('.buyItem').remove();
                jQuery('.activateItem').remove();
            },

            disableShop: function()
            {
                _readOnly = true;
                jQuery('.buyItem').remove();
            },

            setPackOverride: function(packOverride)
            {
                _packOverride = packOverride;
            },

            disableActivate: function()
            {
                _readOnly = true;
                jQuery('.activateItem').remove();
            },
                    
            setShopType: function(type)
            {
                _shopType = type;
            },

            /**
             * Init the shop
             *
             * @param   {jQuery} container
             * @returns {void}
             */
            init: function(container)
            {
                switch (_shopType) {
                    case 'sideShop':
                        that.initSideMenu(container);
                        break;

                    case 'standard': 
                    default:
                        that.initStandardMenu(container);
                        break;
                }
            },
                    
            /**
             * Init the shop
             *
             * @param   {jQuery} container
             * @returns {void}
             */
            initStandardMenu: function(container)
            {
                var li, a, ul, div, category, item, hasItems, firstCategory = null;

                if (_initActiveCategory !== null) {
                    firstCategory = _initActiveCategory;
                }

                // Create the tab list
                if (_categories !== null) {
                    ul = jQuery('<ul class="tabList"/>');

                    for (category in _categoryNames) {
                        hasItems = false;

                        for (item in _categories[category]) {
                            if (_readOnly || that.getItemFromUuid(item).buyable) {
                                hasItems = true;
                                break;
                            }
                        }

                        if (!hasItems) {
                            continue;
                        }

                        li = jQuery('<li/>');
                        li.attr('id', 'category-tab-' + category);

                        a = jQuery('<a/>');
                        a.attr('title', _categoryNames[category].description);
                        a.attr('href', '#');
                        a.bind('click', {category: category}, function(e){
                            e.preventDefault();

                            jQuery(this).parent().parent()
                                        .find('li a.active')
                                        .removeClass('active');
                            jQuery(this).addClass('active');

                            that.showCategory(e.data.category);
                        });

                        if (firstCategory === null || category === _initActiveCategory) {
                            firstCategory = category;
                            a.addClass('active');
                        }

                        a.append(jQuery('<span class="left"/>'));
                        a.append(jQuery('<span class="center"/>').text(_categoryNames[category].name));
                        a.append(jQuery('<span class="right"/>'));

                        li.append(a);
                        ul.append(li);
                    }

                    container.append(ul);
                }

                _initActiveCategory = null;

                // Create the details container
                _itemDetailsContent = jQuery('<div class="content"/>');
                _itemDetailsContent.append(jQuery('<div class="title"/>'));
                _itemDetailsContent.append(jQuery('<img width="125" height="125"/>'));
                _itemDetailsContent.append(jQuery('<div class="data price"/>').attr('title', _translations.price).append(jQuery('<div/>')));
                _itemDetailsContent.append(jQuery('<div class="data inventory"/>').attr('title', _translations.inventory).append(jQuery('<div/>')));
                _itemDetailsContent.append(jQuery('<div class="data duration"/>').attr('title', _translations.duration).append(jQuery('<div/>')));
                _itemDetailsContent.append(jQuery('<div class="data cooldown"/>').attr('title', _translations.cooldown).append(jQuery('<div/>')));
                _itemDetailsContent.append(jQuery('<div class="description"/>'));
                _itemDetailsContent.append(
                    jQuery('<a href="#" class="extendedDescription"/>')
                        .text(_translations.extendedDescriptionShow)
                        .bind('click', function(e){
                            e.preventDefault();

                            var extendedDescription = that.getItemFromUuid(_currentItemUuid).extendedDescription;

                            if (extendedDescription.indexOf('link:') === 0) {
                                window.open(extendedDescription.substring(5));
                            } else {
                                _itemListContainer.hide();
                                _extendedDescriptionContainer.show();
                                _extendedDescriptionContainer.find('div').html(that.toHtml(extendedDescription));
                            }
                        })
                );

                _itemDetailsContent.append(
                    jQuery('<a href="#" class="buyItem awesome-button"/>')
                        .text(_translations.buy)
                        .bind('click', function(e){
                            e.preventDefault();
                                
                            if (_buyInProgress || jQuery(this).hasClass('disabled')) {
                                return;
                            }

                            _buyInProgress = true;
                            var button     = jQuery(this);
                            var uri        = button.attr('href');

                            that.setActivateButtonStatus();
                            
                            _fade.animate(button, true);

                            jQuery.get(uri, function(data) {
                                if (data.bought) {
                                    jQuery('body').trigger('buy', data);
                                    _currentBalance = data.balance;
                                    jQuery('.currentBalance').text(data.balance.totalCurrency)
                                                             .parent().attr('title',
                                                                $P.sprintf(_translations.currencyBought, data.balance.boughtCurrency) + " | " +
                                                                $P.sprintf(_translations.currencyFree, data.balance.freeCurrency) + " (" + _translations.localAvailable 
                                                                + data.balance.freeCurrencyLocal + ")"
                                                             );

                                    for (uuid in data.items) {
                                        var item = that.getItemFromUuid(uuid);
                                        
                                        if (item !== null) {
                                            if (data.items[uuid].globalStock === undefined || isNaN(data.items[uuid].globalStock)) {
                                                data.items[uuid].globalStock = 0;
                                            }

                                            if (data.items[uuid].localStock === undefined || isNaN(data.items[uuid].localStock)) {
                                                data.items[uuid].localStock = 0;
                                            }
                                            
                                            _stock[item.uuid].global = Number(_stock[item.uuid].global) + Number(data.items[uuid].globalStock);
                                            _stock[item.uuid].local  = Number(_stock[item.uuid].local)  + Number(data.items[uuid].localStock);

                                            if (_currentItemUuid === uuid) {
                                                // TODO Umbau auf itemStock-Widget
                                                _itemDetailsContent.find('div.inventory>div')
                                                    .text(_stock[item.uuid].global + _stock[item.uuid].local)
                                                    .attr('title',
                                                        _translations.localAvailable + ' ' + _stock[item.uuid].local + " | " +
                                                        _translations.globalAvailable + ' ' + _stock[item.uuid].global
                                                    );
                                            }

                                            // TODO Umbau auf itemStock-Widget
                                            _itemListContainer.find('#relic-item-' + uuid + ' div.inventory')
                                                .text(_stock[item.uuid].global + _stock[item.uuid].local)
                                                .attr('title',
                                                    _translations.localAvailable + ' ' + _stock[item.uuid].local + " | " +
                                                    _translations.globalAvailable + ' ' + _stock[item.uuid].global
                                                );
                                            _showHint = data.showHint;

                                            if (_showHint == true) {
                                                hint = Premium.Hint(_baseLink);
                                                hint.setTranslations(_translations).init(item.name).show();
                                            }
                                        }
                                    }
                                } else {
                                    jQuery('.currentBalance').text(data.balance.totalCurrency)
                                                             .parent().attr('title',
                                                                $P.sprintf(_translations.currencyBought, data.balance.boughtCurrency) + " | " +
                                                                $P.sprintf(_translations.currencyFree, data.balance.freeCurrency) + " (" + _translations.localAvailable 
                                                                + data.balance.freeCurrencyLocal + ")"
                                                             );
                                    alert(data.error);
                                }

                                _fade.setFadeObserver(function(fadeIn){
                                    if (!fadeIn) {
                                        _buyInProgress = false;
                                        _fade.setFadeObserver(null);

                                        if (item !== null) {
                                            that.setBuyButtonStatus();
                                            that.setActivateButtonStatus();
                                        }
                                    }
                                });

                                if (_fade.isFadeComplete()) {
                                    _fade.animate(button, false);
                                } else {
                                    _fade.fadeOutOnComplete();
                                }
                            }, 'json');
                        })
                );
                    
                _itemDetailsContent.append(
                    jQuery('<a href="#" class="activateItem awesome-button"/>')
                        .text(_translations.activate)
                        .bind('click', function(e){
                            if (_buyInProgress || jQuery(this).hasClass('disabled')) {
                                e.preventDefault();
                                return;
                            }

                            var uri = jQuery(this).attr('href');
                            var key, value;

                            for (key in _params) {
                                value = _params[key];

                                uri += (uri.indexOf('?') > -1 ? '&' : '?')
                                    +  'param.' + encodeURIComponent(key)
                                    +  '=' +  encodeURIComponent(value);
                            }

                            jQuery(this).attr('href', uri);
                        })
                );

                div = jQuery('<div id="itemDetails"/>');
                div.append(jQuery('<div class="top"/>'));
                div.append(jQuery('<div class="middle"/>').append(_itemDetailsContent));
                div.append(jQuery('<div class="bottom"/>'));
                container.append(div);

                // Create the item list container
                _itemListContainer = jQuery('<ul class="itemList"/>');
                container.append(_itemListContainer);

                // Create the extended description container
                _extendedDescriptionContainer = jQuery('<div id="extendedDescriptionContainer"/>').hide();
                _extendedDescriptionContainer.append(jQuery('<div/>')).append(
                    jQuery('<a href="#"/>')
                        .text(_translations.extendedDescriptionClose)
                        .bind('click', function(e){
                            e.preventDefault();

                            _extendedDescriptionContainer.hide();
                            _itemListContainer.show();
                        })
                );
                container.append(_extendedDescriptionContainer);

                // Show the first category
                if (_categories !== null) {
                    that.showCategory(firstCategory);
                } else {
                    that.showCategory(null);
                }

                // Register itself with the premium subject
                PremiumSubject.registerObserver(that);
            },
            
            /**
             * Init the shop with the new layout
             *
             * @param   {jQuery} container
             * @returns {void}
             */
            initSideMenu: function(container)
            {
                var li, a, ul, div, category, item, hasItems, firstCategory = null;
                
                _shopContainer = container;

                if (_initActiveCategory !== null) {
                    firstCategory = _initActiveCategory;
                }
                
                head   = jQuery('<div class="header"/>');
                body   = jQuery('<div class="body"/>');
                footer = jQuery('<div class="footer"/>');

                container.append(head);
                container.append(body);
                container.append(footer);
                
                container          = body;
                activeCategoryName = jQuery('<div class="activeCategoryName" />');
                headerRightButtons = jQuery('<div class="headerRightButtons" />');
                currentBalance     = jQuery('<div />');
                currencyImage      = jQuery('<img src="img/premium/coins.png" />');
                getPremiumCurrency = jQuery('<a href="' + _paymentLink + '" class="getPremiumCurrency awesome-button menu" />');

                head.append(activeCategoryName);
                head.append(headerRightButtons);
                headerRightButtons.append(currentBalance);
                headerRightButtons.append(getPremiumCurrency.text(_translations.getPremiumCurrency).attr('title', _translations.getPremiumCurrencyTooltip));
                
                currencyImage.addClass('currencyImage');
                currentBalance.addClass('accountBalance awesome-button menu')
                              .append(currencyImage)
                              .attr('title', $P.sprintf(_translations.currencyBought, _currentBalance.boughtCurrency) + " | " +
                                     $P.sprintf(_translations.currencyFree, _currentBalance.freeCurrency) + " (" + _translations.localAvailable 
                                     + _currentBalance.freeCurrencyLocal + ")")
                              .append(jQuery('<span class="currentBalance" />').text(_currentBalance.totalCurrency));

                // Create the tab list
                if (_categories !== null) {
                    ul = jQuery('<ul class="tabList"/>');

                    for (category in _categoryNames) {
                        hasItems = false;

                        for (item in _categories[category]) {
                            if (_readOnly || that.getItemFromUuid(item).buyable) {
                                hasItems = true;
                                break;
                            }
                        }

                        if (!hasItems) {
                            continue;
                        }

                        li = jQuery('<li/>');
                        li.attr('id', 'category-tab-' + category);

                        a = jQuery('<a/>');
                        a.addClass('awesome-button menu');
                        
                        a.attr('title', _categoryNames[category].description);
                        a.attr('href', '#');
                        a.bind('click', {category: category}, function(e){
                            e.preventDefault();

                            jQuery(this).parent().parent()
                                        .find('li a.active')
                                        .removeClass('active');
                            jQuery(this).addClass('active');

                            that.showCategory(e.data.category);
                        });

                        if (firstCategory === null || category === _initActiveCategory) {
                            firstCategory = category;
                            a.addClass('active');
                            activeCategoryName.text(_categoryNames[category].name);
                        }

                        if (_categoryNames[category].image !== undefined) {
                            a.append(jQuery('<div class="' + _categoryNames[category].key + '" style="background-image: url(/item-images/' + _categoryNames[category].image + '-large.png); background-position: center center; background-size: 45px 45px;"/>'));
                        } else {
                            a.append(jQuery('<div class="' + _categoryNames[category].key + '"/>'));
                        }

                        li.append(a);
                        ul.append(li);
                    }

                    container.append(ul);
                }

                _initActiveCategory = null;

                // Create the details container
                _itemDetailsContent = jQuery('<div class="content"/>');
                _itemDetailsContent.append(jQuery('<div class="title"/>'));
                _itemDetailsContent.append(jQuery('<img width="125" height="125"/>'));
                _itemDetailsContent.append(jQuery('<div class="data price"/>').attr('title', _translations.price).append(jQuery('<div/>')));
                _itemDetailsContent.append(jQuery('<div class="data inventory"/>').attr('title', _translations.inventory).append(jQuery('<div/>')));
                _itemDetailsContent.append(jQuery('<div class="data duration"/>').attr('title', _translations.duration).append(jQuery('<div/>')));
                _itemDetailsContent.append(jQuery('<div class="data cooldown"/>').attr('title', _translations.cooldown).append(jQuery('<div/>')));
                _itemDetailsContent.append(jQuery('<div class="description"/>'));
                _itemDetailsContent.append(
                    jQuery('<a href="#" class="extendedDescription"/>')
                        .text(_translations.extendedDescriptionShow)
                        .bind('click', function(e){
                            e.preventDefault();

                            var extendedDescription = that.getItemFromUuid(_currentItemUuid).extendedDescription;

                            if (extendedDescription.indexOf('link:') === 0) {
                                window.open(extendedDescription.substring(5));
                            } else {
                                _itemListContainer.hide();
                                _extendedDescriptionContainer.show();
                                _extendedDescriptionContainer.find('div').html(that.toHtml(extendedDescription));
                            }
                        })
                );

                _itemDetailsContent.append(
                    jQuery('<a href="#" class="buyItem awesome-button"/>')
                        .text(_translations.buy)
                        .bind('click', function(e){
                            e.preventDefault();
                                
                            if (_buyInProgress || jQuery(this).hasClass('disabled')) {
                                return;
                            }

                            _buyInProgress = true;
                            var button     = jQuery(this);
                            var uri        = button.attr('href');

                            that.setActivateButtonStatus();
                            
                            _fade.animate(button, true);

                            jQuery.get(uri, function(data) {
                                if (data.bought) {
                                    jQuery('body').trigger('buy', data);
                                    _currentBalance = data.balance;
                                    jQuery('.currentBalance').text(data.balance.totalCurrency)
                                                             .parent().attr('title',
                                                                $P.sprintf(_translations.currencyBought, data.balance.boughtCurrency) + " | " +
                                                                $P.sprintf(_translations.currencyFree, data.balance.freeCurrency) + " (" + _translations.localAvailable 
                                                                + data.balance.freeCurrencyLocal + ")"
                                                             );

                                    for (uuid in data.items) {
                                        var item = that.getItemFromUuid(uuid);
                                        
                                        if (item !== null) {
                                            if (data.items[uuid].globalStock === undefined || isNaN(data.items[uuid].globalStock)) {
                                                data.items[uuid].globalStock = 0;
                                            }

                                            if (data.items[uuid].localStock === undefined || isNaN(data.items[uuid].localStock)) {
                                                data.items[uuid].localStock = 0;
                                            }
                                            
                                            _stock[item.uuid].global = Number(_stock[item.uuid].global) + Number(data.items[uuid].globalStock);
                                            _stock[item.uuid].local  = Number(_stock[item.uuid].local)  + Number(data.items[uuid].localStock);

                                            if (_currentItemUuid === uuid) {
                                                // TODO Umbau auf itemStock-Widget
                                                _itemDetailsContent.find('div.inventory>div')
                                                    .text(_stock[item.uuid].global + _stock[item.uuid].local)
                                                    .attr('title',
                                                        _translations.localAvailable + ' ' + _stock[item.uuid].local + " | " +
                                                        _translations.globalAvailable + ' ' + _stock[item.uuid].global
                                                    );
                                            }

                                            // TODO Umbau auf itemStock-Widget
                                            _itemListContainer.find('#relic-item-' + uuid + ' div.inventory')
                                                .text(_stock[item.uuid].global + _stock[item.uuid].local)
                                                .attr('title',
                                                    _translations.localAvailable + ' ' + _stock[item.uuid].local + " | " +
                                                    _translations.globalAvailable + ' ' + _stock[item.uuid].global
                                                );
                                            _showHint = data.showHint;

                                            if (_showHint == true) {
                                                hint = Premium.Hint(_baseLink);
                                                hint.setTranslations(_translations).init(item.name).show();
                                            }
                                        }
                                    }
                                } else {
                                    jQuery('.currentBalance').text(data.balance.totalCurrency)
                                                             .parent().attr('title',
                                                                $P.sprintf(_translations.currencyBought, data.balance.boughtCurrency) + " | " +
                                                                $P.sprintf(_translations.currencyFree, data.balance.freeCurrency) + " (" + _translations.localAvailable 
                                                                + data.balance.freeCurrencyLocal + ")"
                                                             );
                                    alert(data.error);
                                }

                                _fade.setFadeObserver(function(fadeIn){
                                    if (!fadeIn) {
                                        _buyInProgress = false;
                                        _fade.setFadeObserver(null);

                                        if (item !== null) {
                                            that.setBuyButtonStatus();
                                            that.setActivateButtonStatus();
                                        }
                                    }
                                });

                                if (_fade.isFadeComplete()) {
                                    _fade.animate(button, false);
                                } else {
                                    _fade.fadeOutOnComplete();
                                }
                            }, 'json');
                        })
                );
                    
                _itemDetailsContent.append(
                    jQuery('<a href="#" class="activateItem awesome-button"/>')
                        .text(_translations.activate)
                        .bind('click', function(e){
                            if (_buyInProgress || jQuery(this).hasClass('disabled')) {
                                e.preventDefault();
                                return;
                            }

                            var uri = jQuery(this).attr('href');
                            var key, value;

                            for (key in _params) {
                                value = _params[key];

                                uri += (uri.indexOf('?') > -1 ? '&' : '?')
                                    +  'param.' + encodeURIComponent(key)
                                    +  '=' +  encodeURIComponent(value);
                            }

                            jQuery(this).attr('href', uri);
                        })
                );

                div = jQuery('<div id="itemDetails"/>');
                div.append(jQuery('<div class="top"/>'));
                div.append(jQuery('<div class="middle"/>').append(_itemDetailsContent));
                div.append(jQuery('<div class="bottom"/>'));
                container.append(div);

                // Create the item list container
                _itemListContainer = jQuery('<ul class="itemList"/>');
                container.append(_itemListContainer);

                // Create the extended description container
                _extendedDescriptionContainer = jQuery('<div id="extendedDescriptionContainer"/>').hide();
                _extendedDescriptionContainer.append(jQuery('<div/>')).append(
                    jQuery('<a href="#"/>')
                        .text(_translations.extendedDescriptionClose)
                        .bind('click', function(e){
                            e.preventDefault();

                            _extendedDescriptionContainer.hide();
                            _itemListContainer.show();
                        })
                );
                container.append(_extendedDescriptionContainer);

                // Show the first category
                if (_categories !== null) {
                    that.showCategory(firstCategory);
                } else {
                    that.showCategory(null);
                }

                // Register itself with the premium subject
                PremiumSubject.registerObserver(that);
            },

            setItemStock: function(uuid, localStock, globalStock, isInitialized)
            {
                if (_stock[uuid] == undefined) {
                    _stock[uuid] = {};
                }
                
                _stock[uuid].local = Number(localStock);
                _stock[uuid].global = Number(globalStock);

                

                if (isInitialized) {
                    _itemListContainer.find('#relic-item-' + uuid + ' div.inventory')
                    .text(_stock[uuid].global + _stock[uuid].local)
                    .attr('title',
                        _translations.localAvailable  + ' ' + _stock[uuid].local + " | " +
                        _translations.globalAvailable + ' ' + _stock[uuid].global
                    );

                    if ($('#relic-item-' + uuid) != null) {
                        $('#relic-item-' + uuid).trigger('click');
                    }

                    if (_itemListContainer.find('#relic-item-' + uuid).hasClass('active')) {
                        _itemDetailsContent.find('div.inventory>div')
                        .text(_stock[uuid].global + _stock[uuid].local)
                        .attr('title',
                            _translations.localAvailable  + ' ' + _stock[uuid].local + " | " +
                            _translations.globalAvailable + ' ' + _stock[uuid].global
                        );
                    }
                }
            },

            /**
             * Show a category
             *
             * @param   {String} category
             * @returns {void}
             */
            showCategory: function(category)
            {
                var uuid, items, item, li, img, firstItem = null;

                if (_initActiveUuid !== null) {
                    firstItem       = that.getItemFromUuid(_initActiveUuid);
                }

                _extendedDescriptionContainer.hide();
                _itemListContainer.show();
                _itemListContainer.find('li').remove();

                if (category !== null) {
                    items              = _categories[category];
                    activeCategoryName = jQuery('.activeCategoryName');
                    activeCategoryName.text(_categoryNames[category].name);
                } else {
                    items = _items;
                }
                
                for (uuid in items) {
                    item = items[uuid];
                    
                    if (!_readOnly && !item.buyable) {
                        continue;
                    }
                    
                    if (_stock[item.uuid] == undefined) {
                        _stock[item.uuid] = {
                            global : 0,
                            local  : 0
                        };
                    }

                    li   = jQuery('<li/>');

                    if (firstItem === null || _initActiveUuid === uuid) {
                        firstItem = item;
                        li.addClass('active');
                    }

                    li.addClass('rarity-' + item.rarity)
                      .attr('id', 'relic-item-' + item.uuid)
                      .bind('click', {item: item}, function(e){
                        jQuery(this).parent()
                                    .find('li.active')
                                    .removeClass('active');
                        jQuery(this).addClass('active');

                        that.showItem(e.data.item);
                    });

                    img = jQuery('<img width="72" height="52"/>');
                    img.attr('src', '/item-images/' + item.imageFilename + '-small.png')
                       .attr('alt', item.name);
                    li.append(img);

                    if (item.packAmount === false || _packOverride) {
                        li.append(jQuery('<div class="title"/>').text(item.name));
                    } else {
                        li.append(jQuery('<div class="title"/>').html(item.name + '<br />' + $P.sprintf(_translations.pack, item.packAmount)));
                    }

                    if (item.buyable !== false) {
                        if (item.specialPrice !== null) {
                            li.append(
                                jQuery('<div class="price"/>').html('<span class="specialPrice">' + item.specialPrice + '</span> <span><span>' + item.price + "</span></span>")
                                                              .attr('title', _translations.price)
                            );
                            
                        } else {
                            li.append(jQuery('<div class="price"/>').text(item.price).attr('title', _translations.price));
                        }
                    }

                    if (item.isSpecialOffer === true) {
                        li.append(
                            jQuery('<div class="awesome-button specialOffer">' + _translations.specialOffer + '</d>')
                        );
                    }
                    
                    if (!item.noInventoryActivation || _packOverride) {
                        li.append(jQuery('<div class="inventory"/>')
                            .text(_stock[item.uuid].global + _stock[item.uuid].local)
                            .attr('title', 
                                _translations.localAvailable + ' ' + (_stock[item.uuid] == undefined ? 0 : _stock[item.uuid].local) + " | " +
                                _translations.globalAvailable + ' ' + _stock[item.uuid].global
                            )
                        );
                    }

                    _itemListContainer.append(li);
                }

                _initActiveUuid = null;

                that.showItem(firstItem);
            },

            /**
             * Show an item
             *
             * @param   {Object} item
             * @returns {void}
             */
            showItem: function(item)
            {
                // Set current item uuid
                _currentItemUuid = item.uuid;
                
                // Title
                if (item.packAmount === false || _packOverride) {
                    _itemDetailsContent.find('div.title').text(item.name);
                    _itemDetailsContent.find('div.inventory').show();
                } else {
                    _itemDetailsContent.find('div.title').html(item.name + '<br />' + $P.sprintf(_translations.pack, item.packAmount));
                    _itemDetailsContent.find('div.inventory').hide();
                }

                // Data boxes
                if (item.buyable !== false) {
                    if (item.specialPrice !== null) {
                        _itemDetailsContent.find('div.price div').html('<span class="specialPrice">' + item.specialPrice + '</span> <span><span>' + item.price + "</span></span>").show();
                    } else {
                        _itemDetailsContent.find('div.price div').text(item.price).show();
                    }
                } else {
                    _itemDetailsContent.find('div.price div').hide();
                }
                
                _itemDetailsContent.find('div.inventory div')
                    .text(_stock[item.uuid].global + _stock[item.uuid].local)
                    .attr('title',
                        _translations.localAvailable + ' ' + _stock[item.uuid].local + " | " +
                        _translations.globalAvailable + ' ' + _stock[item.uuid].global
                    );

                if (item.duration !== 0) {
                    _itemDetailsContent.find('div.duration div').text(that.secondsToLocalizedString(item.duration));
                    _itemDetailsContent.find('div.duration').show();
                } else {
                    _itemDetailsContent.find('div.duration').hide();
                }

                if (item.cooldown !== 0) {
                    _itemDetailsContent.find('div.cooldown div').text(that.secondsToLocalizedString(item.cooldown));
                    _itemDetailsContent.find('div.cooldown').show();
                } else {
                    _itemDetailsContent.find('div.cooldown').hide();
                }

                // Description
                _itemDetailsContent.find('div.description').html(that.toHtml(item.description));

                // Image
                _itemDetailsContent.find('img')
                    .attr('src', '/item-images/' + item.imageFilename + '-large.png')
                    .attr('alt', item.name);

                // Buttons
                if (item.buyable) {
                    _itemDetailsContent.find('a.buyItem').attr('href', _baseLink + '&a=buyRelic&aj=1&uuid=' + item.uuid);
                }

                if (!item.noInventoryActivation || _packOverride) {
                    _itemDetailsContent.find('a.activateItem').attr('href', _baseLink + '&a=activateRelic&aj=1&uuid=' + item.uuid);
                    _itemDetailsContent.find('a.activateItem').css('visibility', 'visible');
                } else {
                    _itemDetailsContent.find('a.activateItem').css('visibility', 'hidden');
                }

                that.setBuyButtonStatus();
                that.setActivateButtonStatus();

                // Call item show observer if set
                if (_itemShowObserver !== null) {
                    _itemShowObserver();
                }
            
                // IE content pane fix
                jQuery('#tower_left').css('height', jQuery('#main_content').outerHeight());
                jQuery('#tower_right').css('height', jQuery('#main_content').outerHeight());

                switch (_shopType) {
                    case 'sideShop':
                        if (_shopContainer.find('.tabList').height() !== null) {
                            _itemDetailsContent.css('float', 'left');
                            _itemDetailsContent.css('min-height', _shopContainer.find('.tabList').height() - 80); // minus header and footer
                            _shopContainer.find('.itemList').css('max-height', _shopContainer.find('#itemDetails').height()); // minus header and footer
                        }
                        break;

                    case 'standard': 
                    default:
                        break;
                }
            },

            /**
             * Get an item from a uuid
             *
             * @param   {String} uuid
             * @returns {Object}
             */
            getItemFromUuid: function(uuid)
            {
                if (_items.hasOwnProperty(uuid)) {
                    return _items[uuid];
                }

                return null;
            },

            /**
             * Set buy button status
             *
             * @returns {void}
             */
            setBuyButtonStatus: function()
            {
                var item      = that.getItemFromUuid(_currentItemUuid);
                var buyButton = _itemDetailsContent.find('a.buyItem');

                if (item === null) {
                    return;
                }

                if (_readOnly === true) {
                    return;
                }

                if ((item.specialPrice === null && item.price > _currentBalance.totalCurrency) || (item.specialPrice !== null && item.specialPrice > _currentBalance.totalCurrency)) {
                    buyButton.get(0).removeAttribute('style');

                    buyButton.addClass('disabled');
                    buyButton.attr('title', _translations.insufficientBalance);
                } else if (!item.buyable) {
                    buyButton.get(0).removeAttribute('style');

                    buyButton.addClass('disabled');
                    buyButton.attr('title', _translations.notBuyable);
                } else {
                    buyButton.removeClass('disabled');
                    buyButton.attr('title', '');
                }
            },

            /**
             * Set activate button status
             *
             * @returns {void}
             */
            setActivateButtonStatus: function()
            {
                var item           = that.getItemFromUuid(_currentItemUuid);
                var activateButton = _itemDetailsContent.find('a.activateItem');

                if (item === null) {
                    return;
                }

                var hasRequiredParameters = true;

                for (var index = 0; index < item.requiredParameters.length; index++) {
                    if (!_params.hasOwnProperty(item.requiredParameters[index])) {
                        hasRequiredParameters = false;
                        break;
                    }
                }

                if (hasRequiredParameters || item.preparePage !== null) {
                    var inactiveStatus = null;

                    if (_buyInProgress) {
                        inactiveStatus = _translations.buyInProgress;
                    } else if (_stock[item.uuid].global + _stock[item.uuid].local === 0) {
                        inactiveStatus = _translations.insufficientStock;
                    } else if (item.isActive && item.cooldown !== 0) {
                        inactiveStatus = _translations.itemIsActive;
                    } else if (item.isCoolingDown) {
                        inactiveStatus = _translations.itemIsCoolingDown;
                    }

                    var uuid;

                    if (inactiveStatus === null && item.baseUuid !== item.uuid && item.cooldown !== 0) {
//                        for (uuid in _items) {
//                            if (_items[uuid].baseUuid !== item.baseUuid) {
//                                continue;
//                            }

//                            if (_items[uuid].isActive) {
                            if (item.isActive) {
                                inactiveStatus = _translations.itemIsActive;
//                                break;
//                            } else if (_items[uuid].isCoolingDown) {
                            } else if (item.isCoolingDown) {
                                inactiveStatus = _translations.itemCoolingDown;
//                                break;
                            }
//                        }
                    }

                    if (inactiveStatus !== null) {
                        activateButton.addClass('disabled');
                        activateButton.attr('title', inactiveStatus);
                    } else {
                        activateButton.removeClass('disabled');
                        activateButton.attr('title', '');
                    }

                    activateButton.show();
                } else {
                    activateButton.hide();
                }
            },

            /**
             * Set activation parameters
             *
             * @param   {Object} params
             * @returns {void}
             */
            setParams: function(params)
            {
                _params = params;
            },

            /**
             * Convert seconds to a localized string
             *
             * @param   {Integer} seconds
             * @returns {String}
             */
            secondsToLocalizedString: function(seconds)
            {
                var units = {
                    'day':    86400,
                    'hour':   3600,
                    'minute': 60
                };

                var valueUnit  = 'second';
                var value      = seconds;
                var unit, unitSeconds;

                for (unit in units) {
                    unitSeconds = units[unit];

                    if (unitSeconds <= seconds && (seconds % unitSeconds) === 0) {
                        valueUnit = unit;
                        value     = Math.floor(seconds / unitSeconds);
                        break;
                    }
                }

                return value.toString() + _translations[valueUnit + 'sSuffix'];
            },

            /**
             * Convert a stringt to HTML
             *
             * Will escape all special characters and convert line-breaks
             *
             * @param   {String} str
             * @returns {String}
             */
            toHtml: function(str)
            {
                return str.replace(/&/g, '&amp;')
                          .replace(/</g, '&lt;')
                          .replace(/>/g, '&gt;')
                          .replace(/"/g, '&quot;')
                          .replace(/\n/g, '<br />');
            },

            /**
             * Set an item show observer
             *
             * @returns {void}
             */
            setItemShowObserver: function(observer)
            {
                _itemShowObserver = observer;
            },

            /**
             * Get the details height
             *
             * @returns {Integer}
             */
            getDetailsHeight: function()
            {
                if (playerId % 2 == 0) {
                    return _itemDetailsContent.parent().parent().height();
                } else {
                    return _itemDetailsContent.parent().parent().height() + 54; // header height
                }
            },

            /**
             * Handle an elapsed active item
             *
             * @param   {String} changedUuid
             * @returns {void}
             */
            activeElapsed: function(changedUuid)
            {
                var uuid;

                for (uuid in _items) {
                    if (uuid === changedUuid) {
                        _items[uuid].isActive = false;

                        if (_items[uuid].cooldown !== 0) {
                            _items[uuid].isCoolingDown = true;
                        }
                    }
                }

                that.setActivateButtonStatus();
            },

            /**
             * Handle an elapsed cooldown
             *
             * @param   {String} changedUuid
             * @returns {void}
             */
            cooldownElapsed: function(changedUuid)
            {
                var uuid;

                for (uuid in _items) {
                    if (uuid === changedUuid) {
                        _items[uuid].isCoolingDown = false;
                    }
                }

                that.setActivateButtonStatus();
            }
        });
    }
});

/**
 * Gradient fader
 */
Zet.declare('Gradient.Fade' , {
    defineBody: function(that){
        /**
         * Start colors
         *
         * @property {Object}
         */
        var _startColor;

        /**
         * End colors
         *
         * @property {Object}
         */
        var _endColor;

        /**
         * Duration of the animation
         *
         * @property {Integer}
         */
        var _duration = 500;

        /**
         * FPS
         *
         * @property {Integer}
         */
        var _fps = 25;

        /**
         * Calculated time per frame
         *
         * @property {Integer}
         */
        var _timePerFrame = (1000 / _fps);

        /**
         * Calculated intervals
         *
         * @property {Integer}
         */
        var _intervals = (_duration / _timePerFrame);

        /**
         * Wether the fade has completed
         *
         * @property {Boolean}
         */
        var _fadeComplete;

        /**
         * Wether the fade out on complete
         *
         * @property {Boolean}
         */
        var _fadeOutOnComplete;

        /**
         * Fade observer
         *
         * @property {Function}
         */
        var _fadeObserver = null;

        Zet.public({
            /**
             * Define the start values
             *
             * @returns {void}
             */
            construct: function()
            {
                _startColor = {'top': Color(0x4f0000), 'bottom': Color(0x220502)};
                _endColor   = {'top': Color(0xd9c64b), 'bottom': Color(0xbfaa26)};
            },
            
            setDuration: function(duration)
            {
                _duration = duration;
                _intervals = (_duration / _timePerFrame);
            },

            /**
             * Animate a jQuery object
             *
             * @param   {jQuery}  jQueryObject
             * @param   {boolean} fadeIn
             * @returns {void}
             */
            animate: function(jQueryObject, fadeIn)
            {
                _fadeComplete      = false;
                _fadeOutOnComplete = false;

                if (fadeIn) {
                    var startTopColor    = _startColor.top;
                    var startBottomColor = _startColor.bottom;
                    var endTopColor      = _endColor.top;
                    var endBottomColor   = _endColor.bottom;
                } else {
                    var startTopColor    = _endColor.top;
                    var startBottomColor = _endColor.bottom;
                    var endTopColor      = _startColor.top;
                    var endBottomColor   = _startColor.bottom;
                }

                // Init values
                var topColor         = Color(0);
                var bottomColor      = Color(0);
                var middleColor      = Color(0);
                var currentInterval  = 0;

                // Current colors as floats
                var topColorCurrent = {
                    'red':   startTopColor.getRed(),
                    'green': startTopColor.getGreen(),
                    'blue':  startTopColor.getBlue()
                };

                var bottomColorCurrent = {
                    'red':   startBottomColor.getRed(),
                    'green': startBottomColor.getGreen(),
                    'blue':  startBottomColor.getBlue()
                };

                // Color steps
                var topColorStep = {
                    'red':   (endTopColor.getRed() - startTopColor.getRed()) / _intervals,
                    'green': (endTopColor.getGreen() - startTopColor.getGreen()) / _intervals,
                    'blue':  (endTopColor.getBlue() - startTopColor.getBlue()) / _intervals
                };

                var bottomColorStep = {
                    'red':   (endBottomColor.getRed() - startBottomColor.getRed()) / _intervals,
                    'green': (endBottomColor.getGreen() - startBottomColor.getGreen()) / _intervals,
                    'blue':  (endBottomColor.getBlue() - startBottomColor.getBlue()) / _intervals
                };

                // Animation interval
                var animationInterval = window.setInterval(function(){
                    // Set color values
                    topColor.setRed(Math.round(
                        startTopColor.getRed() > endTopColor.getRed()
                        ? Math.max(topColorCurrent.red, endTopColor.getRed())
                        : Math.min(topColorCurrent.red, endTopColor.getRed())
                    ));
                    topColor.setGreen(Math.round(
                        startTopColor.getGreen() > endTopColor.getGreen()
                        ? Math.max(topColorCurrent.green, endTopColor.getGreen())
                        : Math.min(topColorCurrent.green, endTopColor.getGreen())
                    ));
                    topColor.setBlue(Math.round(
                        startTopColor.getBlue() > endTopColor.getBlue()
                        ? Math.max(topColorCurrent.blue, endTopColor.getBlue())
                        : Math.min(topColorCurrent.blue, endTopColor.getBlue())
                    ));
                    bottomColor.setRed(Math.round(
                        startBottomColor.getRed() > endBottomColor.getRed()
                        ? Math.max(bottomColorCurrent.red, endBottomColor.getRed())
                        : Math.min(bottomColorCurrent.red, endBottomColor.getRed())
                    ));
                    bottomColor.setGreen(Math.round(
                        startBottomColor.getGreen() > endBottomColor.getGreen()
                        ? Math.max(bottomColorCurrent.green, endBottomColor.getGreen())
                        : Math.min(bottomColorCurrent.green, endBottomColor.getGreen())
                    ));
                    bottomColor.setBlue(Math.round(
                        startBottomColor.getBlue() > endBottomColor.getBlue()
                        ? Math.max(bottomColorCurrent.blue, endBottomColor.getBlue())
                        : Math.min(bottomColorCurrent.blue, endBottomColor.getBlue())
                    ));
                    middleColor.setRed(Math.round((topColor.getRed() + bottomColor.getRed()) / 2));
                    middleColor.setGreen(Math.round((topColor.getGreen() + bottomColor.getGreen()) / 2));
                    middleColor.setBlue(Math.round((topColor.getBlue() + bottomColor.getBlue()) / 2));

                    if (jQuery.browser.opera) {
                        jQueryObject.css('background-color', middleColor.toHex());
                    } else if (!document.all) {
                        jQueryObject.css('background', '-moz-linear-gradient(top, ' + topColor.toHex() + ', ' + bottomColor.toHex() + ')');
                        jQueryObject.css('background', '-webkit-gradient(linear, left top, left bottom, from(' + topColor.toHex() + '), to(' + bottomColor.toHex() + '))');
                    } else {
                        jQueryObject.css('filter', "progid:DXImageTransform.Microsoft.Gradient(StartColorStr='" + topColor.toHex() + "', EndColorStr='" + bottomColor.toHex() + "', GradientType=0)");
                    }

                    if (currentInterval++ >= _intervals) {
                        window.clearInterval(animationInterval);
                        _fadeComplete = true;

                        if (_fadeOutOnComplete) {
                            that.animate(jQueryObject, false);
                        }

                        if (_fadeObserver) {
                            _fadeObserver(fadeIn);
                        }
                    } else {
                        // Raise the color values
                        topColorCurrent.red      += topColorStep.red;
                        topColorCurrent.green    += topColorStep.green;
                        topColorCurrent.blue     += topColorStep.blue;
                        bottomColorCurrent.red   += bottomColorStep.red;
                        bottomColorCurrent.green += bottomColorStep.green;
                        bottomColorCurrent.blue  += bottomColorStep.blue;
                    }
                }, _timePerFrame);
            },

            /**
             * Check if the fade has completed
             *
             * @returns {Boolean}
             */
            isFadeComplete: function()
            {
                return _fadeComplete;
            },

            /**
             * Set fade-out on completion
             *
             * @returns {void}
             */
            fadeOutOnComplete: function()
            {
                _fadeOutOnComplete = true;
            },

            /**
             * Set fade observer called on completion
             *
             * @param   {Function} observer
             * @returns {void}
             */
            setFadeObserver: function(observer)
            {
                _fadeObserver = observer;
            }
        });
    }
});

/**
 * Color parser
 */
Zet.declare('Color' , {
    defineBody: function(that){
        /**
         * Red
         *
         * @property {Integer}
         */
        var _red;

        /**
         * Green
         *
         * @property {Integer}
         */
        var _green;

        /**
         * Blue
         *
         * @property {Integer}
         */
        var _blue;

        Zet.public({
            /**
             * Parse a color
             *
             * @param   {Integer} rgb
             * @returns {void}
             */
            construct: function(rgb)
            {
                _red   = (rgb >> 16);
                _green = (rgb >> 8) & 0xff;
                _blue  = rgb & 0xff;
            },

            /**
             * Get the red value
             *
             * @returns {Integer}
             */
            getRed: function()
            {
                return _red;
            },

            /**
             * Get the green value
             *
             * @returns {Integer}
             */
            getGreen: function()
            {
                return _green;
            },

            /**
             * Get the blue value
             *
             * @returns {Integer}
             */
            getBlue: function()
            {
                return _blue;
            },

            /**
             * Set the red value
             *
             * @param   {Integer} red
             * @returns {void}
             */
            setRed: function(red)
            {
                _red = red;
            },

            /**
             * Set the green value
             *
             * @param   {Integer} green
             * @returns {void}
             */
            setGreen: function(green)
            {
                _green = green;
            },

            /**
             * Set the blue value
             *
             * @param   {Integer} blue
             * @returns {void}
             */
            setBlue: function(blue)
            {
                _blue = blue;
            },

            /**
             * Convert the color to HTML RGB value
             *
             * @returns {String}
             */
            toHex: function()
            {
                var rgb = (_red << 16)
                        + (_green << 8)
                        + (_blue);

                var str = '000000' + rgb.toString(16);

                return '#' + str.substring(str.length - 6, str.length);
            }
        });
    }
});

/**
 * Premium subject
 *
 * Implements the observer pattern
 */
var PremiumSubject = {
    /**
     * Registered observers
     *
     * @property {Array}
     */
    observers: [],

    /**
     * Register an object as observer
     *
     * @param   {Object} observer
     * @returns {void}
     */
    registerObserver: function(observer)
    {
        PremiumSubject.observers.push(observer);
    },

    /**
     * Notify about an elapsed active item
     *
     * @param   {String} uuid
     * @returns {void}
     */
    activeElapsed: function(uuid)
    {
        for (var i = 0; i < PremiumSubject.observers; i++) {
            PremiumSubject.observers[i].activeElapsed(uuid);
        }
    },

    /**
     * Notify about an elapsed cooldown
     *
     * @param   {String} uuid
     * @returns {void}
     */
    cooldownElapsed: function(uuid)
    {
        for (var i = 0; i < PremiumSubject.observers; i++) {
            PremiumSubject.observers[i].cooldownElapsed(uuid);
        }
    }
}

// Load stuff after initialize startup scripts like excanvas
jQuery(window).bind('load', function(){
    jQuery('.buff').buff({
        width  : 38,
        height : 38,
        lang   : lang
    }).buff('startInterval');
});