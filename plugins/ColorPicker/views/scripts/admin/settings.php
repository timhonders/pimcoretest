<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Pimcore ColorPicker Plugin :: Settings</title>
        <link href="/pimcore/static/js/lib/ext/resources/css/ext-all.css" media="screen" rel="stylesheet" type="text/css"/>
        <?php 
        $conf = Zend_Registry::get("pimcore_config_system");
        $themeUrl = "/pimcore/static/js/lib/ext/resources/css/xtheme-blue.css";
        if ($conf->general->theme) {
            $themeUrl = $conf->general->theme;
        }
        ?>
        <link href="<?php echo $themeUrl ?>" media="screen" rel="stylesheet" type="text/css"/>
        <link href="/plugins/ColorPicker/static/css/admin.css" media="screen" rel="stylesheet" type="text/css"/>
        <script src="/pimcore/static/js/lib/ext/adapter/ext/ext-base.js" type="text/javascript"></script>
        <script src="/pimcore/static/js/lib/ext/ext-all-debug.js" type="text/javascript"></script>
        <script type="text/javascript">
            Ext.onReady(function() {
                Ext.QuickTips.init();
                
                var panel = new Ext.form.FormPanel({
                    id: 'settingsForm',
                    border: true,
                    width: 600,
                    padding: 10,
                    iconCls: 'colorpicker_menu_icon',
                    title: '<?php echo $this->translate->_('settingsFormTitle') ?>',
                    items: [{
                        xtype: 'displayfield',
                        value: '<?php echo $this->translate->_('settingsFormDescription') ?>',
                        hideLabel: true,
                        style: {
                            marginBottom: '20px'
                        }
                    },
                    {
                        xtype: 'displayfield',
                        value: '<?php echo $this->translate->_('settingsFormLessTitle') ?>',
                        hideLabel: true,
                        style: {
                            fontWeight: 'bold'
                        }
                    },
                    {
                        xtype: 'displayfield',
                        value: '<?php echo $this->translate->_('settingsFormLessDescription') ?>',
                        hideLabel: true,
                        style: {
                            color: '#999',
                            fontStyle: 'italic',
                            marginBottom: '10px'
                        }
                    },
                    {
                        xtype: 'displayfield',
                        value: '<?php echo $this->translate->_('settingsFormLessPathDescription') ?>',
                        hideLabel: true,
                        style: {
                            color: '#999',
                            fontStyle: 'italic'
                        }
                    },
                    {
                        xtype: 'checkbox',
                        id: 'lessGeneration',
                        fieldLabel: '<?php echo $this->translate->_('settingsFormLessLabel') ?>',
                        value: 'lessGeneration',
                        checked: <?php echo $this->config['colorPicker']['lessGeneration'] ? 'true' : 'false' ?>
                    },
                    {
                        xtype: 'textfield',
                        id: 'lessPath',
                        fieldLabel: '<?php echo $this->translate->_('settingsFormLessPathLabel') ?>',
                        value: '<?php echo $this->config['colorPicker']['lessPath'] ?>',
                        width: 250,
                        listeners: {
                            specialkey: function(form, event) {
                                if(event.getKey() == 13) {
                                    submitForm();
                                }
                            }
                        }
                    }],
                    buttons: [{
                        text: '<?php echo $this->translate->_('settingsFormSubmit')?>',
                        iconCls: 'colorpicker_save_icon',
                        handler: function() {
                            submitForm();
                        }
                    }]
                });
                
                function submitForm() {
                    var fields = Ext.getCmp('settingsForm').getForm().getFieldValues();
                    Ext.Ajax.request({
                        url: '/plugin/ColorPicker/admin/save-settings',
                        params: {
                            settings: Ext.util.JSON.encode(fields)
                        },
                        method: 'POST',
                        success: function(xhr) {
                            window.location.reload();
                        }
                    });
                }
                
                panel.render('settingsFormContainer');
            });
        </script>
    </head>
    <body>
        <div id="page">
            <div id="settingsFormContainer"></div>
        </div>
    </body>
</html>