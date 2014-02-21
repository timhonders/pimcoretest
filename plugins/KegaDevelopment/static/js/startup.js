pimcore.registerNS("pimcore.plugin.kegadevelopment");

pimcore.plugin.kegadevelopment = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return "pimcore.plugin.kegadevelopment";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },
 
    pimcoreReady: function (params,broker){
        
        
        var user = pimcore.globalmanager.get("user");
        if(user.admin == true){
      
        	Ext.Ajax.request({
    		    url: '/plugin/KegaDevelopment/index/files',
    		    success: function(response, opts) {
					var obj = Ext.decode(response.responseText);
					if (obj.total > 0){
						Ext.MessageBox.confirm('Kega Development update', obj.total + ' sql updates detected, install updates', function (btn){
							if (btn == 'yes'){
							 	
								Ext.Ajax.request({
					    		    url: '/plugin/KegaDevelopment/index/install',
					    		    success: function(response, opts) {
				
					    		    },
					    		    failure: function(response, opts) {
					    			   console.log('server-side failure with status code ' + response.status);
					    		    }
					    		});
								
							}	
						});
					}
					console.dir(obj);
    		    },
    		    failure: function(response, opts) {
    			   console.log('server-side failure with status code ' + response.status);
    		    }
    		});
        }
    }
    
});

var kegadevelopmentPlugin = new pimcore.plugin.kegadevelopment();

