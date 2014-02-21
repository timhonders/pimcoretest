<?php

/**
 * This source file is subject to the new BSD license that is 
 * available through the world-wide-web at this URL:
 * http://www.pimcore.org/license
 *
 * @category   Pimcore
 * @package    Pimcore_API
 * @copyright  Copyright (c) 2012 Amgate B.V. (http://www.amgate.com)
 * @author     Leon Rodenburg <leon.rodenburg@amgate.com>
 * @license    http://www.pimcore.org/license
 */
class ColorPicker_Plugin extends Pimcore_API_Plugin_Abstract implements Pimcore_API_Plugin_Interface
{
    /**
     * The path to the plugin directory.
     * 
     * @var string 
     */

    const PLUGIN_DIRECTORY = '/var/plugins/ColorPicker/';

    /**
     * The palette table name in the database.
     * 
     * @var string 
     */
    const PALETTE_TABLE_NAME = 'plugin_colorpicker_palettes';

    /**
     * The path to the directory with language files.
     * 
     * @var string 
     */
    const LANG_PATH = '/ColorPicker/texts/';

    /**
     * The filename of the configuration file. 
     */
    const CONFIG_FILE = 'config.xml';

    /**
     * The name of the LESS file generated if the LESS file
     * generation is enabled.
     * 
     * @var string 
     */
    const LESS_FILE = 'colorpicker.less';

    /**
     * Return whether or not Pimcore needs a reload after install
     * of the ColorPicker plugin.
     * 
     * @return boolean 
     */
    public static function needsReloadAfterInstall()
    {
        return true;
    }

    /**
     * Install the ColorPicker plugin.
     * 
     * @return string 
     */
    public static function install()
    {
        Logger::debug('ColorPicker: ColorPicker installation running...');

        // create the database table
        if (self::createDatabaseTable() && self::createPluginDirectory()) {

            self::createInitialConfig();

            Logger::debug('ColorPicker: plugin install successful.');
            return 'ColorPicker plugin install successful.';
        } else {
            Logger::debug('ColorPicker: plugin install failed. Is the plugin directory (' . PIMCORE_WEBSITE_PATH . self::PLUGIN_DIRECTORY . ') writable?');
            return 'ColorPicker plugin install failed.';
        }
    }

    /**
     * Uninstall the plugin from this Pimcore CMS. 
     * 
     * @return string
     */
    public static function uninstall()
    {
        Logger::debug('ColorPicker: ColorPicker removal running...');

        // drop the database table
        if (self::dropDatabaseTable() && self::deletePluginDirectory()) {
            return 'ColorPicker plugin removal successful.';
        } else {
            return 'ColorPicker plugin removal failed.';
        }
    }

    /**
     * Return whether or not the ColorPicker plugin is
     * installed on this Pimcore CMS.
     * 
     * @return boolean 
     */
    public static function isInstalled()
    {
        // find the 'plugin_colorpicker_palettes' table in the table list
        $db = Pimcore_Resource_Mysql::get();
        $tables = $db->listTables();
        return in_array(self::PALETTE_TABLE_NAME, $tables) && file_exists(PIMCORE_WEBSITE_PATH . self::PLUGIN_DIRECTORY);
    }

    /**
     * Return the translation file for a given language.
     * 
     * @param string $language 
     * 
     * @return string
     */
    public static function getTranslationFile($language)
    {
        $languageFile = self::LANG_PATH . "$language.csv";
        if (file_exists(PIMCORE_PLUGINS_PATH . $languageFile)) {
            Logger::debug('ColorPicker: loading language file for language ' . $language);
            return $languageFile;
        }

        return self::LANG_PATH . "en.csv";
    }

    /**
     * Loads the configuration file and returns the settings in an
     * array.
     * 
     * @return array 
     */
    public static function loadConfig()
    {
        $result = array();
        $configPath = self::getConfigFile();
        if ($configPath && file_exists($configPath)) {
            $config = new Zend_Config_Xml($configPath);
            $result = $config->toArray();
        }

        return $result;
    }

    /**
     * Writes the passed in configuration to the config.xml file.
     * 
     * @param array $config 
     * 
     * @return boolean
     */
    public static function writeConfig($config)
    {
        $lessPath = $config->lessPath;
        $lessGeneration = $config->lessGeneration;

        if (empty($lessPath)) {
            $lessPath = '/';
        } else {
            if (preg_match('%^[a-zA-Z_0-9/]*[^/]$%', $lessPath)) {
                $lessPath .= '/';
            }
        }
        $lessPath = preg_replace('%/+%', '/', $lessPath);

        $newConfig = array();
        $newConfig['colorPicker'] = array();
        $newConfig['colorPicker']['lessPath'] = $lessPath;
        $newConfig['colorPicker']['lessGeneration'] = $lessGeneration;

        try {
            $configObject = new Zend_Config($newConfig);
            $writer = new Zend_Config_Writer_Xml(array(
                'config' => $configObject,
                'filename' => self::getConfigFile()
            ));
            $writer->write();
            return true;
        } catch (Exception $e) {
            var_dump($e->getMessage());
            return false;
        }
    }
    
    /**
     * Return the JavaScript class name of the plugin. 
     */
    public static function getJsClassName()
    {
        return 'pimcore.plugin.ColorPicker';
    }

    /**
     * Create the directory used by the plugin.
     * 
     * @return boolean 
     */
    private static function createPluginDirectory()
    {
        // if the directory exists, return true, otherwise try to create the directory
        if (file_exists(PIMCORE_WEBSITE_PATH . self::PLUGIN_DIRECTORY) && is_writeable(PIMCORE_WEBSITE_PATH . self::PLUGIN_DIRECTORY)) {
            return true;
        } else {
            if (mkdir(PIMCORE_WEBSITE_PATH . self::PLUGIN_DIRECTORY, 0755)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Delete the directory used by the plugin (all files are deleted
     * in the process).
     * 
     * @return boolean 
     */
    private static function deletePluginDirectory()
    {
        // loop over files in plugin directory and delete them
        $handle = opendir(PIMCORE_WEBSITE_PATH . self::PLUGIN_DIRECTORY);
        if ($handle) {
            while ($file = readdir($handle)) {
                if ($file != '.' && $file != '..') {
                    Logger::debug(PIMCORE_WEBSITE_PATH . self::PLUGIN_DIRECTORY . $file);
                    unlink(PIMCORE_WEBSITE_PATH . self::PLUGIN_DIRECTORY . $file);
                }
            }
        } else {
            Logger::debug('ColorPicker: no directory handle');
        }

        closedir($handle);
        
        // delete directory
        if (rmdir(PIMCORE_WEBSITE_PATH . self::PLUGIN_DIRECTORY)) {
            return true;
        } else {
            Logger::debug('ColorPicker: rmdir() failed');
        }

        return false;
    }

    /**
     * Return the path to the configuration file. Creates an
     * initial configuration if it doesn't exist.
     * 
     * @return string
     */
    private static function getConfigFile()
    {
        // find path of config file, create one if it doesn't exist
        $path = PIMCORE_WEBSITE_PATH . self::PLUGIN_DIRECTORY;
        $configFile = self::CONFIG_FILE;
        if (file_exists($path . $configFile)) {
            return $path . $configFile;
        } else {
            if (is_writable($path)) {
                touch($path . $configFile);
                chmod($path . $configFile, 0755);
                return $path . $configFile;
            }
        }

        return '';
    }
    
    /**
     * Create the initial configuration file at the correct path.
     * 
     * @return boolean 
     */
    private static function createInitialConfig()
    {
        // define initial properties
        $config = new stdClass();
        $config->lessPath = '/website/var/plugins/ColorPicker/';
        $config->lessGeneration = true;

        // write config to file
        self::writeConfig($config);
    }

    /**
     * Create the database table used to store
     * palettes.
     * 
     * @return boolean 
     */
    private static function createDatabaseTable()
    {
        // connect to database and create table
        $db = Pimcore_Resource_Mysql::get();

        $query = 'CREATE TABLE IF NOT EXISTS `' . self::PALETTE_TABLE_NAME . '` (`id` INT(11) NOT NULL AUTO_INCREMENT, `name` VARCHAR(255) NOT NULL UNIQUE, `colors` TEXT NULL, PRIMARY KEY(`id`))';

        Logger::debug('ColorPicker: query - ' . $query);
        $db->query($query);

        Logger::debug('ColorPicker: ' . self::PALETTE_TABLE_NAME . ' database table created');

        return true;
    }

    /**
     * Drop the database table used to store
     * palettes.
     * 
     * @return boolean
     */
    private static function dropDatabaseTable()
    {
        // connect to database and drop table
        $db = Pimcore_Resource_Mysql::get();

        $query = 'DROP TABLE IF EXISTS `' . self::PALETTE_TABLE_NAME . '`';

        Logger::Debug('ColorPicker: query - ' . $query);
        $db->query($query);

        Logger::debug('ColorPicker: ' . self::PALETTE_TABLE_NAME . ' database table dropped');

        return true;
    }

    /**
     * Called before a request is dispatched. Used to register the
     * Less Controller plugin.
     */
    public function preDispatch()
    {
        // register controller plugin for LESS
        $front = Zend_Controller_Front::getInstance();
        $front->registerPlugin(new ColorPicker_Controller_Plugin_Less());
    }

}