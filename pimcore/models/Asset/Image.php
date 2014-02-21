<?php
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
 * @category   Pimcore
 * @package    Asset
 * @copyright  Copyright (c) 2009-2013 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     New BSD License
 */

class Asset_Image extends Asset {

    /**
     * @var string
     */
    public $type = "image";

    /**
     * @return void
     */
    public function update() {

        // only do this if the file exists and conains data
        if($this->getDataChanged()) {
            try {
                // save the current data into a tmp file to calculate the dimensions, otherwise updates wouldn't be updated
                // because the file is written in parent::update();
                $tmpFile = $this->getTemporaryFile(true);
                $dimensions = $this->getDimensions($tmpFile);
                unlink($tmpFile);

                if($dimensions && $dimensions["width"]) {
                    $this->setCustomSetting("imageWidth", $dimensions["width"]);
                    $this->setCustomSetting("imageHeight", $dimensions["height"]);
                }
            } catch (Exception $e) {
                Logger::error("Problem getting the dimensions of the image with ID " . $this->getId());
            }

            // this is to be downward compatible so that the controller can check if the dimensions are already calculated
            // and also to just do the calculation once, because the calculation can fail, an then the controller tries to
            // calculate the dimensions on every request an also will create a version, ...
            $this->setCustomSetting("imageDimensionsCalculated", true);
        }

        parent::update();

       $this->clearThumbnails();

        // now directly create "system" thumbnails (eg. for the tree, ...)
        if($this->getDataChanged()) {
            try {
                $path = $this->getThumbnail(Asset_Image_Thumbnail_Config::getPreviewConfig());
                $path = PIMCORE_DOCUMENT_ROOT . $path;

                // set the modification time of the thumbnail to the same time from the asset
                // so that the thumbnail check doesn't fail in Asset_Image_Thumbnail_Processor::process();
                touch($path, $this->getModificationDate());
            } catch (Exception $e) {
                Logger::error("Problem while creating system-thumbnails for image " . $this->getFullPath());
                Logger::error($e);
            }
        }
    }

    /**
     * @return void
     */
    public function clearThumbnails($force = false) {

        if($this->getDataChanged() || $force) {
            recursiveDelete($this->getImageThumbnailSavePath());
        }
    }

    /**
     * @param $name
     */
    public function clearThumbnail($name) {
        $dir = $this->getImageThumbnailSavePath() . "/thumb__" . $name;
        if(is_dir($dir)) {
            recursiveDelete($dir);
        }
    }

     /**
     * Legacy method for backwards compatibility. Use getThumbnail($config)->getConfig() instead.
     * @param mixed $config
     * @return Asset_Image_Thumbnail|bool|Thumbnail
     */
    public function getThumbnailConfig($config) {

        $thumbnail = $this->getThumbnail($config);
        return $thumbnail->getConfig();
    }

    /**
     * Returns a path to a given thumbnail or an thumbnail configuration.
     * @param mixed$config
     * @return Asset_Image_Thumbnail
     */
    public function getThumbnail($config) {

       return new Asset_Image_Thumbnail($this, $config);
    }

    /**
     * @static
     * @throws Exception
     * @return null|Pimcore_Image_Adapter
     */
    public static function getImageTransformInstance () {

        try {
            $image = Pimcore_Image::getInstance();
        } catch (Exception $e) {
            $image = null;
        }

        if(!$image instanceof Pimcore_Image_Adapter){
            throw new Exception("Couldn't get instance of image tranform processor.");
        }

        return $image;
    }

    /**
     * @return string
     */
    public function getFormat() {
        if ($this->getWidth() > $this->getHeight()) {
            return "landscape";
        }
        else if ($this->getWidth() == $this->getHeight()) {
            return "square";
        }
        else if ($this->getHeight() > $this->getWidth()) {
            return "portrait";
        }
        return "unknown";
    }

    /**
     * @return string
     */
    public function getRelativeFileSystemPath() {
        return str_replace(PIMCORE_DOCUMENT_ROOT, "", $this->getFileSystemPath());
    }

    /**
     * @return array
     */
    public function getDimensions($path = null) {

        if(!$path) {
            $path = $this->getFileSystemPath();
        }

        $image = self::getImageTransformInstance();

        $status = $image->load($path);
        if($status === false) {
            return;
        }

        $dimensions = array(
            "width" => $image->getWidth(),
            "height" => $image->getHeight()
        );

        return $dimensions;
    }

    /**
     * @return int
     */
    public function getWidth() {
        $dimensions = $this->getDimensions();
        return $dimensions["width"];
    }

    /**
     * @return int
     */
    public function getHeight() {
        $dimensions = $this->getDimensions();
        return $dimensions["height"];
    }
}
