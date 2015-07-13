<?php

class Webshopapps_Ordermanager_Model_Order_Observer
{
    public function export_new_order(Varien_Event_Observer $observer)
    {

	if (Mage::registry('order_observer_has_run')) {
        return $this;
	}

	Mage::register('order_observer_has_run', true);

	$incrementId = $observer->getEvent()->getOrder()->getIncrementId();

	$order = Mage::getModel('sales/order');
	//$incrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
	$incrementId = $observer->getEvent()->getOrder()->getIncrementId();
	$order->loadByIncrementId($incrementId);

        $shippingAddress = !$order->getIsVirtual() ? $order->getShippingAddress() : null;
        $billingAddress = $order->getBillingAddress();
	$shipping_address_custom = $shippingAddress->getName() .' '. $shippingAddress->getData("company") . ' '. $shippingAddress->getData("street");
        $company = $shippingAddress->getData("company");
        $uspfeedaddress = $shippingAddress->getData("street");
 	$CustId = $order->getCustomerId();
	$Attention = $order->getCustomerSuffix();
        $Name = $order->getCustomerName();
        $Address = $shipping_address_custom;
        $City =  $shippingAddress->getData("city");
        $State = $shippingAddress->getRegion();
        $PostalCode = $shippingAddress->getData("postcode");
        $Country = $shippingAddress->getCountryModel()->getName();
	$EmailAddress = $order->getCustomerEmail();
        $Phone = $shippingAddress->getData("telephone");


	$csvData[] = array($CustId, $Attention, $Name, $Address, $City, $State, $PostalCode, $Country, $EmailAddress, $Phone);

	$base_dir = Mage::getBaseDir();

	if(isset($csvData) && !empty($csvData)) {
	$handle = fopen($base_dir.'/order_shipping_export.csv', 'a');
	//fputcsv($handle, array('CustId', 'Attention', 'Name','Address','City','State','PostalCode','Country','EmailAddress','Phone'));
	foreach($csvData as $row) {
	  fputcsv($handle, $row);
	}
	fclose($handle);

	}
        
        $data = array(
                        'orders_id' => "CN".$incrementId,
                        'delivery_name' => $Name ,
                        'delivery_company' => $company,
                        'delivery_street_address' => $uspfeedaddress,
                        'delivery_suburb' => "",
                        'delivery_city' => $City,
                        'delivery_state' => $State,
                        'delivery_postcode' => $PostalCode,
                        'delivery_country' => $Country,
                        'customers_email_address' => $EmailAddress,
                        'customers_telephone' => $Phone
                );
        $this->_writeCsvDataEO($data);

    }
    
    protected function _writeCsvDataEO($data){
  	//echo DIR_WS_FEED.'upsdata.csv';
        $base_dir = Mage::getBaseDir();
  	$f = fopen($base_dir.'/feeds/upsdata.csv','a+');
  	$this->_fputcsvEO($f, $data);
  	fclose($f);
    }
    protected function _fputcsvEO ($fp, $array, $deliminator=",") {
          $line = "";
          foreach($array as $val) {
            $val = str_replace("\r\n", "\n", $val);
            if(ereg("[$deliminator\"\n\r]", $val)) {
              $val = '"'.str_replace('"', '""', $val).'"';
            }
            $line .= $val.$deliminator;
          }
          $line = substr($line, 0, (strlen($deliminator) * -1));
          $line .= "\n";
          return fputs($fp, $line);
    }

}

