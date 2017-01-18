;(function($){

$.widget('gf.itemStock', {

    options: {
        uuid       : null,
        localStock : 0,
        globalStock: 0,
        localText  : 'local items: ',
        globalText : 'global items: '
    },

    _init: function()
    {
        if (this.options.uuid === null) {
            throw new Error('uuid not defined!');
        }
        this.options.localStock  = Number(this.options.localStock)  || 0;
        this.options.globalStock = Number(this.options.globalStock) || 0;
        
        this._reducedAmount      = 0;
    },

    _checkAmount: function(amount)
    {
        amount = Number(amount);
        return !isNaN(amount) ? amount : false;
    },

    addLocal: function(amount)
    {
        if((amount = this._checkAmount(amount)) === false) return;
        this.options.localStock  += amount;
        this.update();
    },

    addGlobal: function(amount)
    {
        if((amount = this._checkAmount(amount)) === false) return;
        this.options.globalStock += amount;
        this.update();
    },

    sub: function(amount, force_reduced_add)
    {
        if ((amount = this._checkAmount(amount)) === false) return;
        if (force_reduced_add == undefined) force_reduced_add = false;

        var reduceAmount = 0;

        if (this.options.localStock > 0) {
            reduceAmount = this.options.localStock - amount;

            // Im falle, dass die Reduzierung eines lokalen Lagers Ã¼berschritten
            // wird, wird das lokale Lager auf 0 gesetzt und der reducedAmount
            // wird auf die GrÃ¶ÃŸe des lokalen Lagers gesetzt.
            if (reduceAmount < 0) {
                this._reducedAmount     = this.options.localStock;
                this.options.localStock = 0;
                this.sub(Math.abs(reduceAmount), true);
            }

            // Andernfalls wird der localStock gesetzt und der reducedAmount auf
            // den Wert des amounts gesetzt.
            else {
                this.options.localStock = reduceAmount;
                this._reducedAmount     = amount;
            }
        }

        else if (this.options.globalStock > 0) {
            reduceAmount = this.options.globalStock - amount;

            // Im falle, dass die Reduzierung eines globalen Lagers Ã¼berschritten
            // wird, wird das globale Lager auf 0 gesetzt.
            if (reduceAmount < 0) {

                // Sollte bereits das lokale Lager geleert worden sein, so wird der
                // Stand des globalen Lagers dazu addiert.
                if (force_reduced_add)
                    this._reducedAmount += this.options.globalStock;

                // Andernfalls wird nur das globale Lager auf die reducedAmount gesetzt.
                else
                    this._reducedAmount  = this.options.globalStock;
                
                this.options.globalStock = 0;
            }

            // Im Falle, dass die Reduzierung einen positiven Wert mitbringt,
            // wird der Wert, wie er ist Ã¼bernommen.
            else {
                this.options.globalStock = reduceAmount;

                // Wenn das lokale Lager verbraucht wurde, wird der Wert aus
                // der Rekursion dazu addiert.
                if (force_reduced_add)
                    this._reducedAmount += amount;

                // Wenn das lokale Lager bereits leer war, wird der Wert direkt
                // Ã¼bernommen.
                else
                    this._reducedAmount  = amount;
            }
        }
        this.update();
    },

    getReducedAmount: function()
    {
        return this._reducedAmount;
    },

    amount: function()
    {
        return this.options.localStock + this.options.globalStock;
    },

    setLocal: function(amount)
    {
        if((amount = this._checkAmount(amount)) === false || amount < 0) return;
        this.options.localStock = amount;
        this.update();
    },

    getLocalAmount: function()
    {
        return this.options.localStock;
    },

    setGlobal: function(amount)
    {
        if((amount = this._checkAmount(amount)) === false || amount < 0) return;
        this.options.globalStock = amount;
        this.update();
    },

    getGlobalAmount: function()
    {
        return this.options.globalStock;
    },

    update: function()
    {
        this.element.attr('title', this.options.localText + ' ' + this.options.localStock
                        + ' | ' + this.options.globalText + ' ' + this.options.globalStock)
                    .text(this.options.localStock + this.options.globalStock);
    }

});

})(jQuery);