<?php
class Kavenegar_KavenegarApi_Model_KavenegarApi extends Mage_Core_Model_Abstract {
    public
    function _construct() {
        $this->_init("Kavenegar/KavenegarApi");
    }
    public
    function getPhoneNumbers() {
        $col = Mage::getModel('customer/address')->getCollection()->addAttributeToSelect('telephone')->getItems();
        foreach($col as $address) {
            $phones[] = Mage::helper('kavenegarapi')->getPhoneNumber($address->getTelephone());
        }
        $phones = array_unique($phones);
        return $phones;
    }
    public
    function send($messageContent) {
        $numbers = $this->getPhoneNumbers();
        $message = array(
            'recipients' => $numbers,
            'message' => $messageContent
        );
        try {
            $apiClient = Mage::getModel('kavenegarapi/apiClient');
            $response = $apiClient->send($message);
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('kavenegarapi')->__('Message sent successfully'));
        } catch (ApiException $e) {
            Mage::throwException($e->getMessage());
        } catch (HttpException $e) {
            Mage::throwException($e->getMessage());
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }
    }
}
