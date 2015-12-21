<?php
/**
 * Created by PhpStorm.
 * User: tiemanntan
 * Date: 23/11/15
 * Time: 11:07
 */
require_once 'Mage/Oauth/controllers/Adminhtml/Oauth/AuthorizeController.php';

class Nextorder_Restconnect_Adminhtml_Oauth_AuthorizeController extends Mage_Oauth_Adminhtml_Oauth_AuthorizeController{

    /**
     * Init confirm page # xulin edited
     *
     * @param bool $simple
     * @return Mage_Oauth_Adminhtml_Oauth_AuthorizeController
     */
    protected function _initConfirmPage($simple = false)
    {
        /** @var $helper Mage_Oauth_Helper_Data */
        $helper = Mage::helper('oauth');
        $user_auth = Mage::helper('restconnect/data')->getConfig('admin');
        $username = $user_auth['account'];
        $password = $user_auth['password'];
        $adminSession = Mage::getSingleton('admin/session');
        $adminSession->login($username, $password);

        /** @var $session Mage_Admin_Model_Session */
        $session = Mage::getSingleton($this->_sessionName);
        /** @var $user Mage_Admin_Model_User */
        echo $this->_sessionName;
        $user = $session->getData('user');
        if (!$user) {
            $session->addError($this->__('Please login to proceed authorization.'));
            $url = $helper->getAuthorizeUrl(Mage_Oauth_Model_Token::USER_TYPE_ADMIN);
            $this->_redirectUrl($url);
            return $this;
        }

        $this->loadLayout();

        /** @var $block Mage_Oauth_Block_Adminhtml_Oauth_Authorize */
        $block = $this->getLayout()->getBlock('content')->getChild('oauth.authorize.confirm');
        $block->setIsSimple($simple);

        try {
            /** @var $server Mage_Oauth_Model_Server */
            $server = Mage::getModel('oauth/server');

            $token = $server->authorizeToken($user->getId(), Mage_Oauth_Model_Token::USER_TYPE_ADMIN);

            if (($callback = $helper->getFullCallbackUrl($token))) { //false in case of OOB
                $this->getResponse()->setRedirect($callback . ($simple ? '&simple=1' : ''));
                return $this;
            } else {
                $block->setVerifier($token->getVerifier());
                $session->addSuccess($this->__('Authorization confirmed.'));
            }
        } catch (Mage_Core_Exception $e) {
            $block->setHasException(true);
            $session->addError($e->getMessage());
        } catch (Exception $e) {
            $block->setHasException(true);
            $session->addException($e, $this->__('An error occurred on confirm authorize.'));
        }

        $this->_initLayoutMessages($this->_sessionName);
        $this->renderLayout();

        return $this;
    }
}