<?php

class Safepay_Safepay_PaymentController extends Mage_Core_Controller_Front_Action {

	// The redirect action is triggered when someone places an order
	public function redirectAction() {
		// $this->loadLayout();
        // $block = $this->getLayout()->createBlock('Mage_Core_Block_Template','safepay',array('template' => 'safepay/redirect.phtml'));
		// $this->getLayout()->getBlock('content')->append($block);
		// $this->renderLayout();
		$env = Mage::helper('safepay')->get_environment();
		$order = new Mage_Sales_Model_Order();
	    $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
		$order->loadByIncrementId($orderId);
		$result = Mage::helper('safepay')->create_charge(
			$order->getGrandTotal(), Mage::app()->getStore()->getCurrentCurrencyCode(), $env
		);
		// echo '<pre>';
		// print_r($result);
		if ($result == 'error') {
			return array('result' => 'fail');
		}

		$charge = $result->data;

		$order->setComment($charge->token);
		$order->save();

		$hosted_url = Mage::helper('safepay')->construct_url($order, $charge->token);
		Mage::app()->getFrontController()->getResponse()->setRedirect($hosted_url);
	}
	
	// The response action is triggered when your gateway sends back a response after processing the customer's payment
	public function responseAction() {
		if($this->getRequest()->isPost()) {
			
			/*
			/* Your gateway's code to make sure the reponse you
			/* just got is from the gatway and not from some weirdo.
			/* This generally has some checksum or other checks,
			/* and is provided by the gateway.
			/* For now, we assume that the gateway's response is valid
			*/

			
			
			$validated = true;
			$orderId = $_POST['order_id']; // Generally sent by gateway
			
			if($orderId) {
				// Payment was successful, so update the order's state, send order email and move to the success page
				$order = Mage::getModel('sales/order');
				$order->load($orderId);
				$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Gateway has authorized the payment.');
				
				$order->sendNewOrderEmail();
				$order->setEmailSent(true);
				
				$order->save();
			
				Mage::getSingleton('checkout/session')->unsQuoteId();
				
				Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array('_secure'=>true));
			}
			else {
				// There is a problem in the response we got
				$this->cancelAction();
				Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/failure', array('_secure'=>true));
			}
		}
		else
			Mage_Core_Controller_Varien_Action::_redirect('');
	}
	
	// The cancel action is triggered when an order is to be cancelled
	public function cancelAction() {
        if (Mage::getSingleton('checkout/session')->getLastRealOrderId()) {
            $order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
            if($order->getId()) {
				// Flag the order as 'cancelled' and save it
				$order->cancel()->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Gateway has declined the payment.')->save();
			}
        }
	}
}