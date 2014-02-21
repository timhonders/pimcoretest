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
class ColorPicker_Controller_Plugin_Less extends Zend_Controller_Plugin_Abstract
{

    /**
     * Called before a request is dispatched. Used to generate a Less file
     * for the current document's color tags.
     * 
     * @param Zend_Controller_Request_Abstract $request
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $config = ColorPicker_Plugin::loadConfig();
        if($config['colorPicker']['lessGeneration']) {
            // fetch LESS rules from Color tags 
            $lessRules = array();
            $document = $request->getParam('document');
            if($document && $document instanceof Document_Page) {
                $elements = $document->getElements();
                $lessRules = $this->parse($elements);
                
                // write to LESS file if there were rules
                $lessRuleCount = count($lessRules);
                
                if($lessRuleCount > 0) {
                    $lessData = '';
                    for($i = 0; $i < $lessRuleCount; ++$i) {
                        $lessData .= $lessRules[$i];
                        if($i < $lessRuleCount - 1) {
                            $lessData .= PHP_EOL;
                        }
                    }

                    $lessPath = $config['colorPicker']['lessPath'];
                    $fullPath = PIMCORE_WEBSITE_PATH . '/..' . $lessPath;
                    $fullFile = $fullPath . ColorPicker_Plugin::LESS_FILE;
                    if(!file_exists($fullFile) && is_writable($fullPath)) {
                        touch($fullFile);
                        chmod($fullFile, 0777);
                    }

                    if(is_writable($fullFile)) {
                        $fp = fopen($fullFile, 'w+');
                        fputs($fp, $lessData);
                        fclose($fp);
                    } else {
                        Logger::debug('ColorPicker: LESS path not writable');
                    }
                } else {
                    Logger::debug('ColorPicker: no color elements were found, skipping LESS generation');
                }
            }
        } else {
            Logger::debug('ColorPicker: LESS generation disabled, skipping preDispatch()');
        }
    }
    
    /**
     * Parse a document or snippet for color tags and return an array
     * of LESS rules.
     * 
     * @param object $object 
     * 
     * @return array
     */
    private function parse($elements)
    {
        $lessRules = array();
        
        if(count($elements) > 0) {
            foreach($elements as $element) {
                if($element instanceof Document_Tag_Color && $element->getType() == 'color') {
                    $value = 'inherit';
                    if(!empty($element->data['color'])) {
                        $value = '#' . $element->data['color'];
                    }
                    $lessRule = '@' . $element->getName() . ': ' . $value . ';';
                    $lessRules[] = $lessRule;
                }
                if($element instanceof Document_Tag_Snippet) {
                    $document = Document_PageSnippet::getById($element->id);
                    if($document && $document instanceof Document_Snippet) {
                        $extraRules = $this->parse($document->getElements());
                        $lessRules = array_merge($lessRules, $extraRules);
                    }
                }
            }
        }
        
        return $lessRules;
    }

}

