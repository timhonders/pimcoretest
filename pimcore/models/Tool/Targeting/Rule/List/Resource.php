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
 * @package    Tool
 * @copyright  Copyright (c) 2009-2013 pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     New BSD License
 */

class Tool_Targeting_Rule_List_Resource extends Pimcore_Model_List_Resource_Abstract {

    /**
     * Loads a list of document-types for the specicifies parameters, returns an array of Document_DocType elements
     *
     * @return array
     */
    public function load() {

        $targetsData = $this->db->fetchCol("SELECT id FROM targeting_rules" . $this->getCondition() . $this->getOrder() . $this->getOffsetLimit(), $this->model->getConditionVariables());

        $targets = array();
        foreach ($targetsData as $targetData) {
            $targets[] = Tool_Targeting_Rule::getById($targetData);
        }

        $this->model->setTargets($targets);
        return $targets;
    }

}
