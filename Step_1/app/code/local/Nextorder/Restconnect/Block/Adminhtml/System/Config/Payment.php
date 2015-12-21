<?php
/**
 * Created by PhpStorm.
 * User: tiemanntan
 * Date: 04/12/15
 * Time: 12:29
 */

    class Nextorder_Restconnect_Block_Adminhtml_System_Config_Payment
        extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract{

        protected $_itemRendererForZA;
        protected $_itemRendererForPre;

        public function _prepareToRender()
        {
            $this->addColumn('zahlungsart', array(
                'label' => Mage::helper('restconnect')->__('Payment Method'),
                'renderer' => $this->_getRendererForZA(),
                'style' => 'width:100px',
            ));
            $this->addColumn('erpcode', array(
                'label' => Mage::helper('restconnect')->__('ERP Payment Code'),
                'style' => 'width:100px',
            ));
            $this->addColumn('accountingkey', array(
                'label' => Mage::helper('restconnect')->__('Accounting Key'),
                'style' => 'width:100px',
            ));

            $this->addColumn('paidprepayment', array(
                'label' => Mage::helper('restconnect')->__('As Paid Prepayment'),
                'renderer' => $this->_getRendererForPre(),
                'style' => 'width:100px',
            ));

            $this->_addAfter = false;
            $this->_addButtonLabel = Mage::helper('restconnect')->__('hinzufügen');
        }
        protected function  _getRendererForZA(){

            if (!$this->_itemRendererForZA) {
                $this->_itemRendererForZA = $this->getLayout()->createBlock(
                    'restconnect/adminhtml_system_config_zahlungsart', '',
                    array('is_render_to_js_template' => true)
                );
            }
            return $this->_itemRendererForZA;
        }

        protected function  _getRendererForPre(){

            if (!$this->_itemRendererForPre) {
                $this->_itemRendererForPre = $this->getLayout()->createBlock(
                    'restconnect/adminhtml_system_config_paidprepayment', '',
                    array('is_render_to_js_template' => true)
                );
            }
            return $this->_itemRendererForPre;
        }

        protected function _prepareArrayRow(Varien_Object $row){

            $row->setData(
                'option_extra_attr_' . $this->_getRendererForZA()
                    ->calcOptionHash($row->getData('zahlungsart')),
                'selected="selected"'
            );

            $row->setData(
                'option_extra_attr_' . $this->_getRendererForPre()
                    ->calcOptionHash($row->getData('paidprepayment')),
                'selected="selected"'
            );
        }
    }