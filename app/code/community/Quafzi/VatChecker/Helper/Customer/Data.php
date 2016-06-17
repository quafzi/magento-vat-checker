<?php
/**
 * This file is part of Quafzi_VatChecker for Magento.
 *
 * @license MIT
 * @author Thomas Birke <tbirke@netextreme.de>
 * @author Pascal Querner <pascal.querner@mscg.de>
 * @category Quafzi
 * @package Quafzi_VatChecker
 * @copyright Copyright (c) 2015 Thomas Birke (http://netextreme.de)
 * @copyright Copyright (c) 2016 Pascal Querner (https://mscg.de)
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
        $countryCode = $this->_checkCountryCode($countryCode);
        if (0 === stripos($vatNumber, $countryCode)) {
            $vatNumber = substr($vatNumber, strlen($countryCode));
        }
        return parent::checkVatNumber($countryCode, $vatNumber, $requesterCountryCode, $requesterVatNumber);
    }

    /**
     * This method will replace country codes for specific countries.
     * Example: Greece has country code "GR", but the VAT Checker requires the country code "EL"
     * It may be the case that other countries have this special treatment aswell, so maybe outsource this in some kind
     * of xml structure.
     *
     * @param $countryCode
     * @return string
     */
    protected function _checkCountryCode($countryCode) {
        switch ($countryCode) {
            case 'GR':
                $countryCode = 'EL';
                break;
            default:
                break;
        }
        return $countryCode;
    }
}
