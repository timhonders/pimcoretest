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

pimcore.registerNS('pimcore.plugin.ColorPicker');
pimcore.plugin.ColorPicker = Class.create(pimcore.plugin.admin, {
    
    /**
     * Return the class name of the plugin.
     * 
     * @return string
     */
    getClassName: function() {
        return 'pimcore.plugin.ColorPicker';
    },
    
    /**
     * Initialize the plugin.
     */
    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },
    
    /**
     * Called when Pimcore is ready. Adds a item to the
     * main menu.
     */
    pimcoreReady: function() {
        // get toolbar and add color palette item
        var toolbar = Ext.getCmp('pimcore_panel_toolbar');
        
        colorPicker.action = new Ext.Action({
            id:         'colorpicker_menu_item',
            text:       t('menuitem_title'),
            iconCls:    'colorpicker_menu_icon',
            handler:    this.showTab
        });
        
        toolbar.items.items[2].menu.insert(5, this.action);
        
        // register listener for data proxy
        Ext.data.DataProxy.addListener('exception', function(proxy, type, action, options, res) {
            if (type === 'remote') {
                Ext.Msg.show({
                    title: t('messagebox_error_title'),
                    msg: t(res.message),
                    icon: Ext.MessageBox.ERROR,
                    buttons: Ext.Msg.OK
                });
                
                if (action == "create") {
                    var row = colorPicker.gridPanel.store.getCount() - 1;
                    colorPicker.gridPanel.store.removeAt(row);
                } else {
                    colorPicker.gridPanel.store.rejectChanges();
                }
            }
        });
    },
    
    /**
     * Called when the plugin is uninstalled from Pimcore. Used to
     * remove the Color Palettes menu item.
     */
    uninstall: function() {
        // get toolbar and remove color palette item
        var toolbar = Ext.getCmp('pimcore_panel_toolbar');
        toolbar.items.items[2].menu.remove(colorPicker.action);
    },
    
    /**
     * Show the color palette tab when the main menu item
     * is clicked.
     */
    showTab: function() {
        // if the tab is not shown yet
        var tabPanel = Ext.getCmp('pimcore_panel_tabs');
        if(tabPanel.find('id', 'colorpicker_gridpanel').length == 0) {
            // create grid
            colorPicker.gridPanel = new pimcore.plugin.ColorPicker.EditorGridPanel({
                id:             'colorpicker_gridpanel',
                title:          t('menuitem_title'),
                iconCls:        'colorpicker_panel_icon',
                layout:         'fit',
                closable:       true,
                tbar: [{
                    text: t('button_add_title'),
                    iconCls: 'colorpicker_plus_icon',
                    handler: function() {
                        Ext.MessageBox.prompt(t('messagebox_add_title'), t('messagebox_add_text'), function(buttonId, paletteName) {
                            if(buttonId == "ok" && paletteName) {
                                var paletteNewName = paletteName.replace(/\W/g, '_').toLowerCase();
                                var Palette = colorPicker.gridPanel.store.recordType;
                                var p = new Palette({
                                    id: '',
                                    name: paletteNewName,
                                    colors: '000000,FFFFFF'
                                });
                                colorPicker.gridPanel.stopEditing();
                                colorPicker.gridPanel.store.add(p);
                            }
                        });
                    }
                }],
                listeners: {
                    rowcontextmenu: function(grid, rowIndex, event) {
                        event.stopEvent();
                        grid.selectRow(rowIndex);
                        var contextMenu = new Ext.menu.Menu({
                            autoDestroy: true,
                            autoShow: true
                        });
                        var deleteItem = new Ext.menu.Item({
                            text: t('button_delete_title'),
                            iconCls: 'colorpicker_delete_icon',
                            handler: function() {
                                grid.deleteRow();
                            }
                        });
                        contextMenu.addItem(deleteItem);
                        contextMenu.showAt(event.xy);
                    }
                }
            });  

            tabPanel.add(colorPicker.gridPanel);
        }
        
        tabPanel.activate('colorpicker_gridpanel');

        pimcore.layout.refresh();
    }
});

var colorPicker = new pimcore.plugin.ColorPicker();