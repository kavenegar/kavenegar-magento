<?php
class Kavenegar_KavenegarApi_Model_Config {    
    public function getApikey() {
        return Mage::getStoreConfig('kavenegarapi/main_conf/apikey');
    }
    public function getSender() {
        return Mage::getStoreConfig('kavenegarapi/main_conf/sender');
    }   
    public function getStoreName() {  
        return Mage::getStoreConfig('kavenegarapi/main_conf/storename');
    }   
    public function isApiEnabled() {      
        return (Mage::getStoreConfig('kavenegarapi/main_conf/active')==0) ? 0:1;     
    } 
	public function getAdminMobile() {
        return Mage::getStoreConfig('kavenegarapi/main_conf/admin_mobile');
    }   
  
    public function getMessageTemplate($template) { 
        $templateContent = Mage::getStoreConfig('kavenegarapi/templates/status_'.$template);      
        if (Mage::getStoreConfig('kavenegarapi/templates/status_'. $template .'_active') && !empty($templateContent))
            return $templateContent;
        
    }  
}
