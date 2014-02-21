/**
 * This source file is subject to the new BSD license that is 
 * available through the world-wide-web at this URL:
 * http://www.pimcore.org/license
 *
 * @category   Pimcore
 * @package    ColorPicker
 * @copyright  Copyright (c) 2012 Amgate B.V. (http://www.amgate.com)
 * @author     Leon Rodenburg <leon.rodenburg@amgate.com>
 * @license    http://www.pimcore.org/license
 */

pimcore.registerNS('pimcore.document.tags.color');
pimcore.document.tags.color = Class.create(pimcore.document.tag, {  
    
    /**
     * Initialize the color tag.
     * 
     * @param id
     * @param name
     * @param options
     * @param data
     */
    initialize: function(id, name, options, data) {
        this.id = id;
        this.name = name;
        if(!data) {
            data = '';
        }
        this.data = data;
        this.setupWrapper();
        if (!options) {
            this.options = {};
        } else {
            this.options = options;
        }
        if(!this.options.suppressOutput) {
            this.options.suppressOutput = false;
        }

        // if a palette is specified
        if(this.options.palette && !this.options.colors) {
            var source = this;

            if(colorpicker.cache.palette[this.options.palette]) {
                this.options.colors = colorpicker.cache.palette[this.options.palette];
            } else {
                jQuery.ajax({
                    url: '/plugin/ColorPicker/palette/view',
                    type: 'GET',
                    dataType: 'json',
                    cache: true,
                    async: false,
                    data: {
                        name: this.options.palette
                    },
                    success: function(data, status, xhr) {
                        source.options.colors = data.colors;
                        colorpicker.cache.palette[data.name] = data.colors;
                    },
                    error: function(data) {
                        // no action
                    }
                });
            }
            
            source.render();
        } else {
            this.render();
        } 
    },
    
    /** @private */
    render: function() {
        // set value if colors were chosen and the value is not empty
        if(this.data && this.data.color && this.options.colors && this.inArray(this.data.color, this.options.colors)) {
            this.options.value = this.data.color;
        }
        
        this.options.name = this.id + '_editable';  
        
        this.element = new Ext.ColorPalette(this.options);
        
        // set value if colors were empty and the value is not empty
        if(this.data && this.data.color && !this.options.colors && this.inArray(this.data.color, this.element.colors)) {
            this.element.value = this.data.color;
        }
        
        if (this.options.reload) {
            this.element.on('select', this.reloadDocument);
        }
        
        var source = this;
        
        // bind to back- and foreground
        if (this.options.bindBackground) {
            this.element.on('select', function() {
                $(source.options.bindBackground).css({
                    backgroundColor: '#' + this.value
                });
            });
        }
        if(this.options.bindForeground) {
            this.element.on('select', function() {
                $(source.options.bindForeground).css({
                    color: '#' + this.value
                })
            })
        }

        this.element.render(this.id);
    },

    /**
     * Return the value of the tag.
     * 
     * @return object
     */
    getValue: function() {
        return { color: this.element.value, palette: this.options.palette, suppressOutput : this.options.suppressOutput };
    },

    /**
     * Return the type of the tag.
     * 
     * @return string
     */
    getType: function() {
        return 'color';
    },
    
    /** @private */
    inArray: function (object, array) {
        for (var i = 0; i < array.length; ++i) {
            if(array[i] == object) {
                return true;
            }
        }
        
        return false;
    }
});
