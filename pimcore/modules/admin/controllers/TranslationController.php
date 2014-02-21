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

class Admin_TranslationController extends Pimcore_Controller_Action_Admin {

    public function importAction() {

        $this->checkPermission("translations");

        $admin = $this->getParam("admin");

        $tmpFile = $_FILES["Filedata"]["tmp_name"];
        if($admin){
            Translation_Admin::importTranslationsFromFile($tmpFile,true);
        }else{
            Translation_Website::importTranslationsFromFile($tmpFile,true);
        }

        $this->_helper->json(array(
            "success" => true
        ), false);

        // set content-type to text/html, otherwise (when application/json is sent) chrome will complain in
        // Ext.form.Action.Submit and mark the submission as failed
        $this->getResponse()->setHeader("Content-Type", "text/html");
    }

    public function exportAction() {

        $this->checkPermission("translations");
        $admin = $this->getParam("admin");

        // clear translation cache
        Translation_Abstract::clearDependentCache();

        if ($admin) {
            $list = new Translation_Admin_List();
        } else {
            $list = new Translation_Website_List();
        }

        $list->setOrder("asc");
        $list->setOrderKey("key");

        if ($this->getParam("filter")) {
            $filterTerm = $list->quote("%".mb_strtolower($this->getParam("filter"))."%");
            $list->setCondition("lower(`key`) LIKE " . $filterTerm . " OR lower(`text`) LIKE " . $filterTerm);
        }

        $list->load();

        $translations = array();
        foreach ($list->getTranslations() as $t) {
            $translations[] = array_merge(array("key" => $t->getKey(),
                "creationDate" => $t->getCreationDate(),
                "modificationDate" => $t->getModificationDate(),
            ), $t->getTranslations());
        }

        //header column
        $columns = array_keys($translations[0]);

        if($admin) {
            $languages = Pimcore_Tool_Admin::getLanguages();
        } else {
            $languages = Pimcore_Tool::getValidLanguages();
        }

        //add language columns which have no translations yet
        foreach ($languages as $l) {
            if (!in_array($l, $columns)) {
                $columns[] = $l;
            }
        }

        $headerRow = array();
        foreach ($columns as $key => $value) {
            $headerRow[] = '"' . $value . '"';
        }
        $csv = implode(";", $headerRow) . "\r\n";

        foreach ($translations as $t) {
            $tempRow = array();
            foreach ($columns as $key) {
                $value = $t[$key];
                //clean value of evil stuff such as " and linebreaks
                if (is_string($value)) {
                    $value = Pimcore_Tool_Text::removeLineBreaks($value);
                    $value = str_replace('"', '&quot;', $value);

                    $tempRow[$key] = '"' . $value . '"';
                } else {
                    $tempRow[$key] = $value;
                }
            }
            $csv .= implode(";", $tempRow) . "\r\n";
        }
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=\"export.csv\"");
        ini_set('display_errors',false); //to prevent warning messages in csv
        echo $csv;
        die();
    }

    public function addAdminTranslationKeysAction() {

        $this->removeViewRenderer();

        $keys = $this->getParam("keys");
        if ($keys) {
            $availableLanguages = Pimcore_Tool_Admin::getLanguages();
            $data = Zend_Json_Decoder::decode($keys);
            foreach ($data as $translationData) {

                $t = null; // reset

                try{
                    $t = Translation_Admin::getByKey($translationData);
                } catch (Exception $e) {
                    Logger::log($e);
                }
                if (!$t instanceof Translation_Admin) {
                    $t = new Translation_Admin();

                    $t->setKey($translationData);
                    $t->setCreationDate(time());
                    $t->setModificationDate(time());

                    foreach ($availableLanguages as $lang) {
                        $t->addTranslation($lang, "");
                    }

                    try {
                        $t->save();
                    } catch (Exception $e) {
                        Logger::log($e);
                    }
                }
            }
        }
    }

    public function translationsAction() {

        $admin = $this->getParam("admin");

        if ($admin) {
            $class = "Translation_Admin";
            $this->checkPermission("translations_admin");
        } else {
            $class = "Translation_Website";
            $this->checkPermission("translations");
        }

        // clear translation cache
        Translation_Website::clearDependentCache();

        if ($this->getParam("data")) {

            $data = Zend_Json::decode($this->getParam("data"));

            if ($this->getParam("xaction") == "destroy") {
                $data = Zend_Json::decode($this->getParam("data"));
                $t = $class::getByKey($data);
                $t->delete();

                $this->_helper->json(array("success" => true, "data" => array()));
            }
            else if ($this->getParam("xaction") == "update") {
                $t = $class::getByKey($data["key"]);

                foreach ($data as $key => $value) {
                    if ($key != "key") {
                        $t->addTranslation($key, $value);
                    }
                }

                if ($data["key"]) {
                    $t->setKey($data["key"]);
                }
                $t->setModificationDate(time());
                $t->save();

                $return = array_merge(array("key" => $t->getKey(),
                        "creationDate" => $t->getCreationDate(),
                        "modificationDate" => $t->getModificationDate()),
                    $t->getTranslations());

                $this->_helper->json(array("data" => $return, "success" => true));
            }
            else if ($this->getParam("xaction") == "create") {

                try {
                    $t = $class::getByKey($data["key"]);
                }
                catch (Exception $e) {

                    $t = new $class();

                    $t->setKey($data["key"]);
                    $t->setCreationDate(time());
                    $t->setModificationDate(time());

                    foreach (Pimcore_Tool::getValidLanguages() as $lang) {
                        $t->addTranslation($lang, "");
                    }
                    $t->save();
                }

                $return = array_merge(array(
                    "key" => $t->getKey(),
                    "creationDate" => $t->getCreationDate(),
                    "modificationDate" => $t->getModificationDate(),
                ), $t->getTranslations());

                $this->_helper->json(array("data" => $return, "success" => true));
            }
        }
        else {
            // get list of types
            if ($admin) {
                $list = new Translation_Admin_List();

            } else {
                $list = new Translation_Website_List();
            }

            $list->setOrder("asc");
            $list->setOrderKey("key");

            if($this->getParam("dir")) {
                $list->setOrder($this->getParam("dir"));
            }
            if($this->getParam("sort")) {
                $list->setOrderKey($this->getParam("sort"));
            }

            $list->setLimit($this->getParam("limit"));
            $list->setOffset($this->getParam("start"));
            if ($this->getParam("filter")) {
                $filterTerm = $list->quote("%".mb_strtolower($this->getParam("filter"))."%");
                $list->setCondition("lower(`key`) LIKE " . $filterTerm . " OR lower(`text`) LIKE " . $filterTerm);
            }
            $list->load();

            $translations = array();
            foreach ($list->getTranslations() as $t) {
                $translations[] = array_merge($t->getTranslations(), array("key" => $t->getKey(),
                    "creationDate" => $t->getCreationDate(),
                    "modificationDate" => $t->getModificationDate()));
            }

            $this->_helper->json(array("data" => $translations, "success" => true, "total" => $list->getTotalCount()));
        }
    }

    public function cleanupAction() {

        $listClass = "Translation_" . ucfirst($this->getParam("type")) . "_List";
        if(class_exists($listClass)) {

            $list = new $listClass();
            $list->cleanup();

            Pimcore_Model_Cache::clearTags(array("translator","translate"));

            $this->_helper->json(array("success" => true));
        }

        $this->_helper->json(array("success" => false));
    }


    /**
     * THE FOLLOWING ISN'T RELATED TO THE SHARED TRANSLATIONS OR ADMIN-TRANSLATIONS
     * XLIFF CONTENT-EXPORT & MS WORD CONTENT-EXPORT
     */
    public function contentExportJobsAction() {

        $data = Zend_Json::decode($this->getParam("data"));
        $elements = array();
        $jobs = array();
        $exportId = uniqid();
        $source = $this->getParam("source");
        $target = $this->getParam("target");
        $type = $this->getParam("type");

        // XLIFF requires region in language code
        /*$languages = Zend_Locale::getLocaleList();
        if(strlen($source) < 5) {
            foreach ($languages as $key => $value) {
                if(strlen($key) > 4 && strpos($key, $source . "_") === 0) {
                    $source = $key;
                    break;
                }
            }
        }

        if(strlen($target) < 5) {
            foreach ($languages as $key => $value) {
                if(strlen($key) > 4 && strpos($key, $target . "_") === 0) {
                    $target = $key;
                    break;
                }
            }
        }*/

        $source = str_replace("_","-", $source);
        $target = str_replace("_","-", $target);

        if($data && is_array($data)) {
            foreach ($data as $element) {
                $elements[$element["type"] . "_" . $element["id"]] = array(
                    "id" => $element["id"],
                    "type" => $element["type"]
                );

                if($element["children"]) {
                    $el = Element_Service::getElementById($element["type"], $element["id"]);
                    $listClass = ucfirst($element["type"]) . "_List";
                    $list = new $listClass();
                    $list->setCondition(($el instanceof Object_Abstract ? "o_" : "") . "path LIKE ?", array($el->getFullPath() . ($el->getFullPath() != "/" ? "/" : "") . "%"));
                    $idList = $list->loadIdList();

                    foreach($idList as $id) {
                        $elements[$element["type"] . "_" . $id] = array(
                            "id" => $id,
                            "type" => $element["type"]
                        );
                    }
                }
            }
        }

        $elements = array_values($elements);

        // one job = 10 elements
        $elements = array_chunk($elements, 10);
        foreach($elements as $chunk) {
            $jobs[] = array(array(
                "url" => "/admin/translation/" . $type . "-export",
                "params" => array(
                    "id" => $exportId,
                    "source" => $source,
                    "target" => $target,
                    "data" => Zend_Json::encode($chunk)
                )
            ));
        }

        $this->_helper->json(array(
            "success" => true,
            "jobs" => $jobs,
            "id" => $exportId
        ));
    }

    public function xliffExportAction() {

        $id = $this->getParam("id");
        $data = Zend_Json::decode($this->getParam("data"));
        $source = $this->getParam("source");
        $target = $this->getParam("target");

        $exportFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/" . $id . ".xliff";
        if(!is_file($exportFile)) {
            // create initial xml file structure
            $xliff = new SimpleXMLElement('<xliff></xliff>');
            $xliff->addAttribute('version', '1.2');
            $xliff->asXML($exportFile);
        }

        $xliff = simplexml_load_file($exportFile, null, LIBXML_NOCDATA);

        foreach ($data as $el) {
            $element = Element_Service::getElementById($el["type"], $el["id"]);
            $file = $xliff->addChild('file');
            $file->addAttribute('original', Element_Service::getElementType($element) . '-' . $element->getId());
            $file->addAttribute('source-language', $source);
            $file->addAttribute('target-language', $target);
            $file->addAttribute('datatype', "html");
            $file->addAttribute('tool', "pimcore");
            $file->addAttribute('category', Element_Service::getElementType($element));

            $header = $file->addChild('header');

            $body = $file->addChild('body');
            $addedElements = false;

            // elements
            if($element instanceof Document) {
                if(method_exists($element, "getElements")) {
                    foreach ($element->getElements() as $tag) {

                        if(in_array($tag->getType(), array("wysiwyg", "input", "textarea", "image"))) {

                            if($tag->getType() == "image") {
                                $content = $tag->getText();
                            } else {
                                $content = $tag->getData();
                            }

                            if(is_string($content)) {
                                $contentCheck = trim(strip_tags($content));
                                if(!empty($contentCheck)) {
                                    $this->addTransUnitNode($body, "tag~-~" . $tag->getName(), $content, $source);
                                    $addedElements = true;
                                }
                            }
                        }
                    }
                }

                if($element instanceof Document_Page) {
                    $data = array(
                        "title" => $element->getTitle(),
                        "description" => $element->getDescription(),
                        "keywords" => $element->getKeywords()
                    );

                    foreach ($data as $key => $content) {
                        if(!empty($content)) {
                            $this->addTransUnitNode($body, "settings~-~" . $key, $content, $source);
                            $addedElements = true;
                        }
                    }
                }
            } else if ($element instanceof Object_Concrete) {
                if($fd = $element->getClass()->getFieldDefinition("localizedfields")) {
                    $definitions = $fd->getFielddefinitions();

                    $locale = new Zend_Locale(str_replace("-","_", $source));
                    if(Pimcore_Tool::isValidLanguage((string) $locale)) {
                        $locale = (string) $locale;
                    } else {
                        $locale = $locale->getLanguage();
                    }

                    foreach($definitions as $definition) {

                        // check allowed datatypes
                        if(!in_array($definition->getFieldtype(), array("input", "textarea", "wysiwyg"))) {
                            continue;
                        }

                        $content = $element->{"get" . ucfirst($definition->getName())}($locale);

                        if(!empty($content)) {
                            $this->addTransUnitNode($body, "localizedfield~-~" . $definition->getName(), $content, $source);
                            $addedElements = true;
                        }
                    }
                }
            }

            // properties
            $properties = $element->getProperties();
            if(is_array($properties)) {
                foreach($properties as $property) {
                    if($property->getType() == "text" && !$property->isInherited()) {

                        // exclude text properties
                        if($element instanceof Document) {
                            if(in_array($property->getName(), array(
                                "language",
                                "navigation_target",
                                "navigation_exclude",
                                "navigation_class",
                                "navigation_anchor",
                                "navigation_parameters",
                                "navigation_relation",
                                "navigation_accesskey",
                                "navigation_tabindex"))) {
                                continue;
                            }
                        }

                        $content = $property->getData();
                        if(!empty($content)) {
                            $this->addTransUnitNode($body, "property~-~" . $property->getName(), $content, $source);
                            $addedElements = true;
                        }
                    }
                }
            }

            // remove file if it is empty
            if(!$addedElements) {
                $file = dom_import_simplexml($file);
                $file->parentNode->removeChild($file);
            }
        }

        $xliff->asXML($exportFile);

        $this->_helper->json(array(
            "success" => true
        ));
    }

    public function xliffExportDownloadAction() {
        $id = $this->getParam("id");
        $exportFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/" . $id . ".xliff";

        header("Content-Type: application/x-xliff+xml");
        header('Content-Disposition: attachment; filename="' . basename($exportFile) . '"');

        while(@ob_end_flush());
        flush();

        readfile($exportFile);
        @unlink($exportFile);
        exit;
    }

    public function xliffImportUploadAction() {

        $jobs = array();
        $id = uniqid();
        $importFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/" . $id . ".xliff";
        copy($_FILES["file"]["tmp_name"], $importFile);

        $xliff = simplexml_load_file($importFile, null, LIBXML_NOCDATA);
        $steps = count($xliff->file);

        for($i=0; $i<$steps; $i++) {
            $jobs[] = array(array(
                "url" => "/admin/translation/xliff-import-element",
                "params" => array(
                    "id" => $id,
                    "step" => $i
                )
            ));
        }

        $this->_helper->json(array(
            "success" => true,
            "jobs" => $jobs,
            "id" => $id
        ), false);

        // set content-type to text/html, otherwise (when application/json is sent) chrome will complain in
        // Ext.form.Action.Submit and mark the submission as failed
        $this->getResponse()->setHeader("Content-Type", "text/html");
    }

    public function xliffImportElementAction() {

        include_once("simple_html_dom.php");

        $id = $this->getParam("id");
        $step = $this->getParam("step");
        $importFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/" . $id . ".xliff";

        $xliff = simplexml_load_file($importFile, null, LIBXML_NOCDATA);
        $file = $xliff->file[(int)$step];
        $target = $file["target-language"];

        if(!Pimcore_Tool::isValidLanguage($target)) {
            $locale = new Zend_Locale($target);
            $target = $locale->getLanguage();
            if(!Pimcore_Tool::isValidLanguage($target)) {
                $this->_helper->json(array(
                    "success" => false
                ));
            }
        }

        list($type, $id) = explode("-", $file["original"]);
        $element = Element_Service::getElementById($type, $id);

        if($element) {
            foreach($file->body->{"trans-unit"} as $transUnit) {
                list($fieldType, $name) = explode("~-~", $transUnit["id"]);
                $content = $transUnit->target->asXml();
                $content = $this->unescapeXliff($content);

                if($element instanceof Document) {
                    if($fieldType == "tag" && method_exists($element, "getElement")) {
                        $tag = $element->getElement($name);
                        $tag->setDataFromEditmode($content);
                    }

                    if($fieldType == "settings" && $element instanceof Document_Page) {
                        $setter = "set" . ucfirst($name);
                        if(method_exists($element, $setter)) {
                            $element->$setter($content);
                        }
                    }
                } else if($element instanceof Object_Concrete) {
                    if($fieldType == "localizedfield") {
                        $setter = "set" . ucfirst($name);
                        if(method_exists($element, $setter)) {
                            $element->$setter($content, $target);
                        }
                    }
                }

                if($fieldType == "property") {
                    $property = $element->getProperty($name, true);
                    $property->setData($content);
                }
            }

            $element->save();
        }

        $this->_helper->json(array(
            "success" => true
        ));
    }

    protected function addTransUnitNode($xml, $name, $content, $source) {
        $transUnit = $xml->addChild('trans-unit');
        $transUnit->addAttribute("id", htmlentities($name));

        $sourceNode = $transUnit->addChild('source');
        $sourceNode->addAttribute("xmlns:xml:lang", $source);

        $node = dom_import_simplexml($sourceNode);
        $no = $node->ownerDocument;
        $f = $no->createDocumentFragment();
        $f->appendXML($this->escapeXliff($content));
        @$node->appendChild($f);
    }

    protected function unescapeXliff($content) {

        $content = preg_replace("/<\/?target([^>.]+)?>/i", "", $content);

        if(preg_match("/<\/?(bpt|ept)/", $content)) {
            $xml = str_get_html($content);
            if($xml) {
                $els = $xml->find("bpt,ept");
                foreach($els as $el) {
                    $content = html_entity_decode($el->innertext, null, "UTF-8");
                    $el->outertext = $content;
                }
            }
            $content = $xml->save();
        }

        return $content;
    }

    protected function escapeXliff($content) {
        $count = 1;
        $openTags = array();
        $final = array();

        $content = html_entity_decode($content, null, "UTF-8");

        if(!preg_match_all("/<([^>.]+)>([^<.]+)?/", $content, $matches)) {
            // return original content if it doesn't contain HTML tags
            return '<![CDATA[' . $content . ']]>';
        }

        foreach($matches[0] as $match) {
            $parts = explode(">", $match);
            $parts[0] .= ">";
            foreach ($parts as $part) {
                $part = trim($part);
                if(!empty($part)) {

                    if(preg_match("/<([a-z0-9\/]+)/", $part, $tag)) {
                        $tagName = str_replace("/", "", $tag[1]);
                        if(strpos($tag[1], "/") === false) {
                            $openTags[$count] = array("tag" => $tagName, "id" => $count);
                            $part = '<bpt id="' . $count . '"><![CDATA[' . $part . ']]></bpt>';

                            $count++;
                        } else {
                            $closingTag = array_pop($openTags);
                            $part = '<ept id="' . $closingTag["id"] . '"><![CDATA[' . $part . ']]></ept>';
                        }
                    } else {
                        $part = '<![CDATA[' . $part . ']]>';
                    }

                    if(!empty($part)) {
                        $final[] = $part;
                    }
                }
            }
        }

        $content = implode("", $final);
        return $content;
    }


    public function wordExportAction() {

        error_reporting(E_ERROR);
        ini_set("display_errors", "off");

        $id = $this->getParam("id");
        $data = Zend_Json::decode($this->getParam("data"));
        $source = $this->getParam("source");

        $exportFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/" . $id . ".html";
        if(!is_file($exportFile)) {
            /*file_put_contents($exportFile, '<!DOCTYPE html>' . "\n" . '<html>
                <head>
                    <style type="text/css">' . file_get_contents(PIMCORE_PATH . "/static/css/word-export.css") . '</style>
                </head>
                <body>
            ');*/
            Pimcore_File::put($exportFile, '<style type="text/css">' . file_get_contents(PIMCORE_PATH . "/static/css/word-export.css") . '</style>');
        }

        foreach ($data as $el) {
            try {
                $element = Element_Service::getElementById($el["type"], $el["id"]);
                $output = "";

                // check supported types (subtypes)
                if(!in_array($element->getType(), array("page","snippet", "email", "object"))) {
                    continue;
                }

                if($element instanceof Element_Interface) {
                    $output .= '<h1 class="element-headline">' . ucfirst($element->getType()) . " - " . $element->getFullPath() . ' (ID: ' . $element->getId() . ')</h1>';
                }

                if($element instanceof Document_PageSnippet) {
                    if($element instanceof Document_Page) {
                        $structuredDataEmpty = true;
                        $structuredData = '
                            <table border="1" cellspacing="0" cellpadding="5">
                                <tr>
                                    <td colspan="2"><span style="color:#cc2929;font-weight: bold;">Structured Data</span></td>
                                </tr>
                        ';

                        if($element->getTitle()) {
                            $structuredData .= '<tr>
                                    <td><span style="color:#cc2929;">Title</span></td>
                                    <td>' . $element->getTitle() . '&nbsp;</td>
                                </tr>';
                            $structuredDataEmpty = false;
                        }

                        if($element->getDescription()) {
                            $structuredData .= '<tr>
                                    <td><span style="color:#cc2929;">Description</span></td>
                                    <td>' . $element->getDescription() . '&nbsp;</td>
                                </tr>';
                            $structuredDataEmpty = false;
                        }

                        if($element->getKeywords()) {
                            $structuredData .= '<tr>
                                    <td><span style="color:#cc2929;">Keywords</span></td>
                                    <td>' . $element->getKeywords() . '&nbsp;</td>
                                </tr>';
                            $structuredDataEmpty = false;
                        }

                        if($element->getProperty("navigation_name")) {
                            $structuredData .= '<tr>
                                    <td><span style="color:#cc2929;">Navigation</span></td>
                                    <td>' . $element->getProperty("navigation_name") . '&nbsp;</td>
                                </tr>';
                            $structuredDataEmpty = false;
                        }

                        $structuredData .= '</table>';

                        if(!$structuredDataEmpty) {
                            $output .= $structuredData;
                        }
                    }


                    $html = Document_Service::render($element, array(), false);
                    $html = preg_replace("@</?(img|meta|div|section|aside|article|body|bdi|bdo|canvas|embed|footer|head|header|html)([^>]+)?>@", "", $html);
                    $html = preg_replace('/<!--(.*)-->/Uis', '', $html);

                    $dom = str_get_html($html);
                    if($dom) {

                        // remove containers including their contents
                        $elements = $dom->find("form,script,style,noframes,noscript,object,area,mapm,video,audio,iframe,textarea,input,select,button,");
                        if($elements) {
                            foreach($elements as $el) {
                                $el->outertext = "";
                            }
                        }

                        $clearText = function ($string) {
                            $string = str_replace("\r\n", "", $string);
                            $string = str_replace("\n", "", $string);
                            $string = str_replace("\r", "", $string);
                            $string = str_replace("\t", "", $string);
                            $string = preg_replace ('/&[a-zA-Z0-9]+;/', '', $string); // remove html entities
                            $string = preg_replace ('#[ ]+#', '', $string);

                            return $string;
                        };

                        // remove empty tags (where it matters)
                        $elements = $dom->find("a, li");
                        if($elements) {
                            foreach($elements as $el) {

                                $string = $clearText($el->plaintext);
                                if(empty($string)) {
                                    $el->outertext = "";
                                }
                            }
                        }


                        // replace links => links get [Linktext]
                        $elements = $dom->find("a");
                        if($elements) {
                            foreach($elements as $el) {
                                $string = $clearText($el->plaintext);
                                if(!empty($string)) {
                                    $el->outertext = "[" . $el->plaintext . "]";
                                } else {
                                    $el->outertext = "";
                                }
                            }
                        }

                        $html = $dom->save();
                        $dom->clear();
                        unset($dom);

                        // force closing tags (simple_html_dom doesn't seem to support this anymore)
                        $doc = new DOMDocument();
                        libxml_use_internal_errors(true);
                        $doc->loadHTML('<?xml encoding="UTF-8"><article>' . $html . "</article>");
                        libxml_clear_errors();
                        $html = $doc->saveHTML();

                        $bodyStart = strpos($html, "<body>")+6;
                        $bodyEnd = strpos($html, "</body>");
                        if($bodyStart && $bodyEnd) {
                            $html = substr($html, $bodyStart, $bodyEnd - $bodyStart);
                        }

                        $output .= $html;
                    }


                } else if ($element instanceof Object_Concrete) {

                    $hasContent = false;

                    if($fd = $element->getClass()->getFieldDefinition("localizedfields")) {
                        $definitions = $fd->getFielddefinitions();

                        $locale = new Zend_Locale(str_replace("-","_", $source));
                        if(Pimcore_Tool::isValidLanguage((string) $locale)) {
                            $locale = (string) $locale;
                        } else {
                            $locale = $locale->getLanguage();
                        }

                        $output .= '
                            <table border="1" cellspacing="0" cellpadding="2">
                                <tr>
                                    <td colspan="2"><span style="color:#cc2929;font-weight: bold;">Localized Data</span></td>
                                </tr>
                        ';

                        foreach($definitions as $definition) {

                            // check allowed datatypes
                            if(!in_array($definition->getFieldtype(), array("input", "textarea", "wysiwyg"))) {
                                continue;
                            }

                            $content = $element->{"get" . ucfirst($definition->getName())}($locale);

                            if(!empty($content)) {
                                $output .= '
                                <tr>
                                    <td><span style="color:#cc2929;">' . $definition->getTitle() . ' (' . $definition->getName() . ')<span></td>
                                    <td>' . $content . '&nbsp;</td>
                                </tr>
                                ';

                                $hasContent = true;
                            }
                        }

                        $output .= '</table>';
                    }

                    if(!$hasContent) {
                        $output = ""; // there's no content in the object, so reset all contents and do not inclide it in the export
                    }
                }


                // append contents
                if(!empty($output)) {
                    $f = fopen($exportFile, "a+");
                    fwrite($f, $output);
                    fclose($f);
                }
            } catch (\Exception $e) {
                Logger::error("Word Export: " . $e->getMessage());
                Logger::error($e);
            }
        }


        $this->_helper->json(array(
            "success" => true
        ));
    }

    public function wordExportDownloadAction() {
        $id = $this->getParam("id");
        $exportFile = PIMCORE_SYSTEM_TEMP_DIRECTORY . "/" . $id . ".html";

        // add closing body/html
        //$f = fopen($exportFile, "a+");
        //fwrite($f, "</body></html>");
        //fclose($f);

        // should be done via Pimcore_Document(_Adapter_LibreOffice) in the future
        if(Pimcore_Document::isFileTypeSupported("docx")) {
            $lockKey = "soffice";
            Tool_Lock::acquire($lockKey); // avoid parallel conversions of the same document

            $out = Pimcore_Tool_Console::exec(Pimcore_Document_Adapter_LibreOffice::getLibreOfficeCli() . ' --headless --convert-to docx:"Office Open XML Text" --outdir ' . PIMCORE_TEMPORARY_DIRECTORY . " " . $exportFile);

            Logger::debug("LibreOffice Output was: " . $out);

            $tmpName = PIMCORE_TEMPORARY_DIRECTORY . "/" . preg_replace("/\." . Pimcore_File::getFileExtension($exportFile) . "$/", ".docx",basename($exportFile));

            Tool_Lock::release($lockKey);
            // end what should be done in Pimcore_Document

            header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
            header('Content-Disposition: attachment; filename="' . basename($tmpName) . '"');
        } else {
            // no conversion, output html file
            $tmpName = $exportFile;
            header("Content-Type: text/html");
            header('Content-Disposition: attachment; filename="' . basename($tmpName) . '"');
        }

        while(@ob_end_flush());
        flush();

        readfile($tmpName);

        @unlink($exportFile);
        @unlink($tmpName);
        exit;
    }
}
