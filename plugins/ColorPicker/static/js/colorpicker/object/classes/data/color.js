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

pimcore.registerNS('pimcore.object.classes.data.color');
pimcore.object.classes.data.color = Class.create(pimcore.object.classes.data.data, {
    
    /**
     * Type of the data field
     */
    type: 'color',
    
    /**
     * Define where datatype is allowed
     */
    allowIn: {
        object: true,
        objectbrick: true,
        fieldcollection: true,
        localizedfield: true
    },
    
    /**
     * Initialize the data field.
     * 
     * @param treeNode
     * @param initData
     */
    initialize: function(treeNode, initData) {
        this.type = 'color';
        
        this.initData(initData);
        
        this.treeNode = treeNode;
    },
    
    /**
     * Get the name of the group this field type is in.
     * 
     * @return string
     */
    getGroup: function() {
        return 'select';
    },
    
    /**
     * Get the name of the field type.
     * 
     * @return string
     */
    getTypeName: function() {
        return t('color');
    },
    
    /**
     * Get the class of the icon.
     * 
     * @return string
     */
    getIconClass: function() {
        return 'colorpicker_field_icon';
    },
    
    /**
     * Return the layout.
     * 
     * @return Ext.Panel
     */
    getLayout: function($super) {
        $super();
        
        var paletteStore = new Ext.data.JsonStore({
            url: '/plugin/ColorPicker/palette/read/',
            fields: ['id', 'name'],
            root: 'palettes',
            listeners: {
                load: function(store, records, options) {
                    var count = store.getCount();
                    for(var i = 0; i < count; ++i) {
                        var record = store.getAt(i);
                        record.data.id = record.data.name;
                    }
                    
                    store.insert(0, new Ext.data.Record({
                        id: '',
                        name: t('empty_palette')
                    }));
                }
            }
        });
        
        
        
        var comboBox = new Ext.form.ComboBox({
            fieldLabel: t('palette'),
            id: 'palette',
            name: 'palette',
            value: this.datax.palette,
            store: paletteStore,
            listEmptyText: t('palette_list_empty'),
            editable: false,
            displayField: 'name',
            valueField: 'id',
            triggerAction: 'all'
        });
        
        this.specificPanel.removeAll();
        this.specificPanel.add(comboBox);
        
        return this.layout;
    }
});