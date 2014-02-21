<?php

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
class ColorPicker_AdminController extends Pimcore_Controller_Action_Admin
{

    /**
     * Initialize the AdminController. 
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Called when the settings action is requested by the
     * user.
     * (url: /plugin/ColorPicker/admin/settings) 
     */
    public function settingsAction()
    {
        // initialize translation object
        $this->initTranslation();
        
        // fetch config and put in view
        $this->view->config = ColorPicker_Plugin::loadConfig();
    }

    /**
     * Called when the user saves the settings.
     * (url: /plugin/ColorPicker/admin/save-settings) 
     */
    public function saveSettingsAction()
    {
        // disable rendering
        $this->disableViewAutoRender();
        
        // get and parse settings
        $settings = $this->_getParam('settings');
        $settings = Zend_Json::decode($settings, Zend_Json::TYPE_OBJECT);

        // write config
        $result = ColorPicker_Plugin::writeConfig($settings);
        if ($result) {
            $this->_helper->json(array('success' => true));
        } else {
            $this->_helper->json(array('success' => false));
        }
    }

    /**
     * Initialize the translation file and set it in the view. 
     */
    private function initTranslation()
    {
        // get locale
        $language = Zend_Registry::get("Zend_Locale");
        if (is_object($language)) {
            $language = $language->__toString();
        }
        
        // create translation object
        $this->view->translate = new Zend_Translate('csv', PIMCORE_PLUGINS_PATH . ColorPicker_Plugin::getTranslationFile($language), $language, array('delimiter' => ','));
    }

}

?>
