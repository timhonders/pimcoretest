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

pimcore.registerNS('pimcore.object.tags.color');
pimcore.object.tags.color = Class.create(pimcore.object.tags.abstract, {
    
    /**
     * The type of the object tag
     */
    type: 'color',
    
    /**
     * Initialize the tag.
     * 
     * @param data
     * @param fieldConfig
     */
    initialize: function(data, fieldConfig) {
        this.data = '';
        this.dirty = false;
        
        if(data) {
            this.data = data;
        }

        this.fieldConfig = fieldConfig;
    },
    
    /**
     * Get an editor for the grid layout.
     * 
     * @return Ext.ComboBox
     */
    getGridColumnEditor: function(field) {
        var colors = new Array();
        if(field.layout.palette && field.layout.palette.length > 0) {
            if(colorpicker.cache.palette[field.layout.palette]) {
                var cachedColors = colorpicker.cache.palette[field.layout.palette];
                for(var i = 0; i < cachedColors.length; ++i) {
                    var newColor = [ '#' + cachedColors[i], '#' + cachedColors[i] ];
                    colors[i] = newColor;
                }
            } else {
                var ajax = $.ajax({
                    url: '/plugin/ColorPicker/palette/view',
                    type: 'GET',
                    async: false,
                    dataType: 'json',
                    cache: true,
                    data: {
                        name: field.layout.palette
                    },
                    success: function(data) {
                        for(var i = 0; i < data.colors.length; ++i) {
                            var newColor = [ '#' + data.colors[i], '#' + data.colors[i] ];
                            colors[i] = newColor;
                        }

                        colorpicker.cache.palette[data.name] = data.colors;
                    }
                });
            }
        } else {
            var colorPalette = new Ext.ColorPalette();
            for(var i = 0; i < colorPalette.colors.length; ++i) {
                var newColor = [ '#' + colorPalette.colors[i], '#' + colorPalette.colors[i] ];
                colors[i] = newColor;
            }
        }
        
        var comboStore = new Ext.data.ArrayStore({
            data: colors,
            fields: [ 'id', 'color' ]
        });
        
        comboStore.insert(0, new Ext.data.Record({
            id: '',
            color: t('empty_color')
        }));
        
        var editorConfig = {
            store: comboStore,
            editable: false,
            triggerAction: 'all',
            displayField: 'color',
            valueField: 'id',
            mode: 'local',
            listEmptyText: t('color_list_empty')
        };
        return new Ext.form.ComboBox(editorConfig);
    },
    
    /**
     * Get the layout used when editing.
     * 
     * @return Ext.Panel
     */
    getLayoutEdit: function() {
        var paletteConfig = {
            fieldLabel: this.fieldConfig.title,
            name: this.fieldConfig.name,
            value: this.data.replace('#', '')
        };
        
        if(this.fieldConfig.noteditable) {
            paletteConfig.disabled = true;
        }
        
        if(this.fieldConfig.palette) {
            if(colorpicker.cache.palette[this.fieldConfig.palette]) {
                paletteConfig.colors = colorpicker.cache.palette[this.fieldConfig.palette];
            } else {
                $.ajax({
                    url: '/plugin/ColorPicker/palette/view',
                    type: 'GET',
                    async: false,
                    cache: true,
                    dataType: 'json',
                    data: {
                        name: this.fieldConfig.palette
                    },
                    success: function(data) {
                        paletteConfig.colors = data.colors;
                        colorpicker.cache.palette[data.name] = data.colors;
                    }
                });
            }
        }
        
        var panelConfig = {
            layout: 'form',
            border: false
        };
        
        this.panel = new Ext.Panel(panelConfig);
        this.component = new Ext.ColorPalette(paletteConfig);
        this.panel.add(this.component);
        
        // register listeners
        this.component.addListener('select', function(palette, color) {
            this.dirty = true;
        }, this);
        
        return this.panel;
    },
    
    /**
     * Get the layout used when just showing the object.
     * 
     * @return Ext.Panel
     */
    getLayoutShow: function() {
        this.panel = this.getLayoutEdit();
        return this.panel;
    },
    
    /**
     * Return the component's value.
     * 
     * @return string
     */
    getValue: function() {
        return '#' + this.component.value;
    },
    
    /**
     * Return the field's name.
     * 
     * @return string
     */
    getName: function() {
        return this.fieldConfig.name;
    },
    
    /**
     * Return whether or not the field is dirty (changed).
     * 
     * @return  boolean
     */
    isDirty: function() {
        return this.dirty;
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
