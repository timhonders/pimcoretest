/**
 * Pimcore
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.pimcore.org/license
 *
 * @copyright  Copyright (c) 2009-2013 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     New BSD License
 */

pimcore.registerNS("pimcore.settings.profile.panel");
pimcore.settings.profile.panel = Class.create({

    initialize:function () {

        this.getTabPanel();
    },

    getTabPanel:function () {

        if (!this.panel) {
            this.panel = new Ext.Panel({
                id:"profile",
                title:t("profile"),
                border:false,
                closable:true,
                layout:"fit",
                bodyStyle:"padding: 10px;",
                items:[this.getEditPanel()]
            });

            var tabPanel = Ext.getCmp("pimcore_panel_tabs");
            tabPanel.add(this.panel);
            tabPanel.activate("profile");

            this.panel.on("destroy", function () {
                pimcore.globalmanager.remove("profile");
            }.bind(this));


            pimcore.layout.refresh();

        }

        return this.panel;
    },

    getEditPanel:function () {
        this.forceReloadOnSave = false;
        this.currentUser = pimcore.currentuser;

        var generalItems = [];
        generalItems.push({
            xtype:"textfield",
            fieldLabel:t("old_password"),
            name:"old_password",
            inputType:"password",
            width:300
        });

        generalItems.push({
            xtype:"textfield",
            fieldLabel:t("new_password"),
            name:"new_password",
            inputType:"password",
            width:300
        });
        generalItems.push({
            xtype:"textfield",
            fieldLabel:t("retype_password"),
            name:"retype_password",
            inputType:"password",
            width:300,
            style:"margin-bottom: 20px;"
        });

        generalItems.push({
            xtype:"textfield",
            fieldLabel:t("firstname"),
            name:"firstname",
            value:this.currentUser.firstname,
            width:300
        });
        generalItems.push({
            xtype:"textfield",
            fieldLabel:t("lastname"),
            name:"lastname",
            value:this.currentUser.lastname,
            width:300
        });
        generalItems.push({
            xtype:"textfield",
            fieldLabel:t("email"),
            name:"email",
            value:this.currentUser.email,
            width:300
        });


        generalItems.push({
            xtype:'combo',
            fieldLabel:t('language'),
            typeAhead:true,
            value:this.currentUser.language,
            mode:'local',
            listWidth:100,
            store:pimcore.globalmanager.get("pimcorelanguages"),
            displayField:'display',
            valueField:'language',
            forceSelection:true,
            triggerAction:'all',
            hiddenName:'language',
            listeners:{
                change:function () {
                    this.forceReloadOnSave = true;
                }.bind(this),
                select:function () {
                    this.forceReloadOnSave = true;
                }.bind(this)
            }
        });

        generalItems.push({
            xtype:"checkbox",
            fieldLabel:t("show_welcome_screen"),
            name:"welcomescreen",
            checked:this.currentUser.welcomescreen
        });

        generalItems.push({
            xtype:"checkbox",
            fieldLabel:t("memorize_tabs"),
            name:"memorizeTabs",
            checked:this.currentUser.memorizeTabs
        });

        this.userPanel = new Ext.form.FormPanel({
            border:false,
            layout:"pimcoreform",
            items:generalItems,
            labelWidth:130,
            buttons:[
                {
                    text:t("save"),
                    iconCls:"pimcore_icon_apply",
                    handler:this.saveCurrentUser.bind(this)
                }
            ],
            autoScroll:true
        });

        return this.userPanel;
    },

    saveCurrentUser:function () {
        var values = this.userPanel.getForm().getFieldValues();

        Ext.Ajax.request({
            url:"/admin/user/update-current-user",
            method:"post",
            params:{
                id:this.currentUser.id,
                data:Ext.encode(values)
            },
            success:function (response) {
                try {
                    var res = Ext.decode(response.responseText);
                    if (res.success) {

                        if (this.forceReloadOnSave) {
                            this.forceReloadOnSave = false;

                            Ext.MessageBox.confirm(t("info"), t("reload_pimcore_changes"), function (buttonValue) {
                                if (buttonValue == "yes") {
                                    window.location.reload();
                                }
                            }.bind(this));
                        }

                        pimcore.helpers.showNotification(t("success"), t("user_save_success"), "success");
                    } else {
                        pimcore.helpers.showNotification(t("error"), t("user_save_error"), "error", t(res.message));
                    }
                } catch (e) {
                    pimcore.helpers.showNotification(t("error"), t("user_save_error"), "error");
                }
            }.bind(this)
        });
    },


    activate:function () {
        Ext.getCmp("pimcore_panel_tabs").activate("users");
    }

});