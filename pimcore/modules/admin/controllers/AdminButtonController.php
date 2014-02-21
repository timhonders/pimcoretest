<?php
/**
 * Pimcore
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.pimcore.org/license dsf sdaf asdf asdf
 *
 * @copyright  Copyright (c) 2009-2013 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     New BSD License
 */

class Admin_AdminButtonController extends Pimcore_Controller_Action_Admin {

    public function featureRequestAction () {

        $type = "feature";
        $this->view->type = $type;

        $this->featureBug();
    }

    public function bugReportAction () {

        $type = "bug";
        $this->view->type = $type;

        $this->featureBug();
    }

    protected function featureBug() {
        $conf = Pimcore_Config::getSystemConfig();
        $email = $conf->general->contactemail;
        $this->view->contactEmail = $email;

        if(!$this->getParam("submit")) {
            if(Pimcore_Image_HtmlToImage::isSupported()) {
                $file = PIMCORE_TEMPORARY_DIRECTORY . "/screen-" . uniqid() . ".jpeg";
                Pimcore_Image_HtmlToImage::convert($this->getParam("url"), $file, 1280, "jpeg");
                $this->view->image = str_replace(PIMCORE_DOCUMENT_ROOT, "", $file);
            }
        } else {
            // send the request
            $type = $this->view->type;
            $urlParts = parse_url($this->getParam("url"));
            $subject = "Feature Request for ";
            if($type == "bug") {
                $subject = "Bug Report for ";
            }

            $subject .=  $urlParts["host"];

            $mail = Pimcore_Tool::getMail($email, $subject, "UTF-8");
            $mail->setIgnoreDebugMode(true);

            $bodyText = "URL: " . $this->getParam("url") . "\n\n";
            $bodyText .= "Description: \n\n" . $this->getParam("description");

            $markers = Zend_Json::decode($this->getParam("markers"));
            $image = null;

                $screenFile = PIMCORE_DOCUMENT_ROOT . $this->getParam("screenshot");

                list($width, $height) = getimagesize($screenFile);
                $im = imagecreatefromjpeg($screenFile);
                $font = PIMCORE_DOCUMENT_ROOT . "/pimcore/static/font/vera.ttf";
                $fontSize = 10;

                if($markers && count($markers) > 0) {
                    foreach ($markers as $marker) {
                        // set up array of points for polygon

                        $x = $marker["position"]["left"] * $width / 100;
                        $y = $marker["position"]["top"] * $height / 100;

                        $bbox = imagettfbbox($fontSize, 0, $font, $marker["text"]);

                        $textWidth = $bbox[4] + 10;

                        $values = array(
                            $x, $y,         // 1
                            $x-10, $y-10,   // 2
                            $x-10, $y-40,   // 3
                            $x+$textWidth, $y-40,  // 4
                            $x+$textWidth, $y-10,  // 5
                            $x+10, $y-10    // 6
                        );

                        $textcolor = imagecolorallocate($im, 255,255,255);
                        $bgcolor = imagecolorallocatealpha($im, 0,0,0,30);

                        // draw a polygon
                        imagefilledpolygon($im, $values, 6, $bgcolor);
                        imagettftext($im, $fontSize, 0, $x, $y-20, $textcolor, $font, $marker["text"]);
                    }
                }

                imagejpeg($im, $screenFile);
                imagedestroy($im);

                $image = file_get_contents($screenFile);
                unlink($screenFile);

            if($image) {
                $bodyText .= "\n\n\nsee attached file: screen.jpg";

                $at = $mail->createAttachment($image);
                $at->type        = 'image/jpeg';
                $at->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
                $at->encoding    = Zend_Mime::ENCODING_BASE64;
                $at->filename    = 'screen.jpg';
            }

            if($type == "bug") {
                $bodyText .= "\n\n";
                $bodyText .= "Details: \n\n";

                foreach ($_SERVER as $key => $value) {
                    $bodyText .= $key . " => " . $value . "\n";
                }
            }

            $mail->setBodyText($bodyText);
            $mail->send();
        }

        $this->renderScript("/admin-button/feature-bug.php");
    }

    public function promoteAction () {
        if($this->getParam("submit")) {

            $conf = Pimcore_Config::getSystemConfig();
            $email = $conf->general->contactemail;
            $this->view->contactEmail = $email;

            $urlParts = parse_url($this->getParam("url"));
            $subject = "Promotion Enquiry for ";
            $subject .=  $urlParts["host"];

            $mail = Pimcore_Tool::getMail($email, $subject, "UTF-8");
            $mail->setIgnoreDebugMode(true);

            $bodyText = "Host: " . $urlParts["host"] . "\n\n";
            $bodyText .= "URL: " . $this->getParam("url") . "\n\n";
            $bodyText .= "Ad-Type: " . $this->getParam("type") . "\n";
            $bodyText .= "Budget: " . $this->getParam("budget") . "\n";
            $bodyText .= "Duration: " . $this->getParam("duration") . "\n\n";
            $bodyText .= "Notes: \n" . $this->getParam("notes") . "\n";

            $mail->setBodyText($bodyText);
            $mail->send();
        }
    }

    public function personaAction() {

        $list = new Tool_Targeting_Persona_List();
        $list->setCondition("active = 1");
        $this->view->personas = $list->load();
    }
}
