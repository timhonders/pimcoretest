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
class Object_Class_Data_Color extends Object_Class_Data
{

    /**
     * The type of the field.
     * 
     * @var string 
     */
    public $fieldtype = 'color';

    /**
     * The width of the field.
     * 
     * @var int 
     */
    public $width;

    /**
     * The palette chosen in the field.
     * 
     * @var string 
     */
    public $palette;

    /**
     * The column type used in queries.
     * 
     * @var string
     */
    public $queryColumnType = 'varchar(7)';

    /**
     * The column type.
     * 
     * @var string 
     */
    public $columnType = 'varchar(7)';

    /**
     * The PHPDoc type.
     * 
     * @var string 
     */
    public $phpdocType = 'string';

    /**
     * Return the width of the field.
     * 
     * @return int 
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set the width of the field.
     * 
     * @param int $width 
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * Return the chosen palette.
     * 
     * @return string 
     */
    public function getPalette()
    {
        return $this->palette;
    }

    /**
     * Set the palette.
     * 
     * @param string $palette
     */
    public function setPalette($palette)
    {
        $this->palette = $palette;
    }

    /**
     * Get the data used by a resource.
     * 
     * @param mixed $data
     * @param Object_Abstract $object
     * 
     * @return mixed
     */
    public function getDataForResource($data, $object = null)
    {
        return $data;
    }

    /**
     * Get the data from the resource.
     * 
     * @param mixed $data
     * 
     * @return mixed
     */
    public function getDataFromResource($data)
    {
        return $data;
    }

    /**
     * Get the data used by a query resource.
     * 
     * @param mixed $data
     * @param Object_Abstract $object
     * 
     * @return mixed
     */
    public function getDataForQueryResource($data, $object = null)
    {
        return $data;
    }

    /**
     * Get the data used in editmode.
     * 
     * @param mixed $data
     * @param Object_Abstract $object
     * 
     * @return mixed
     */
    public function getDataForEditmode($data, $object = null)
    {
        return $this->getDataForResource($data, $object);
    }

    /**
     * Get the data from the component in editmode.
     * 
     * @param mixed $data
     * @param Object_Abstract $object
     * 
     * @return mixed
     */
    public function getDataFromEditmode($data, $object = null)
    {
        return $this->getDataFromResource($data);
    }

    /**
     * Get a version preview.
     * 
     * @param mixed $data
     * 
     * @return mixed
     */
    public function getVersionPreview($data)
    {
        return $data;
    }

}

?>
