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
class ColorPicker_Palette
{

    /**
     * The ID of the palette.
     * 
     * @var int 
     */
    protected $_id;

    /**
     * The name of the palette.
     * 
     * @var string 
     */
    protected $_name;

    /**
     * The colors in this palette.
     * 
     * @var array 
     */
    protected $_colors;

    /**
     * Default constructor.
     * 
     * @param int $id
     * @param string $name
     * @param array $colors 
     */
    public function __construct($id = "", $name = "", $colors = array())
    {
        $this->_id = $id;
        $this->_name = $name;
        $this->_colors = $this->setColors($colors);
    }

    /**
     * Retrieve a palette from the database.
     * 
     * @param string $name
     * @return ColorPicker_Palette 
     * 
     * @throws Pimcore_API_Plugin_Exception 
     */
    public static function get($name)
    {
        // connect to database and fetch row
        $db = Pimcore_Resource_Mysql::get();
        $select = $db->select()
                ->from(ColorPicker_Plugin::PALETTE_TABLE_NAME)
                ->where('name = ?', $name);

        Logger::debug('ColorPicker: query - ' . $select->assemble());

        $result = $select->query();
        $row = $result->fetchObject();

        // create object
        $palette = new ColorPicker_Palette();
        if ($row) {
            $palette->setId($row->id);
            $palette->setName($row->name);
            $palette->setColors(json_decode($row->colors));
        } else {
            throw new Pimcore_API_Plugin_Exception('ColorPicker: no palette with that name was found');
        }

        return $palette;
    }

    /**
     * Get all palettes from the database and return an array
     * of ColorPicker_Palette objects.
     * 
     * @return array
     */
    public static function getAll()
    {
        // connect to database and perform query
        $db = Pimcore_Resource_Mysql::get();
        $select = $db->select()
                ->from(ColorPicker_Plugin::PALETTE_TABLE_NAME);

        Logger::debug('ColorPicker: query - ' . $select->assemble());

        $results = $select->query();

        // loop over results and create array of palettes
        $palettes = array();
        while ($row = $results->fetchObject()) {
            $palette = new ColorPicker_Palette();
            $palette->setId($row->id);
            $palette->setName($row->name);
            $palette->setColors(json_decode($row->colors));
            $palettes[] = $palette;
        }

        return $palettes;
    }

    /**
     * Create a Palette object from a JSON encoded string.
     * 
     * @param object $object
     * @return ColorPicker_Palette
     * 
     * @throws Pimcore_API_Plugin_Exception 
     */
    public static function getFromJson($object)
    {
        // get properties from object and create palette
        $palette = new ColorPicker_Palette();
        if ($object) {
            $palette->setId($object->id);
            $palette->setName($object->name);
            
            if (!is_array($object->colors)) {
                $colors = explode(",", $object->colors);
                $palette->setColors($colors);
            } else {
                $palette->setColors($object->colors);
            }
        } else {
            throw new Pimcore_API_Plugin_Exception('ColorPicker: invalid JSON passed to method');
        }

        return $palette;
    }

    /**
     * Delete a palette from the database.
     * 
     * @param int $id
     * 
     * @throws Pimcore_API_Plugin_Exception 
     */
    public static function delete($id)
    {
        $db = Pimcore_Resource_Mysql::get();
        $result = $db->delete(ColorPicker_Plugin::PALETTE_TABLE_NAME, array('id = ?' => $id));

        if ($result) {
            Logger::debug('ColorPicker: removed palette with ID ' . $id);
        } else {
            Logger::debug('ColorPicker: failed to remove palette from database');
            throw new Pimcore_API_Plugin_Exception('ColorPicker: failed to remove palette from database');
        }

        return $result;
    }

    /**
     * Insert a palette into the database.
     * 
     * @return int
     * 
     * @throws Pimcore_API_Plugin_Exception
     */
    public function insert()
    {
        $db = Pimcore_Resource_Mysql::get();

        $data = array();
        $data['id'] = $this->_id;
        $data['name'] = $this->_name;
        $data['colors'] = json_encode($this->_colors);

        $result = $db->insert(ColorPicker_Plugin::PALETTE_TABLE_NAME, $data);

        if ($result) {
            $this->setId($db->lastInsertId(ColorPicker_Plugin::PALETTE_TABLE_NAME));
            Logger::debug('ColorPicker: added palette with ID ' . $this->_id);
        } else {
            Logger::debug('ColorPicker: error while adding palette with ID ' . $this->_id);
            throw new Pimcore_API_Plugin_Exception('ColorPicker: error while adding palette');
        }

        return $result;
    }

    /**
     * Update a palette in the database.
     * 
     * @return int
     * 
     * @throws Pimcore_API_Plugin_Exception
     */
    public function update()
    {
        $db = Pimcore_Resource_Mysql::get();

        $data = array();
        $data['name'] = $this->_name;
        $data['colors'] = json_encode($this->_colors);
        
        $result = false;
        
        try {
            $db->update(ColorPicker_Plugin::PALETTE_TABLE_NAME, $data, array('id = ?' => $this->_id));
            $result = true;
        } catch (Zend_Db_Adapter_Exception $exception) {
            Logger::debug('ColorPicker: exception while updating palette - ' . $exception);
        }
        
        if ($result) {
            Logger::debug('ColorPicker: updated palette with ID ' . $this->_id);
        } else {
            Logger::debug('ColorPicker: failed to update palette with ID ' . $this->_id);
            throw new Pimcore_API_Plugin_Exception('ColorPicker: error while updating palette');
        }

        return $result;
    }

    /**
     * Return the ID of this palette.
     * 
     * @return int 
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Set the ID of this palette.
     * 
     * @param int $id
     * @return ColorPicker_Palette 
     */
    public function setId($id)
    {
        $this->_id = $id;

        return $this;
    }

    /**
     * Return the name of this palette.
     * 
     * @return string 
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set the name of this palette.
     * 
     * @param string $name
     * @return ColorPicker_Palette 
     */
    public function setName($name)
    {
        if ($name) {
            $this->_name = $name;
        }

        return $this;
    }

    /**
     * Get the colors in this palette.
     * 
     * @return array 
     */
    public function getColors()
    {
        return $this->_colors;
    }

    /**
     * Set the colors in this palette. Colors should always be uppercase
     * and not prefixed with a #.
     * 
     * @param array $colors
     * @return ColorPicker_Palette 
     */
    public function setColors($colors)
    {
        if (is_array($colors)) {
            $newColors = array();
            foreach($colors as $color) {
                if(!preg_match('/^@\{[\w]+\}/', $color)) {
                    $color = strtoupper($color);
                }
                $color = str_replace('#', '', $color);
                $newColors[] = $color;
            }
            
            $this->_colors = $newColors;
        }

        return $this;
    }

    /**
     * Return a JSON representation of this object.
     * 
     * @return string
     */
    public function getJson()
    {
        // use a stdClass object to rename private properties
        $object = $this->getStdClass();
        return json_encode($object);
    }

    /**
     * Create a stdClass object that can be used in JSON
     * and AJAX.
     * 
     * @return stdClass
     */
    public function getStdClass()
    {
        $object = new stdClass();
        $object->id = $this->_id;
        $object->name = $this->_name;
        $object->colors = $this->_colors;
        return $object;
    }

}

