<?php

/**
 * This source file is subject to the new BSD license that is 
 * available through the world-wide-web at this URL:
 * http://www.pimcore.org/license
 *
 * @category   Pimcore
 * @package    Document
 * @copyright  Copyright (c) 2012 Amgate B.V. (http://www.amgate.com)
 * @author     Leon Rodenburg <leon.rodenburg@amgate.com>
 * @license    http://www.pimcore.org/license
 */
class Document_Tag_Color extends Document_Tag
{

    /**
     * The data of the color object.
     * 
     * @var array
     */
    public $data;

    /**
     * Return the frontend representation of the color.
     * The color is always in hexadecimal notation and
     * prepended with a #.
     * 
     * @return string
     */
    public function frontend()
    {
        if ($this->data['color'] && !$this->data['suppressOutput']) {
            echo $this->fix($this->data['color']);
        }
    }

    /**
     * Get the palette used in this color tag.
     * 
     * @return ColorPicker_Palette 
     */
    public function getPalette()
    {
        $palette = null;

        if ($this->data['palette']) {
            $palette = ColorPicker_Palette::get($this->data['palette']);
        }

        return $palette;
    }

    /**
     * Return the color value, always prepended with a #.
     * 
     * @return string 
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Return the type of the object.
     * 
     * @return string 
     */
    public function getType()
    {
        return 'color';
    }

    /**
     * Set the data from edit mode and fix it afterwards.
     * 
     * @param string $data
     */
    public function setDataFromEditmode($data)
    {
        $this->data = $data;
    }

    /**
     * Set the data from a resource and fix it afterwards.
     * 
     * @param string $data 
     */
    public function setDataFromResource($data)
    {
        $this->data = Pimcore_Tool_Serialize::unserialize($data);
    }

    /**
     * Return whether or not the color is empty.
     * 
     * @return boolean 
     */
    public function isEmpty()
    {
        return empty($this->data);
    }

    /**
     * Fix the color by adding a # to the beginning if it
     * is not present. 
     * 
     * @param string $color
     * 
     * @return string
     */
    private function fix($color)
    {
        $hash = strpos($color, '#');
        if ($hash === false || $hash > 0) {
            $color = str_replace('#', '', $color);
            $color = '#' . $color;
        }

        return $color;
    }

}