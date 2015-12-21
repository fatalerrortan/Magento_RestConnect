<?php
/**
 * Created by PhpStorm.
 * User: tiemanntan
 * Date: 24/11/15
 * Time: 12:44
 */

class Nextorder_Restconnect_Model_Admin_System_Config_Einsatz{

    public function toOptionArray()
    {
        return array(
            array('value'=>1, 'label'=>Mage::helper('restconnect')->__('Ja')),
            array('value'=>0, 'label'=>Mage::helper('restconnect')->__('Nein')),
        );
    }

}