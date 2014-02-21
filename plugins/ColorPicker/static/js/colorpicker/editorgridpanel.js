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

pimcore.registerNS("pimcore.plugin.ColorPicker.EditorGridPanel");
pimcore.plugin.ColorPicker.EditorGridPanel = Ext.extend(Ext.grid.EditorGridPanel, {
    
    /**
     * The xtype of the objects
     */
    xtype: 'pimcore.plugin.ColorPicker.EditorGridPanel',
    
    /**
     * The selection model of the grid
     */
    selectionModel: new Ext.grid.RowSelectionModel({
        singleSelect: true
    }),
    
    /**
     * Initialize component
     */
    initComponent: function() {
        Ext.QuickTips.init();
        
        // create writer
        var writer = new Ext.data.JsonWriter({
            encode: true,
            encodeDelete: true,
            listful: true,
            writeAllFields: true
        });
        
        // create proxy
        var proxy = new Ext.data.HttpProxy({
            api: {
                read: { url: '/plugin/ColorPicker/palette/read', method: 'GET' },
                create: { url: '/plugin/ColorPicker/palette/create', method: 'POST' },
                update: { url: '/plugin/ColorPicker/palette/update', method: 'POST' },
                destroy: { url: '/plugin/ColorPicker/palette/destroy', method: 'POST' }
            }
        });
        
        // define hardcoded configuration
        var config = {
            store: new Ext.data.JsonStore({
                url: '/plugin/ColorPicker/palette/list',
                storeId: 'colorPickerPalettes',
                root: 'palettes',
                idProperty: 'id',
                totalProperty: 'count',
                successProperty: 'success',
                messageProperty: 'message',
                writer: writer,
                proxy: proxy,
                autoLoad: true,
                autoSave: true,
                fields: [
                    { name: 'name', allowBlank: false },
                    'colors'
                ],
                sortInfo: {
                    field: 'name',
                    direction: 'ASC'
                }
            }),
            columns: [{
                id: 'name',
                dataIndex: 'name',
                header: t('grid_palette_column_title'),
                width: 100,
                editor: new Ext.form.TextField({
                    allowBlank: false,
                    validator: this.validateName
                }),
                sortable: true,
                hideable: false,
                resizable: false
            },
            {
                id: 'colors',
                dataIndex: 'colors',
                header: t('grid_colors_column_title'),
                width: 200,
                editor: new Ext.form.TextField({
                    allowBlank: false,
                    validator: this.validateColors
                }),
                renderer: this.renderColor,
                resizable: false,
                hideable: false
            },
            {
                xtype: 'actioncolumn',
                id: 'action',
                width: 10,
                items: [
                    {
                        icon: '../plugins/ColorPicker/static/img/delete.png',
                        tooltip: t('button_row_delete'),
                        handler: function(grid, rowIndex) {
                            Ext.MessageBox.confirm(t('delete_confirm_title'), t('delete_confirm_text'), function(button) {
                                if(button == 'yes') {
                                    grid.deleteRow(rowIndex);
                                }
                            });
                        }
                    }
                ],
                hideable: false,
                resizable: false
            }],
            viewConfig: {
                forceFit: true
            },
            autoExpandColumn: 'name',
            selModel: this.selectionModel
        };
        
        // apply config
        Ext.apply(this, Ext.apply(this.initialConfig, config));
        
        // call superclass
        pimcore.plugin.ColorPicker.EditorGridPanel.superclass.initComponent.apply(this, arguments);
    },
    
    /**
     * Select a row in the grid.
     * 
     * @param rowIndex
     */
    selectRow: function(rowIndex) {
        this.selectionModel.selectRow(rowIndex);
    },
    
    /**
     * Delete a row in the grid. If no index is passed, delete
     * the selected row.
     * 
     * @param rowIndex
     */
    deleteRow: function(rowIndex) {
        if(rowIndex != null && rowIndex > -1) {
            this.store.removeAt(rowIndex);
        } else {
            this.store.remove(this.selectionModel.getSelected());
        }
    },
    
    /** @private */
    renderColor: function(val) {
        var result = '';
    
        if(val) {
            var elements = val.toString().split(',');
            for(var i = 0; i < elements.length; ++i) {
                elements[i] = elements[i].replace(/[\s#]/g, '');
                if(elements[i].length > 0 && elements[i].charAt(0) != '@') {
                    result += "#" + elements[i];
                } else {
                    result += elements[i].replace(' ', '');
                }
                if(i < elements.length - 1) {
                    result += ' ';
                }
            }
        }

        return result;
    },
    
    /** @private */
    validateName: function(value) {  
        var search = value.search(/[^a-z0-9_]/g);
        if(search > -1) {
            return t('validation_palette_name');
        } else {
            return true;
        }
    },
    
    /** @private */
    validateColors: function(value) {
        var search = value.search(/^((#?[a-f0-9]{6},?\s*)|(\@\{[\w]+\},?\s*))*$/i);
        if(search > -1 && value.charAt(value.length - 1) != ',') {
            return true;
        } else {
            return t('validation_palette_colors');
        }
    }
});