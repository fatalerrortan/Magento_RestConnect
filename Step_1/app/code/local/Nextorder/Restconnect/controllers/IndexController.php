<?php
/**
 * Created by PhpStorm.
 * User: tiemanntan
 * Date: 07/10/15
 * Time: 14:04
 */
    class Nextorder_Restconnect_IndexController extends Mage_Core_Controller_Front_Action{

        public function indexAction(){

            $accessCheck = $this->getRequest()->getPost();
            $postUser = $accessCheck['username'];
            $postPw = $accessCheck['password'];
            $key = $accessCheck['key'];
            $secret = $accessCheck['secret'];
            Mage::helper('restconnect/data')->checkPerPost($postUser, $postPw);
            $query = $this->getRequest()->getParam('query');
            $resultForm = $this->getRequest()->getParam('form');
            if(empty($resultForm)){
                $resultForm='xml';
            }
            $this->customerloginAction();
            $rootURL =  str_replace("/index.php/","", Mage::getUrl());
            $params = array(
                'siteUrl' => $rootURL.'/oauth',
                'requestTokenUrl' => $rootURL.'/oauth/initiate',
                'accessTokenUrl' => $rootURL.'/oauth/token',
                //'authorizeUrl' => 'http://127.0.0.1/magento/admin/oauth_authorize',//This URL is used only if we authenticate as Admin user type
                //'authorizeUrl' => str_replace( "index.php/", "index.php/admin",Mage::getModel('adminhtml/url')->getUrl('admin/oauth_authorize')),
                'consumerKey' => '21f9f6531ce622065eb9259474afd694',//Consumer key registered in server administration
                'consumerSecret' => 'fc1690c796b838679fa8f1bb2b497929',//Consumer secret registered in server administration
                'callbackUrl' => $rootURL.'/restconnect/index/callback?query='.$query .'&form=' .$resultForm,//Url of callback action below
            );
            // Initiate oAuth consumer with above parameters
            $consumer = new Zend_Oauth_Consumer($params);
            // Get request token
            $requestToken = $consumer->getRequestToken();
            $authURL = $consumer->getRedirectUrl();
            //echo $authURL. "<br/>";
            $tmpToken =  substr(strstr($authURL,"oauth_token="),12);
            //echo $tmpToken. "<br/>";
            // Get session
            $session = Mage::getSingleton('core/session');
            // Save serialized request token object in session for later use
            $session->setRequestToken(serialize($requestToken));
            $url = $rootURL.'/oauth/authorize/confirm?oauth_token='.$tmpToken;
            Mage::app()->getFrontController()->getResponse()->setRedirect($url);
        }

        public function adminAction(){

//            $this->userloginAction();
            $accessCheck = $this->getRequest()->getPost();
            $postUser = $accessCheck['username'];
            $postPw = $accessCheck['password'];
            $key = $accessCheck['key'];
            $secret = $accessCheck['secret'];
            Mage::helper('restconnect/data')->checkPerPost($postUser, $postPw);
            $resultForm = $this->getRequest()->getParam('form');
            if(empty($resultForm)){
                $resultForm='xml';
            }
            $query = $this->getRequest()->getParam('query');
            $rootURL =  str_replace("/index.php/","", Mage::getUrl());
            $params = array(
                'siteUrl' => $rootURL.'/oauth',
                'requestTokenUrl' => $rootURL.'/oauth/initiate',
                'accessTokenUrl' => $rootURL.'/oauth/token',
                'authorizeUrl' => $rootURL.'/admin/oauth_authorize',//This URL is used only if we authenticate as Admin user type
                //'authorizeUrl' => str_replace( "index.php/", "index.php/admin",Mage::getModel('adminhtml/url')->getUrl('admin/oauth_authorize')),
                'consumerKey' => $key,//Consumer key registered in server administration
                'consumerSecret' => $secret,//Consumer secret registered in server administration
                'callbackUrl' => $rootURL.'/restconnect/index/callback?query='.$query.'&form=' .$resultForm.'&key='.$key.'&secret='.$secret,//Url of callback action below
            );
            // Initiate oAuth consumer with above parameters
            $consumer = new Zend_Oauth_Consumer($params);
            // Get request token
            $requestToken = $consumer->getRequestToken();
            $authURL = $consumer->getRedirectUrl();
            echo $authURL. "<br/>";
            $tmpToken =  substr(strstr($authURL,"oauth_token="),12);
            echo $tmpToken. "<br/>";
            // Get session
            $session = Mage::getSingleton('core/session');
            // Save serialized request token object in session for later use
            $session->setRequestToken(serialize($requestToken));
//            $url = $rootURL.'/admin/oauth_authorize/confirm?oauth_token='.$tmpToken;
            $url = $rootURL.'/admin/oauth_authorize/confirm?oauth_token='.$tmpToken;
            echo $url;
            Mage::app()->getFrontController()->getResponse()->setRedirect($url);

        }

        public function callbackAction() {

            $query = $this->getRequest()->getParam('query');
            $resultForm = $this->getRequest()->getParam('form');
            $key = $this->getRequest()->getParam('key');
            $secret = $this->getRequest()->getParam('secret');
            $rootURL =  str_replace("/index.php/","", Mage::getUrl());
            //oAuth parameters
            $params = array(
                'siteUrl' => $rootURL.'/oauth',
                'requestTokenUrl' => $rootURL.'/oauth/initiate',
                'accessTokenUrl' => $rootURL.'/oauth/token',
                    'consumerKey' => $key,
                    'consumerSecret' => $secret,
            );

            // Get session
            $session = Mage::getSingleton('core/session');
            // Read and unserialize request token from session
            $requestToken = unserialize($session->getRequestToken());
            // Initiate oAuth consumer
            $consumer = new Zend_Oauth_Consumer($params);
            // Using oAuth parameters and request Token we got, get access token
            $acessToken = $consumer->getAccessToken($_GET, $requestToken);
            // Get HTTP client from access token object
            $restClient = $acessToken->getHttpClient($params);
            // Set REST resource URL
            $restClient->setUri($rootURL.'/api/rest/' . $query);
            // In Magento it is neccesary to set json or xml headers in order to work
            $restClient->setHeaders('Accept', 'application/'.$resultForm);
//            $restClient->setHeaders('Accept', 'application/json');
            // Get method
            $restClient->setMethod(Zend_Http_Client::GET);
            //Make REST request
            $response = $restClient->request();
            // Here we can see that response body contains json list of products
            //For Test
//            file_put_contents("/opt/lampp/htdocs/testOutput.txt", $response);

//            Mage::log($response, null, 'xulin.log');
            Zend_Debug::dump($response);
        }

        public function customerloginAction(){

            $user_auth = Mage::helper('restconnect/data')->getConfig('customer');
            $username = $user_auth['account'];
            $password = $user_auth['password'];

            $websiteId = Mage::app()->getWebsite()->getId();
            $store = Mage::app()->getStore();
            $customer = Mage::getModel("customer/customer");
            $customer->website_id = $websiteId;
            $customer->setStore($store);
            try {
                $customer->loadByEmail();
                $session = Mage::getSingleton('customer/session')->setCustomerAsLoggedIn($customer);
                $session->login($username, $password);
            }catch(Exception $e){

            }
        }
//    Function simulate Userlogin transfer to admin controller override

//        public function userloginAction(){
//            ///** @var $session Mage_Admin_Model_Session */
//            $username = "";
//            $password = "";
//            //Methode 1
////            $adminUser = Mage::getModel('admin/user');
////            $adminUser->authenticate($username, $password);
//           // print_r($adminUser);
//            //Methode 2
////            $adminUser = Mage::getModel('admin/user');
////            $adminUser->login($username, $password);
//
//            //Methode 3
//            $adminSession = Mage::getSingleton('admin/session');
//            $adminSession->login($username, $password);

//        }

        public function testAction(){

            $oreder = Mage::getModel('sales/order')->load(2);
            $items = $oreder->getAllItems();

            foreach($items as $item){

                echo Mage::getModel('catalog/product')->loadByAttribute('sku', $item->getSku())->getAttributeText('sellingtyp')."___".$item->getName(). "___" .$item->getSku(). "___" . $item->getPrice()."__TRUEPRICE___".Mage::getModel('catalog/product')->loadByAttribute('sku', $item->getSku())->getPrice() ."<br/>";

            }
        }

        public function test_1Action(){

            $customerGroupID = Mage::getModel('customer/customer')->load(1);
            print_r($customerGroupID->getData('language'));
        }

        public function paymentAction(){

            $ActivePaymentMethods = Mage::getModel('payment/config')->getAllMethods();
            foreach ($ActivePaymentMethods as $paymentCode=>$paymentModel) {

                $paymentTitle = Mage::getStoreConfig('payment/'.$paymentCode.'/title');

                echo $paymentTitle ."_______________".$paymentCode."<br/>";
            }

        }



}