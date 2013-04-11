<?php

class Lema21_CustomExport_Service_GenerateCSV {

    private $_orderIds;
    private $_collectionOrders;
    private $_contentCSV;

    public function __construct($ordersId) {
        $this->_orderIds = $ordersId;
    }

    private function _loadOrderObjects()
    {
        $this->_collectionOrders = array();

        foreach($this->_orderIds as $id) {
            $instance = Mage::getModel("sales/order")->load($id);
            array_push($this->_collectionOrders, $instance);
        }
    }

    private function _prepareData($templateLine)
    {
        $this->_contentCSV = "";

        //iterate on the orders selected
        foreach($this->_collectionOrders as $order) {

            $lineItem = "";

            // iterate on the itens in template
            foreach($templateLine as $t) {

                // order.increment_id => $order->getData("increment_id");
                // getAttributeByCode($attribute, $order)
                $item = "";
                list($object, $attribute) = explode(".", $t);

                switch($object) {

                    case "order":

                        $item = $order->getData($attribute);
                        break;

                    case "customer":

                        if ($attribute=="name") {
                            $item = $order->getData("customer_firstname") . " " .
                            $order->getData("customer_lastname");
                        } else {
                            $item = $order->getData("customer_{$attribute}");
                        }

                    break;

                    case "address":

                        $address = $order->getShippingAddress();

                        if (strpos($attribute, "street_")!==false) {
                            $street = explode("_", $attribute);
                            $item = $address->getStreet($street[1]);
                        } else {
                            $item = $address->getData($attribute);
                        }

                    break;
                }

                $lineItem.="{$item},";
            }

            // endline
            $this->_contentCSV .=$lineItem ."\n";
        }
    }
    
    public function call()
    {
        $this->_loadOrderObjects();
        
        $templateLine = Mage::helper("custom_export")->loadTemplate();

        $this->_prepareData($templateLine);

        return $this->_contentCSV;
    }

}