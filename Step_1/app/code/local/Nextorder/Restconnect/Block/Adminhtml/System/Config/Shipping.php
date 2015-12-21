<?php
/**
 * Created by PhpStorm.
 * User: tiemanntan
 * Date: 04/12/15
 * Time: 16:31
 */
class Nextorder_Restconnect_Block_Adminhtml_System_Config_Shipping
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract{

    protected $_itemRendererForSH;
    protected $_itemRendererForPRI;

    public function _prepareToRender()
    {
        $this->addColumn('shipping', array(
            'label' => Mage::helper('restconnect')->__('Shipping Methode'),
            'renderer' => $this->_getRendererForSH(),
            'style' => 'width:100px',
        ));

        $this->addColumn('priority', array(
            'label' => Mage::helper('restconnect')->__('Priority'),
            'renderer' => $this->_getRendererForPRI(),
            'style' => 'width:100px',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('restconnect')->__('hinzufügen');
    }
    protected function  _getRendererForSH(){

        if (!$this->_itemRendererForSH) {
            $this->_itemRendererForSH = $this->getLayout()->createBlock(
                'restconnect/adminhtml_system_config_shippingmethod', '',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_itemRendererForSH;
    }

    protected function  _getRendererForPRI(){

        if (!$this->_itemRendererForPRI) {
            $this->_itemRendererForPRI = $this->getLayout()->createBlock(
                'restconnect/adminhtml_system_config_priority', '',
                array('is_render_to_js_template' => true)
            );
        }
        return $this->_itemRendererForPRI;
    }

    protected function _prepareArrayRow(Varien_Object $row){

        $row->setData(
            'option_extra_attr_' . $this->_getRendererForSH()
                ->calcOptionHash($row->getData('shipping')),
            'selected="selected"'
        );

        $row->setData(
            'option_extra_attr_' . $this->_getRendererForPRI()
                ->calcOptionHash($row->getData('priority')),
            'selected="selected"'
        );
    }
}