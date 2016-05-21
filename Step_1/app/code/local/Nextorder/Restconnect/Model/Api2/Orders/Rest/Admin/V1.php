<?php
class Nextorder_Restconnect_Model_Api2_Orders_Rest_Admin_V1 extends Mage_Api2_Model_Resource{

    public function _retrieve(){

        $orderID = $this->getRequest()->getParam('orderID');
        return $orderID;
    }

    public function _retrieveCollection(){

        $orderID = str_replace('ph','%',$this->getRequest()->getParam('orderID'));
        $collection = $this->getCustomerCollection($orderID);
        return $collection;
    }

    public function getCustomerCollection($orderQuery){

        $collection = new Varien_Data_Collection();
        if($orderQuery == 'all'){
            $collectionForOrders = Mage::getModel('sales/order')->getCollection()
                                    ->addFieldToSelect('*')->addAttributeToFilter('increment_id', array('like' => '%-%-%'));
        }else{
            $collectionForOrders = Mage::getModel('sales/order')->getCollection()
                ->addFieldToSelect('*')->addAttributeToFilter('increment_id', array('like' => $orderQuery));
        }
        foreach($collectionForOrders as $orderOBJ){
        $varienObject = new Varien_Object();
        $varienObject->setData('customerOrder',
        array(
            'customer' => empty($orderOBJ->getData("customer_id")) ? "" : $this->getChildrenForCustomer($orderOBJ->getData("customer_id")),
            'grossTotalOrderValue' => $this->getChildrenForGrossTotalOrderValue($orderOBJ),
            'CustomerOrderHead' => $this->getChildrenForCustomerOrderHead($orderOBJ),
            'customerOrderPositions' => $this->getChildrenForcustomerOrderPositions($orderOBJ),
            ));
        $collection->addItem($varienObject);
            Mage::log("laufen bis: ".$orderOBJ->getIncrementId(), null, 'restConnect.log');
        }
    return $collection;
    }

    public function getChildrenForCustomer($customerID){

        $customerOBJ= Mage::getModel('customer/customer')->load($customerID);
        $customer = array(
//            "defaultPaymentMethodType" => $this->getDefaultPayment($customerID),
//            "customAttributes" => array("originCode" => "!!!!!!!"),
                "creditAssessmentInfo" => "",
                "externalNumber" => "",
                //"number" => $customerOBJ->getData('aleacustomerid'),
                "originMediumNumber" => "",
                "exemptFromVat" => "",
                "originMediumTargetGroupNumber" => "",
                "person" => $this->getPerson($customerOBJ)
                    );
                $groupID = $customerOBJ->getData('group_id');
                $exempt = Mage::helper('restconnect/data')->getGroupConfig($groupID);
                if($exempt['tax'] == 0){
                    $customer['exemptFromVat'] = "true";
                }else{
                    $customer['exemptFromVat'] = "false";
                }

        return $customer;
    }

    public function getPerson($customer){

    $children =array(
        "birthday" => $customer->getData("dob"),
        "email" => $customer->getData("email"),
        "vatId" => $customer->getData("taxvat"),
        "standardInvoiceAddress" => $this->getStandardAddress($customer,'billing'),
        "standardDeliveryAddress" => $this->getStandardAddress($customer, 'shipping'),
        "contactType" => "",
//Customer Attribut !!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//            "titleCode" => $customer->getData('titelcode'),
//Customer Attribut !!!!!!!!!!!!!!!!!!!!!!!!!!!!!
//            "language" => $this->getLanguage($customer),
);
        if(empty($children["vatId"])){
            $children["contactType"] = 1;
        }
        else{
            $children["contactType"] = 2;
        }

        return $children;
    }

//    public function getLanguage($customer){}

    public function getStandardAddress($customer, $addressType){

        if($addressType == "billing"){
            $addressForDefault = $customer->getPrimaryBillingAddress();
            if($addressForDefault == false){return "No Default Billing Address";}
        }
        elseif($addressType == "shipping"){
            $addressForDefault = $customer->getPrimaryShippingAddress();
            if($addressForDefault == false){return "No Default Shipping Address";}
        }
        $address = array(
            "name" => $addressForDefault->getData("lastname"),
            "firstName" => $addressForDefault->getData("firstname"),
            "title" => $addressForDefault->getData("prefix"),
            "companyName" => $addressForDefault->getData("company"),
            "street" => $addressForDefault->getStreet(1),
            "streetNumber" => $addressForDefault->getStreet(2),
            "careof" => $addressForDefault->getStreet(3),
            "district" => $addressForDefault->getData("region"),
            "town" =>  $addressForDefault->getData("city"),
            "zipCode" => $addressForDefault->getData("postcode"),
            "countryCode" => $addressForDefault->getData("country_id"),
            "mobilePriv1" => $addressForDefault->getData('telephone'),
            "phonePriv1" => $addressForDefault->getData('fax'),
            "vatId" => $addressForDefault->getData('vat_id')
            );

        return $address;
        }

        public function getChildrenForGrossTotalOrderValue($orderOBJ, $singlePrice = false){

            if(empty($orderOBJ->getData('customer_id'))){$customerGroupID = 0;}
            else{$customerGroupID = Mage::getModel('customer/customer')->load($orderOBJ->getData('customer_id'))->getData('group_id');}
            $config = Mage::helper('restconnect/data')->getGroupConfig($customerGroupID);
            $grandTotal = $orderOBJ->getData('grand_total');
            $tax = $orderOBJ->getData('tax_amount');
            if($config['tax'] == 1){return  $grandTotal;}
            if($config['tax'] == 0){return  $grandTotal - $tax;}
            if($config['tax'] == 2){return ($grandTotal - $tax) * (1 + $config['rate']/100);}
        }

    public function getChildrenForCustomerOrderHead($orderOBJ){

        $orderInkreID = $orderOBJ->getIncrementId();
        $customerGroupID = $orderOBJ->getData("customer_group_id");
        $orderDateTime = $orderOBJ->getData("created_at");
        $status = $orderOBJ->getData("status");
        $children = array(
            "noticeList" => $this->getNotice($orderOBJ),
            "number" => $orderInkreID,
//            "targetGroupNumber" => $customerGroupID,
            "shopCustomerGroupID" => $customerGroupID,
            "orderDateTime" => $orderDateTime,
            "paymentMethodType" => $this->getPaymentCode($orderOBJ),
            "invoiceToDeliveryAdress" => "",
            "invoiceAddress" => $this->getAddress($orderOBJ, "billing"),
            "deliveryAddress" => $this->getAddress($orderOBJ, "shipping"),
            "directDebit" => $this->getDirectDebit($orderOBJ),
            "status" => $status,
            "shippingService" => $this->getShippingService($orderOBJ),
            "slivered" => "",
            "orderType" => $this->getOrderType($orderOBJ),
            "mediumNumber" => "",
            //abhängig von Transportsmethoden (Admin config)
            "priority" => $this->getShippingService($orderOBJ, true),
            //if Vorkasse,  gibt AccountingKey an, sonst nicht!!!
            "paidPrepayment" => array("accountingKey" => $this->getPaymentCode($orderOBJ, true)),
            );
            if($children["invoiceAddress"] == $children["deliveryAddress"]){
                $children["invoiceToDeliveryAdress"] = 1;
                }
            else{
                $children["invoiceToDeliveryAdress"] = 0;
                }
            if($children["status"] == "closed"){
                $children["slivered"] = 1;
                }
            else{
                $children["slivered"] = 0;
                }

        return $children;
    }

    public function getShippingService($orderOBJ, $requirePriority = false){

        if($requirePriority == true){

            $shippingCode = $orderOBJ->getTracksCollection()->getFirstItem()->getData('carrier_code');
            $priority = Mage::helper('restconnect/data')->getPriority($shippingCode);

            return $priority;
        }else {

                return $orderOBJ->getTracksCollection()->getFirstItem()->getTitle();
        }
    }

    public function getPaymentCode($orderOBJ, $requireAccoutingKey = false){
//        $payment_title = $orderOBJ->getPayment()->getMethodInstance()->getTitle();
        $payment_code = $orderOBJ->getPayment()->getMethodInstance()->getCode();
        if($requireAccoutingKey == true){
            $dataResult = Mage::helper('restconnect/data')->getConfigPaymentCode($payment_code, true);
        } else {
            $dataResult = Mage::helper('restconnect/data')->getConfigPaymentCode($payment_code);
        }

        return $dataResult;
    }

    public function getNotice($orderOBJ){

        $comments = $orderOBJ->getStatusHistoryCollection(true);
        $children = array();
        foreach ($comments as $comment) {
            if(!empty($comment->getComment())){
            //                $notice['notice'.$index] = str_replace( html_entity_decode('&#xA0;&#x20AC;', ENT_COMPAT, 'UTF-8'), ' Euro',substr(strstr($comment->getComment(),'}}{{',true),3,100)) ." Created at ".$comment->getCreatedAt();
//                  $notice['notice_'.$index] = html_entity_decode(substr(strstr($comment->getComment(),'}}{{',true),3,100) ." Created at ".$comment->getCreatedAt());
                $children[] = array('notice' => $comment->getComment().  ", Created at ".$comment->getCreatedAt());
//                $index++\
            }
        }

        return $children;
    }

    public function getAddress($orderOBJ,$addressType){

        if($addressType == "billing") {
            $billingId = $orderOBJ->getBillingAddress()->getId();
            $addressForOrder = Mage::getModel('sales/order_address')->load($billingId);
        }
        elseif($addressType == "shipping"){
            $shippingId = $orderOBJ->getShippingAddress()->getId();
            $addressForOrder = Mage::getModel('sales/order_address')->load($shippingId);
        }
        $address = array(
            "name" => $addressForOrder->getData("lastname"),
            "firstName" => $addressForOrder->getData("firstname"),
            "title" => $addressForOrder->getData("prefix"),
            "companyName" => $addressForOrder->getData("company"),
            "street" => $addressForOrder->getStreet(1),
            "streetNumber" => $addressForOrder->getStreet(2),
            "careof" => $addressForOrder->getStreet(3),
            "district" => $addressForOrder->getData("region"),
            "town" =>  $addressForOrder->getData("city"),
            "zipCode" => $addressForOrder->getData("postcode"),
            "countryCode" => $addressForOrder->getData("country_id"),
        );

        return $address;
    }

    public function getDirectDebit($orderOBJ){

        $customerId = $orderOBJ->getData('customer_id');
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $directDebit = array(
            "accountNumber" => substr($customer->getData('debit_payment_account_iban'),12,10),
            "accountHolder" => $customer->getData('firstname')." ".$customer->getData('lastname'),
            "bankCode" =>substr($customer->getData('debit_payment_account_iban'),4,8),
            "IBAN" => $customer->getData('debit_payment_account_iban')
        );

        return $directDebit;
    }

    public function getOrderType($orderOBJ){

        $items = $orderOBJ->getAllItems();
        if(count($items)>1){
            return "Multi Bestellung(" . count($items) . " Produktarten)";
        } else{
            return "Einzele Bestellung";
        }
    }

    public function getAdvancePaymentNotificationSend($orderOBJ){

        $notified = $orderOBJ->getStatusHistoryCollection(true)->getData('is_customer_notified')[0]['is_customer_notified'];
        return $notified;
}

    public function getChildrenForcustomerOrderPositions($orderOBJ){

        $items = $orderOBJ->getAllItems();
        //        customerOrderPosition
        foreach($items as $item){
        $children[] = array(
            "customerOrderPosition" => array(
            "positionNumber" => $item->getId(),
                "description" => $this->getDescription($item->getSku()),
            "mediumArticleNumber" => $item->getSku(),
            //"batchNumber" => "!!!!!!!",
            "quantity" => $item->getQtyOrdered(),
            "unitPreisWithoutTax" =>  $item->getData('price'),
            "status" => $item->getStatus(),
//           To Fixing!!!
//                    "positionType" => $this->getPostionType($item, $product),
                )
            );
        }

        return $children;
    }

    public function getDescription($sku){

       return Mage::getModel('catalog/product')->load(677)->getData('description');
    }

    public function getPostionType($item, $product){

        if($item->getPrice() == $product->getPrice() && $item->getPrice() != 0){
        return substr(strstr($product->getAttributeText('sellingtyp'), '_'), 1);
        } elseif(substr(strstr($product->getAttributeText('sellingtyp'), '_'), 1) == 1){
        return 107;
        } else{
        return substr(strstr($product->getAttributeText('sellingtyp'), '_'), 1);
        }
    }
}
?>