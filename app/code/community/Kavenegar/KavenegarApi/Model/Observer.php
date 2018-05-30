<?php
class Kavenegar_kavenegarApi_Model_Observer {
    public static $lastExecutionTime; //to avoid multiple SMS if status was changed more than one time per 2 second
    public function _construct() {
        if (!self::$lastExecutionTime)
            self::$lastExecutionTime = time();
    }
    public function orderStatusHistorySave($observer) {
        $config =   Mage::getModel('kavenegarapi/config');
        if ($config->isApiEnabled()==0) return; //do nothing if api is disabled
        $history = $observer->getEvent()->getStatusHistory();
        //only for new status
        if (!$history->getId()) {
            $order = $history->getOrder();
            $newStatus =  $order->getData('status');
            $origStatus =  $order->getOrigData('status');
            if (time()-self::$lastExecutionTime<=2)
                return;
            self::$lastExecutionTime = time();
            //if status has changed run action
            if ($newStatus!=$origStatus) {
                $message = $config->getMessageTemplate($newStatus); //get template for new status (if active and exists)
                if (!$message){  //return if no active message template
                //return;
				}
				else{
                    //getting last tracking number
                    $tracking = Mage::getResourceModel('sales/order_shipment_track_collection')->setOrderFilter($order)->getData();
                    if (!empty($tracking)) {
                        $last = count($tracking)-1;
                        $last_tracking_number = $tracking[$last]['track_number'];
                    }
                    else{
                        $last_tracking_number = ''; //if no tracking number set "no_tracking" message for {TRACKINGNUMBER} template
                    }
                    //getting order data to generate message template
                    $messageOrderData['{NAME}'] = $order->getShippingAddress()->getData('firstname');
                    $messageOrderData['{ORDERNUMBER}'] = $order->getIncrement_id();
                    $messageOrderData['{TRACKINGNUMBER}'] = $last_tracking_number;
                    $messageOrderData['{STORENAME}'] = $config->getStoreName();
                    $message     = strtr($message,$messageOrderData);
                    $receptor    = Mage::helper('kavenegarapi')->getPhoneNumber($order->getShippingAddress()->getData('telephone')); //or getBillingAddress
                    $sender=$config->getSender();
                    $apikey=$config->getApikey();
                    $localid="1000_"+$order->getIncrement_id();                
                    try {
                        $apiClient = Mage::getModel('kavenegarapi/apiClient');
                        $response = $apiClient->send($apikey,$sender,$receptor,$message,$localid);
                        //@successs add comment to order
                        $newComment = Mage::helper('kavenegarapi')->__('SMS notification sent (SMS id:').$response->entries->messageid.')' ;
                        $history->setComment($newComment);
                    } 
                    // catch(ApiException $e){
                    //      //Mage::throwException($e->getMessage());
                    //      $newComment = Mage::helper('kavenegarapi')->__('SMS notification sending error:').' "'.$e->getMessage().'"';
                    //      $history->setComment($newComment);
                    // }
                    // catch(HttpException $e){
                    //       //Mage::throwException($e->getMessage());
                    //       $newComment = Mage::helper('kavenegarapi')->__('SMS notification sending error:').' "'.$e->getMessage().'"';
                    //       $history->setComment($newComment);
                    // } 
                    catch (Exception $e) {
                        $newComment = Mage::helper('kavenegarapi')->__('SMS notification sending error:').' "'.$e->getMessage().'"';
                        $history->setComment($newComment);
                    }
				}
				
				//Admin
				if($newStatus=="pending"){	
                    $message = $config->getMessageTemplate("admin_pending"); //get template for new status (if active and exists)
                    if (!$message){  //return if no active message template
                    //return;
                        }else{
                    //getting last tracking number
                    $tracking = Mage::getResourceModel('sales/order_shipment_track_collection')->setOrderFilter($order)->getData();
                    if (!empty($tracking)) {
                        $last = count($tracking)-1;
                        $last_tracking_number = $tracking[$last]['track_number'];
                    }
                    else{
                        $last_tracking_number = ''; //if no tracking number set "no_tracking" message for {TRACKINGNUMBER} template                        
                    }
                    //getting order data to generate message template
                    $messageOrderData2['{NAME}'] = $order->getShippingAddress()->getData('firstname');
                    $messageOrderData2['{ORDERNUMBER}'] = $order->getIncrement_id();
                    $messageOrderData2['{TRACKINGNUMBER}'] = $last_tracking_number;
                    $messageOrderData2['{STORENAME}'] = $config->getStoreName();
                    $message     = strtr($message,$messageOrderData2);
                    $receptor    = $config->getAdminMobile();
                    $sender=$config->getSender();
                    $apikey=$config->getApikey();
                    $localid="2000_"+$order->getIncrement_id();
                    try {
                        $apiClient = Mage::getModel('kavenegarapi/apiClient');
                        $response = $apiClient->send($apikey,$sender,$receptor,$message,$localid);
                        //@successs add comment to order
                        $newComment = Mage::helper('kavenegarapi')->__('SMS notification sent (SMS id:').$response->entries->messageid.')' ;
                        $history->setComment($newComment);
                    } 
                    // catch(ApiException $e){
                    //      //Mage::throwException($e->getMessage());
                    //      $newComment = Mage::helper('kavenegarapi')->__('SMS notification sending error:').' "'.$e->getMessage().'"';
                    //      $history->setComment($newComment);
                    // }
                    // catch(HttpException $e){
                    //       //Mage::throwException($e->getMessage());
                    //       $newComment = Mage::helper('kavenegarapi')->__('SMS notification sending error:').' "'.$e->getMessage().'"';
                    //       $history->setComment($newComment);
                    // } 
                    catch (Exception $e) {
                        $newComment = Mage::helper('kavenegarapi')->__('SMS notification sending error:').' "'.$e->getMessage().'"';
                        $history->setComment($newComment);
                    }
                    }
			    }	
            }
        }
    }  
}
