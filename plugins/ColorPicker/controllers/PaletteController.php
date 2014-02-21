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
class ColorPicker_PaletteController extends Pimcore_Controller_Action
{

    /**
     * Initialize the PaletteController. 
     */
    public function init()
    {
        parent::init();

        // disable view rendering
        $this->removeViewRenderer();
    }

    /**
     * Add a palette to the database.
     * (url: /plugin/ColorPicker/palette/create)
     */
    public function createAction()
    {
        // get array of palettes
        $palettes = $this->_getParam('palettes');
        $palettes = urldecode($palettes);
        $palettes = str_replace(' ', '', $palettes);
        
        // define results
        $success = false;
        $result = new stdClass();
        $resultPalettes = array();

        // loop over array of palettes
        if ($palettes) {
            $palettes = Zend_Json::decode($palettes, Zend_Json::TYPE_OBJECT);

            $success = true;

            // create new ColorPicker_Palette object
            try {
                foreach ($palettes as $palette) {
                    $newPalette = ColorPicker_Palette::getFromJson($palette);
                    $newPalette->insert();

                    $resultPalettes[] = $newPalette->getStdClass();
                }
            } catch (Exception $exception) {
                Logger::warn('ColorPicker: exception - ' . $exception->getMessage());
                $success = false;
            }
        }

        // set results
        $result->palettes = $resultPalettes;
        $result->count = count($resultPalettes);
        $result->success = $success;
        $result->message = $success ? 'palette_create_success' : 'palette_create_fail';

        echo Zend_Json::encode($result);
    }

    /**
     * Update a palette in the database.
     * (url: /plugin/ColorPicker/palette/update) 
     */
    public function updateAction()
    {
        // get array of palettes
        $palettes = $this->_getParam('palettes');
        $palettes = urldecode($palettes);
        $palettes = str_replace(' ', '', $palettes);

        // define results 
        $success = false;
        $result = new stdClass();
        $resultPalettes = array();

        if ($palettes) {
            $palettes = Zend_Json::decode($palettes, Zend_Json::TYPE_OBJECT);

            $success = true;

            // update palette
            try {
                foreach ($palettes as $palette) {
                    $newPalette = ColorPicker_Palette::getFromJson($palette);
                    $newPalette->update();

                    $resultPalettes[] = $newPalette->getStdClass();
                }
            } catch (Exception $exception) {
                Logger::warn('ColorPicker: exception - ' . $exception->getMessage());
                $success = false;
            }
        }

        // set results
        $result->palettes = $resultPalettes;
        $result->count = count($resultPalettes);
        $result->success = $success;
        $result->message = $success ? 'palette_update_success' : 'palette_update_fail';

        echo Zend_Json::encode($result);
    }

    /**
     * Retrieve a palette with a given name from the database. 
     * The name should be the unique string identifier of the palette. 
     * The palette is returned as a JSON encoded object.
     * (url: /plugin/ColorPicker/palette/view) 
     */
    public function viewAction()
    {
        $name = $this->_getParam('name');

        // find palette if a name was passed
        if ($name) {
            $palette = ColorPicker_Palette::get($name);
            
            if($palette) {
                $colors = $this->parsePalette($palette);
                $palette->setColors($colors);
                
                echo $palette->getJson();
            }
        } else {
            echo $this->_helper->json(new stdClass());
        }
    }

    /**
     * Retrieve a list of palettes from the database as a JSON encoded
     * array.
     * (url: /plugin/ColorPicker/palette/read) 
     */
    public function readAction()
    {
        $result = new stdClass();
        $jsonArray = array();
        
        // get all palettes and put in array
        $palettes = ColorPicker_Palette::getAll();
        foreach ($palettes as $palette) {
            $jsonArray[] = $palette->getStdClass();
        }

        // set results
        $result->palettes = $jsonArray;
        $result->count = count($jsonArray);
        $result->success = true;
        
        echo Zend_Json::encode($result);
    }

    /**
     * Delete a palette from the database. The ID should be the numeric
     * ID of the palette.
     * (url: /plugin/ColorPicker/palette/destroy) 
     */
    public function destroyAction()
    {
        // get array of palettes
        $palettes = $this->_getParam('palettes');

        // define results
        $count = 0;
        $success = false;
        $result = new stdClass();

        if ($palettes) {
            $palettes = Zend_Json::decode($palettes, Zend_Json::TYPE_OBJECT);


            $success = true;

            // delete palettes
            try {
                foreach ($palettes as $palette) {
                    ColorPicker_Palette::delete($palette->id);
                    ++$count;
                }
            } catch (Exception $exception) {
                Logger::warn('ColorPicker: exception - ' . $exception->getMessage());
                $success = false;
            }
        }

        // set results
        $result->count = $count;
        $result->success = $success;
        
        echo Zend_Json::encode($result);
    }
    
    /**
     * Return an array of colors that is built from a given palette.
     * It recurses deeper if it finds a reference to another palette.
     * 
     * @param ColorPicker_Palette $palette 
     */
    private function parsePalette($palette) 
    {
        $colors = array();
        foreach($palette->getColors() as $color) {
            if(preg_match('/^@\{[\w]+\}$/', $color)) {
                $paletteName = str_replace(array('@', '{', '}'), '', $color);
                $secondPalette = ColorPicker_Palette::get($paletteName);
                $paletteColors = $this->parsePalette($secondPalette);
                foreach($paletteColors as $paletteColor) {
                    $colors[] = $paletteColor;
                }
            } else {
                $colors[] = $color;
            }
        }
        
        return $colors;
    }

}
