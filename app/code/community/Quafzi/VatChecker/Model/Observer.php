<?php
/**
 * This file is part of Quafzi_VatChecker for Magento.
 *
 * @license MIT
 * @author Thomas Birke <tbirke@netextreme.de>
 * @author Pascal Querner <pascal.querner@mscg.de>
 * @category Quafzi
 * @package Quafzi_VatChecker
 * @copyright Copyright (c) 2014 Thomas Birke (http://netextreme.de)
 * @copyright Copyright (c) 2016 Pascal Querner (https://mscg.de)
 */

/**
 * Observer Model
 * @package Quafzi_VatChecker
 */
class Quafzi_VatChecker_Model_Observer extends Mage_Customer_Model_Observer
{
    const XML_PATH_EMAIL_VAT_ERRORS_TEMPLATE  = 'customer/vatchecker/error_email_template';
    const XML_PATH_EMAIL_VAT_ERRORS_IDENTITY  = 'customer/vatchecker/error_email_identity';
    const XML_PATH_EMAIL_VAT_ERRORS_RECIPIENT = 'customer/vatchecker/error_email';
    const CUSTOMERS_PER_CYCLE = 100;

    /**
     * validate VAT numbers of all customers
     */
    public function checkCustomers()
    {
        /** @var Mage_Customer_Model_Entity_Customer_Collection $customers */
        $customers = Mage::getModel('customer/customer')
            ->getCollection()
            ->setPageSize(self::CUSTOMERS_PER_CYCLE)
            ->load();

        $pages = $customers->getLastPageNumber();
        $currentPage = 1;
        $configAddressType = Mage::helper('customer/address')->getTaxCalculationAddressType();
        $invalidCustomers = array();
        $done = 0;
        $total = $customers->getSize();

        if($customers->getSize() > 0) {
            do {
                $customers->setCurPage($currentPage);
                $customers->load();

                /** @var Mage_Customer_Model_Customer $customer */
                foreach ($customers as $customer) {
                    ++$done;
                    // echo "\r$done (" . round($done*100/$total) . '%)';
                    $customer = $customer->load($customer->getId());
                    $address = ($configAddressType == Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING)
                        ? $customer->getDefaultShippingAddress()
                        : $customer->getDefaultBillingAddress();
                    if (false === $address
                        || '' == $address->getVatId()
                        || false === Mage::helper('core')->isCountryInEU($address->getCountry())
                    ) {
                        // skip customers without addresses
                        continue;
                    }
                    // run validation for every address
                    if (false === $this->_checkCustomerVat($address)) {
                        // init invalid customer
                        if (!isset($invalidCustomers[$customer->getId()])) {
                            $invalidCustomers[$customer->getId()] = array(
                                'customer'    => $customer,
                                'invalidVats' => array()
                            );
                        }
                        // collect wrong vat ids
                        $invalidCustomers[$customer->getId()]['invalidVats'][] = $address->getVatId();
                    }
                }

                $currentPage++;
                // clear collection (if not done, the same page will be loaded each loop) - will also free memory
                $customers->clear();
            } while ($currentPage <= $pages);
        }
        $this->_alertCustomerVat($invalidCustomers);
    }

    /**
     * validate customer VAT number
     *
     * @param Mage_Customer_Model_Address $customerAddress
     * @return boolean
     */
    protected function _checkCustomerVat($customerAddress)
    {
        try {
            /** @var $customerHelper Mage_Customer_Helper_Data */
            $customerHelper = Mage::helper('customer');

            $result = $customerHelper->checkVatNumber(
                $customerAddress->getCountryId(),
                $customerAddress->getVatId()
            );
            if (false === $result->getRequestSuccess()) {
                // request failed, validation unknown, so we assume, it's ok
                return true;
            }
            if (!(bool)$result->getIsValid()) {
                // in some cases we need to skip the first 2 chars of VAT ID
                $result = $customerHelper->checkVatNumber(
                    $customerAddress->getCountryId(),
                    substr($customerAddress->getVatId(), 2)
                );
                if (false === $result->getRequestSuccess()) {
                    // request failed, validation unknown, so we assume, it's ok
                    return true;
                }
            }
            return (bool)$result->getIsValid();

        } catch (Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
    }

    /**
     * send notification mail to merchant
     *
     * @param array $invalidCustomers Array of invalid customer data
     * @return $this
     */
    protected function _alertCustomerVat($invalidCustomers)
    {
        if (!Mage::getStoreConfig(self::XML_PATH_EMAIL_VAT_ERRORS_RECIPIENT)) {
            return $this;
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $emailTemplate = Mage::getModel('core/email_template');
        /* @var $emailTemplate Mage_Core_Model_Email_Template */
        $emailTemplate->setDesignConfig(array('area' => 'backend'))
            ->sendTransactional(
                Mage::getStoreConfig(self::XML_PATH_EMAIL_VAT_ERRORS_TEMPLATE),
                Mage::getStoreConfig(self::XML_PATH_EMAIL_VAT_ERRORS_IDENTITY),
                Mage::getStoreConfig(self::XML_PATH_EMAIL_VAT_ERRORS_RECIPIENT),
                null,
                array('output' => $this->_getPrintableOutput($invalidCustomers))
            );
        $translate->setTranslateInline(true);

        return $this;
    }

    protected function _getPrintableOutput($invalidCustomers)
    {
        $output = '';
        foreach ($invalidCustomers as $invalid) {
            $customer = $invalid['customer'];
            $output .= "\n#" . $customer->getEntityId() . "\t";
            $output .= $customer->getFirstname() . ' ';
            $output .= $customer->getLastname() . ": \t";
            $output .= implode(', ', $invalid['invalidVats']);
        }
        return $output;
    }
}
