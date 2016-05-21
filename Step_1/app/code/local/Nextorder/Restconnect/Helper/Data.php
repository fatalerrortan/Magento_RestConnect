<?php 
	class Nextorder_Restconnect_Helper_Data extends Mage_Core_Helper_Abstract
	{

		public function getConfig($userType)
		{

			$store = Mage::app()->getStore();
			if ($userType == "admin") {
				$ad_check = Mage::getStoreConfig('restsection/restgroup_ad/restfield_ad_check', $store);
				if ($ad_check) {
					$ad_account = Mage::getStoreConfig('restsection/restgroup_ad/restfield_ad_konto', $store);
					$ad_password = Mage::getStoreConfig('restsection/restgroup_ad/restfield_ad_pw', $store);
					return array('account' => $ad_account, 'password' => $ad_password);
				} else {
					return die("permission denied, Please check the Admin Configuration");
				}
			} elseif ($userType == "customer") {

				$cm_check = Mage::getStoreConfig('restsection/restgroup_cm/restfield_cm_check', $store);
				if ($cm_check) {
					$cm_account = Mage::getStoreConfig('restsection/restgroup_cm/restfield_cm_konto', $store);
					$cm_password = Mage::getStoreConfig('restsection/restgroup_cm/restfield_cm_pw', $store);
					return array('account' => $cm_account, 'password' => $cm_password);
				} else {
					return die("permission denied, Please check the Admin Configuration");
				}
			}
		}

		public function checkPerPost($user, $password)
		{

			$store = Mage::app()->getStore();
			$ad_account = Mage::getStoreConfig('restsection/restgroup_ad/restfield_ad_konto', $store);
			$ad_password = Mage::getStoreConfig('restsection/restgroup_ad/restfield_ad_pw', $store);
			$cm_account = Mage::getStoreConfig('restsection/restgroup_cm/restfield_cm_konto', $store);
			$cm_password = Mage::getStoreConfig('restsection/restgroup_cm/restfield_cm_pw', $store);

//			if(($user != $ad_account && $password != $ad_password) || ($user != $cm_account && $password != $cm_password)){
//
//				die('invalide username oder password!');
//			}

			if ($user != $ad_account || $password != $ad_password) {
				if ($user != $cm_account || $password != $cm_password) {
					die('invalide username oder password!');
				}
			}
		}

		public function getAllActivePaymentMethod()
		{

			$ActivePaymentMethods = Mage::getModel('payment/config')->getActiveMethods();
//			$ActivePaymentMethods = Mage::getModel('payment/config')->getAllMethods();
//			$paymentArray =array(array('label'=> '!Keine Zuweisung!', 'value'=>'nokonto'));
			$paymentArray = array();
			foreach ($ActivePaymentMethods as $paymentCode => $paymentModel) {

				$paymentTitle = Mage::getStoreConfig('payment/' . $paymentCode . '/title');
				if (!empty($paymentTitle)) {

					$paymentArray[] = array(
						'label' => $paymentTitle,
						'value' => $paymentCode,
					);
				}
			}
			return $paymentArray;
		}

		public function getConfigPaymentCode($payment_code, $requireAccoutingKey = false)
		{

			$config_params = Mage::getStoreConfig('ordersection/ordergroup_payment/orderfield_payment', Mage::app()->getStore());
			//'sectionName/groupName/fieldName
			if ($config_params) {
				$config_params = unserialize($config_params);
				if (is_array($config_params)) {
					//return $config_params;
					if ($requireAccoutingKey == true) {
						foreach ($config_params as $config_param) {
							if ($config_param['zahlungsart'] == $payment_code &&
								$config_param['paidprepayment'] == 1
							) {
								return $config_param['accountingkey'];
							}
						}
					} else {
						foreach ($config_params as $config_param) {
							if ($config_param['zahlungsart'] == $payment_code) {
								return $config_param['erpcode'];
							}
						}
					}
				}
			}
		}

		public function getAllShippingMethod()
		{

			$methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
//			$methods = Mage::getSingleton('shipping/config')->getAllCarriers();
			$options = array();
			foreach ($methods as $_code => $_method) {
				if (!$_title = Mage::getStoreConfig("carriers/$_code/title")) {
					$_title = $_code;
				}
				$options[] = array('label' => $_title,
					'value' => $_code);
			}
			return $options;
		}

		public function getPriority($shippingCode)
		{

			$config_params = Mage::getStoreConfig('ordersection/ordergroup_shipping/orderfield_shipping', Mage::app()->getStore());
			//'sectionName/groupName/fieldName
			if ($config_params) {
				$config_params = unserialize($config_params);
				if (is_array($config_params)) {
					foreach ($config_params as $config_param) {
						if ($config_param['shipping'] == $shippingCode) {
							return $config_param['priority'];
						}
					}
				}
			}
		}

		public function getCustomerGroup()
		{
			$result = array();
			$groups = Mage::getModel('customer/group')->getCollection();
			foreach ($groups as $group) {
				$groupID = $group->getId();
				$groupLabel = $group->getCustomerGroupCode();
				$result[] = array('value' => $groupID, 'label' => $groupID . "___" . $groupLabel);
			}
			return $result;
		}

		public function getGroupConfig($customerGroupID)
		{

			$config_params = Mage::getStoreConfig('ordersection/ordergroup_tax/orderfield_tax', Mage::app()->getStore());
			//'sectionName/groupName/fieldName
			if ($config_params) {
				$config_params = unserialize($config_params);
				if (is_array($config_params)) {

					foreach ($config_params as $config_param) {

						if ($config_param['customergroup'] == $customerGroupID) {

							if ($config_param['taxcheck'] == 0) {
								return array('tax' => 0);
							}else{
								if(empty($config_param['taxrate'])){
									return array('tax' => 1);
								}else{
									return array('tax' =>2, 'rate'=> $config_param['taxrate']);
								}
							}
						}
					}
				}
			}
		}
	}
?>