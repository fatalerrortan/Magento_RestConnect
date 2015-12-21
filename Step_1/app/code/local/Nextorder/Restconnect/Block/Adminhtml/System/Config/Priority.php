<?php
/**
 * Created by PhpStorm.
 * User: tiemanntan
 * Date: 04/12/15
 * Time: 16:34
 */
class Nextorder_Restconnect_Block_Adminhtml_System_Config_Priority
    extends Mage_Core_Block_Html_Select{

    public function _toHtml(){
        /*
           $options = Mage::getSingleton('adminhtml/system_config_source_country')
               ->toOptionArray();
        */
        $options = array(
            array('value' => 'a', 'label' => '__A__'),
            array('value' => 'b', 'label' => '__B__'),
            array('value' => 'c', 'label' => '__C__'),
        );

        foreach ($options as $option) {
            $this->addOption($option['value'], $option['label']);
        }

        //Superclass

        if (!$this->_beforeToHtml()) {
            return '';
        }

        $html = '<select style="width:50px" name="' . $this->getName() . '" id="' . $this->getId() . '" class="'
            . $this->getClass() . '" title="' . $this->getTitle() . '" ' . $this->getExtraParams() . '>';
        $values = $this->getValue();

        if (!is_array($values)){
            if (!is_null($values)) {
                $values = array($values);
            } else {
                $values = array();
            }
        }

        $isArrayOption = true;
        foreach ($this->getOptions() as $key => $option) {
            if ($isArrayOption && is_array($option)) {
                $value  = $option['value'];
                $label  = (string)$option['label'];
                $params = (!empty($option['params'])) ? $option['params'] : array();
            } else {
                $value = (string)$key;
                $label = (string)$option;
                $isArrayOption = false;
                $params = array();
            }

            if (is_array($value)) {
                $html .= '<optgroup label="' . $label . '">';
                foreach ($value as $keyGroup => $optionGroup) {
                    if (!is_array($optionGroup)) {
                        $optionGroup = array(
                            'value' => $keyGroup,
                            'label' => $optionGroup
                        );
                    }
                    $html .= $this->_optionToHtml(
                        $optionGroup,
                        in_array($optionGroup['value'], $values)
                    );
                }
                $html .= '</optgroup>';
            } else {
                $html .= $this->_optionToHtml(
                    array(
                        'value' => $value,
                        'label' => $label,
                        'params' => $params
                    ),
                    in_array($value, $values)
                );
            }
        }
        $html .= '</select>';
        return $html;

        //Superclass

        //  return parent::_toHtml();
    }

    public function setInputName($value){

        return $this->setName($value);
    }


}