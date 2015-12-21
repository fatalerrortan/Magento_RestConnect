<?php
/**
 * Created by PhpStorm.
 * User: tiemanntan
 * Date: 06/12/15
 * Time: 22:17
 */
class Nextorder_Restconnect_Block_Adminhtml_System_Config_Tax
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract{

    protected $_itemRendererForCG;
    protected $_itemRendererForEinsatz;

    public function _prepareToRender()
    {
        $this->addColumn('customergroup', array(
            'label' => Mage::helper('restconnect')->__('Customer Group'),
            'renderer' => $this->_getRendererForCG(),
            'style' => 'width:100px',
        ));

        $this->addColumn('taxcheck', array(
            'label' => Mage::helper('restconnect')->__('With Tax'),
            'renderer' => $this->_getRendererForEinsatz(),
            'style' => 'width:100px',
        ));

        $this->addColumn('taxrate', array(
            'label' => Mage::helper('restconnect')->__('With Specical Tax Rate(%)'),
            'style' => 'width:100px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('restconnect')->__('hinzufügen');
    }
    protected function  _getRendererForCG(){

        if (!$this->_itemRendererForCG) {
            $this->_itemRendererForCG = $this->getLayout()->createBlock(
                'restconnect/adminhtml_system_config_customergroup', '',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_itemRendererForCG;
    }

    protected function  _getRendererForEinsatz(){

        if (!$this->_itemRendererForEinsatz) {
            $this->_itemRendererForEinsatz = $this->getLayout()->createBlock(
                'restconnect/adminhtml_system_config_taxcheck', '',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_itemRendererForEinsatz;
    }

    protected function _prepareArrayRow(Varien_Object $row){

        $row->setData(
            'option_extra_attr_' . $this->_getRendererForCG()
                ->calcOptionHash($row->getData('customergroup')),
            'selected="selected"'
        );

        $row->setData(
            'option_extra_attr_' . $this->_getRendererForEinsatz()
                ->calcOptionHash($row->getData('taxcheck')),
            'selected="selected"'
        );
    }
}