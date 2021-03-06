<?php
class Safepay_Safepay_Model_Standard extends Mage_Payment_Model_Method_Abstract {
	protected $_code = 'safepay';
	
	protected $_isInitializeNeeded      = true;
	protected $_canUseInternal          = true;
	protected $_canUseForMultishipping  = false;
	protected $_infoBlockType = 'safepay/payment_info';
	
	public function getOrderPlaceRedirectUrl() {
		return Mage::getUrl('safepay/payment/redirect', array('_secure' => true));
	}
}
?>