pimcore.registerNS("pimcore.plugin.kegaamqp");

pimcore.plugin.kegaamqp = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return "pimcore.plugin.kegaamqp";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },
 
    pimcoreReady: function (params,broker){
        // alert("Example Ready!");
    }
});

var kegaamqpPlugin = new pimcore.plugin.kegaamqp();

