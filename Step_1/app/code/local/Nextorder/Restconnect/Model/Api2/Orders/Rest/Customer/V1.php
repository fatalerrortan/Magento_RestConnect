<?php
///**
// * Created by PhpStorm.
// * User: tiemanntan
// * Date: 18/11/15
// * Time: 22:18
// */
//
class Nextorder_Restconnect_Model_Api2_Orders_Rest_Admin_V1 extends Mage_Api2_Model_Resource{

    public function _retrieve(){
        //retrieve a Order name by Order_ID4

        $orderID = $this->getRequest()->getParam('orderID');
//        $orderInstanz = Mage::getModel('sales/order')->loadByIncrementId($orderID);
//        $order = $this->getCustomerCollection($orderID);
//print_r($this->_retrieveCollection());
        return $orderID;

    }

    public function _retrieveCollection(){

        $orderID = $this->getRequest()->getParam('orderID');
        $collection = $this->getCustomerCollection();
        if($orderID == 'all'){
            return $collection;
        }elseif($orderID == (int)$orderID){
            $collection_item = new Varien_Data_Collection();
            $varienObject = new Varien_Object();
            foreach($collection as $item){
                $itemArray = $item->toArray();
                $orderID_API = $itemArray['customerOrder']['CustomerOrderHead']['number'];
                if($orderID == $orderID_API){
                    $varienObject->setData('customerOrder', $itemArray['customerOrder']);
                    $collection_item->addItem($varienObject);
                    return $collection_item;
                }
            }
        }

    }

    public function getCustomerCollection(){

        $collection = new Varien_Data_Collection();
        $collectionForOrders = Mage::getModel('sales/order')->getCollection();
        foreach($collectionForOrders as $orderOBJ){

            $varienObject = new Varien_Object();
            $varienObject->setData('customerOrder',
                array(
//                    Fixing for No Login Order
                    'customer' => empty($orderOBJ->getData("customer_id")) ? "" : $this->getChildrenForCustomer($orderOBJ->getData("customer_id")),
                    'grossTotalOrderValue' => $this->getChildrenForGrossTotalOrderValue($orderOBJ),
                    'CustomerOrderHead' => $this->getChildrenForCustomerOrderHead($orderOBJ),
                    'customerOrderPositions' => $this->getChildrenForcustomerOrderPositions($orderOBJ),
                )
            );
            $collection->addItem($varienObject);
        }
        return $collection;
    }

    public function getChildrenForCustomer($customerID){

        $customerOBJ= Mage::getModel('customer/customer')->load($customerID);
        $customer = array(
//            //noch nicht besetzt(1. Order als Default)(Config site)
//            "defaultPaymentMethodType" => $this->getDefaultPayment($customerID),
            //noch nicht besetzt
//            "customAttributes" => array("originCode" => "!!!!!!!"),
            "creditAssessmentInfo" => "",
            "externalNumber" => "",
//           To Fixing!!!
//            "number" => $customerOBJ->getData('aleacustomerid'),
            //noch nicht besetzt
            "originMediumNumber" => "",
            // Abhängig von KundgruppeNr.(Config Site)
            "exemptFromVat" => "",
            //noch nicht besetzt
            "originMediumTargetGroupNumber" => "",
            "person" => $this->getPerson($customerOBJ)
        );
        $groupID = $customerOBJ->getData('group_id');
        $exempt = Mage::helper('restconnect/data')->getGroupConfig($groupID);
        if($exempt['exempt'] == 1){
            $customer['exemptFromVat'] = "true";
        }else{
            $customer['exemptFromVat'] = "false";
        }


        return $customer;
    }

//    public function getDefaultPayment($customerID){}

    public function getPerson($customer){
//        Mage::log("Result: ".$customer->getPrimaryBillingAddress()->getData('lastname') , null, 'xulin.log');

//        $customer= Mage::getModel('customer/customer')->load($customerid);
//        Mage::log("Result: ".$customer->getPrimaryBillingAddress()->getData('lastname') , null, 'xulin.log');
        $children =array(
            "birthday" => $customer->getData("dob"),
            "emailPriv1" => $customer->getData("email"),
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

    public function getLanguage($customer){

        $languageCode = $customer->getData('language');
        if($languageCode == 8){
            return 'Franzoesich';
        }
        elseif($languageCode == 7){
            return "Englisch";
        }
        elseif($languageCode == 9){
            return "Italienisch";
        }
        else{
            return "Deutsch";
        }
    }

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

        if($singlePrice != false){
            $grandTotal = $singlePrice;
        }else {
            $grandTotal = $orderOBJ->getData('grand_total');
        }

        if(empty($orderOBJ->getData('customer_id'))){$customerGroupID = 0;}
        else{$customerGroupID = Mage::getModel('customer/customer')->load($orderOBJ->getData('customer_id'))->getData('group_id');}

        $config = Mage::helper('restconnect/data')->getGroupConfig($customerGroupID);
        if(empty($config)){return $grandTotal;}
        else {
            if ($config['spec'] == 1) {
                $newRate = $config['rate'];
                return ($grandTotal / 1.19) * $newRate;
            } elseif ($config['spec'] == 0) {
                return $grandTotal / $config['rate'];
            }
        }

        return $grandTotal;
    }

    public function getChildrenForCustomerOrderHead($orderOBJ){

        $orderInkreID = $orderOBJ->getIncrementId();
//        Mage::log("Result: ". $orderInkreID, null, 'xulin.log');
        $customerGroupID = $orderOBJ->getData("customer_group_id");
        $orderDateTime = $orderOBJ->getData("created_at");
        $status = $orderOBJ->getData("status");
        $children = array(
            "noticeList" => $this->getNotice($orderOBJ),
            "number" => $orderInkreID,
//            "targetGroupNumber" => $customerGroupID,
            "shopCustomerGroupID" => $customerGroupID,
            "orderDateTime" => $orderDateTime,
//            for Mapping (admin config)
            "paymentMethodType" => $this->getPaymentCode($orderOBJ),
            "invoiceToDeliveryAdress" => "",
            "invoiceAddress" => $this->getAddress($orderOBJ, "billing"),
            "deliveryAddress" => $this->getAddress($orderOBJ, "shipping"),
            "directDebit" => $this->getDirectDebit($orderOBJ),
            "status" => $status,
            "shippingService" => $this->getShippingService($orderOBJ),
            "slivered" => "",
            "orderType" => $this->getOrderType($orderOBJ),
//            "advancePaymentNotificationSend" => $this->getAdvancePaymentNotificationSend($orderOBJ),
            "mediumNumber" => "!!!!!!!",
            //abhängig von Transportsmethoden (Admin config)
            "priority" => $this->getShippingService($orderOBJ, true),
            //if Vorkasse,  gibt AccountingKey an, sonst nicht!!!
            "paidPrepayment" => array("accountingKey" => $this->getPaymentCode($orderOBJ, true)),
        );
        if($children["invoiceAddress"] == $children["deliveryAddress"]){
            $children["invoiceToDeliveryAdress"] = 'yes';
        }
        else{
            $children["invoiceToDeliveryAdress"] = 'no';
        }
        if($children["status"] == "closed"){
            $children["slivered"] = "true";
        }
        else{
            $children["slivered"] = "false";
        }

        return $children;
    }

    public function getShippingService($orderOBJ, $requirePriority = false){

        if($requirePriority == true){
            $shippingCode = $orderOBJ->getTracksCollection()->getFirstItem()->getData('carrier_code');
            $priority = Mage::helper('restconnect/data')->getPriority($shippingCode);
            return $priority;
        }
        else {
            return $orderOBJ->getTracksCollection()->getFirstItem()->getTitle();
        }
    }

    public function getPaymentCode($orderOBJ, $requireAccoutingKey = false){

//        $payment_title = $orderOBJ->getPayment()->getMethodInstance()->getTitle();
        $payment_code = $orderOBJ->getPayment()->getMethodInstance()->getCode();
        if($requireAccoutingKey == true){
            $dataResult = Mage::helper('restconnect/data')->getConfigPaymentCode($payment_code, true);
        }
        else {
            $dataResult = Mage::helper('restconnect/data')->getConfigPaymentCode($payment_code);
        }
        return $dataResult;
    }

    public function getNotice($orderOBJ){
//        $notice = array();
        $comments = $orderOBJ->getStatusHistoryCollection(true);
//        $index = 1;
        $children = array();
        foreach ($comments as $comment) {
            if(!empty($comment->getComment())){
//                $notice['notice'.$index] = str_replace( html_entity_decode('&#xA0;&#x20AC;', ENT_COMPAT, 'UTF-8'), ' Euro',substr(strstr($comment->getComment(),'}}{{',true),3,100)) ." Created at ".$comment->getCreatedAt();
//                  $notice['notice_'.$index] = html_entity_decode(substr(strstr($comment->getComment(),'}}{{',true),3,100) ." Created at ".$comment->getCreatedAt());
                $children[] = array('notice' => $comment->getComment().  ", Created at ".$comment->getCreatedAt());
//                $index++;
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
            "accountNumber" => substr($customer->getData('iban'),12,10),
            "accountHolder" => $customer->getData('firstname')." ".$customer->getData('lastname'),
            "bankCode" =>substr($customer->getData('iban'),4,8),
            "IBAN" => $customer->getData('iban')
        );

        return $directDebit;
    }

    public function getOrderType($orderOBJ){

        $items = $orderOBJ->getAllItems();
        if(count($items)>1){
            return "Multi Bestellung(" . count($items) . " Produktarten)";
        }
        else{
            return "Einzele Bestellung";
        }
    }
    public function getAdvancePaymentNotificationSend($orderOBJ){

        $notified = $orderOBJ->getStatusHistoryCollection(true)->getData('is_customer_notified')[0]['is_customer_notified'];
        return $notified;
    }

    public function getChildrenForcustomerOrderPositions($orderOBJ){
        Mage::log("Result: ".$orderOBJ->getIncrementId() , null, 'xulin.log');

        $items = $orderOBJ->getAllItems();
        //        customerOrderPosition
        foreach($items as $item){
//            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $item->getSku());
            $children[] = array(
                "customerOrderPosition" => array(
                    "positionNumber" => $item->getId(),
//                    "description" => $product->getdata('description'),
                    "mediumArticleNumber" => $item->getSku(),
//                    in Frage
                    "batchNumber" => "!!!!!!!",
                    "quantity" => $item->getQtyOrdered(),
//                    Steuer muss man noch anpassen
//           To Fixing!!!
                    "priceDiscountedManually" => $this->getChildrenForGrossTotalOrderValue($orderOBJ, $item->getData('price')),
                    "status" => $item->getStatus(),
//                    !!!noch nicht besetzt, abhaengig von Sku
//                    "mediumNumber" => "!!!!!!!",
//                  original Produkt schon config
//           To Fixing!!!
//                    "positionType" => $this->getPostionType($item, $product),
                )
            );
        }
        return $children;
    }

    public function getPostionType($item, $product){

        if($item->getPrice() == $product->getPrice() && $item->getPrice() != 0){

            return substr(strstr($product->getAttributeText('sellingtyp'), '_'), 1);
        }
        elseif(substr(strstr($product->getAttributeText('sellingtyp'), '_'), 1) == 1){

            return 107;
        }
        else{
            return substr(strstr($product->getAttributeText('sellingtyp'), '_'), 1);
        }
    }

}