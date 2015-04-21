<?php
/**
 * This file is part of Quafzi_VatChecker for Magento.
 *
 * @license MIT
 * @author Thomas Birke <tbirke@netextreme.de>
 * @category Quafzi
 * @package Quafzi_VatChecker
 * @copyright Copyright (c) 2015 Thomas Birke (http://netextreme.de)
 */

/**
 * Data Helper
 * @package Quafzi_VatChecker
 */
class Quafzi_VatChecker_Helper_Customer_Data extends Mage_Customer_Helper_Data
{
    /**
     * Before sending request to VAT validation service and returning validation result
     * we cut off the leading country code, if the customer started the vat number with country code
     */
    public function checkVatNumber($countryCode, $vatNumber, $requesterCountryCode = '', $requesterVatNumber = '')
    {
        if (0 === stripos($vatNumber, $countryCode)) {
            $vatNumber = substr($vatNumber, strlen($countryCode));
        }
        return parent::checkVatNumber($countryCode, $vatNumber, $requesterCountryCode, $requesterVatNumber);
    }
}
