<?php
/**
 * This file is part of Quafzi_VatChecker for Magento.
 *
 * @license MIT

 * @author Pascal Querner <pascal.querner@mscg.de>
 * @category Quafzi
 * @package Quafzi_VatChecker
 * @copyright Copyright (c) 2016 Pascal Querner (https://mscg.de)
 */

class Quafzi_VatChecker_Helper_Core_Data extends Mage_Core_Helper_Data
{
    /**
     * Check whether specified country is in EU countries list
     *
     * @added "EL" for "Greece" to list, see \Quafzi_VatChecker_Helper_Customer_Data::_checkCountryCode
     *
     * @param string $countryCode
     * @param null|int $storeId
     * @return bool
     */
    public function isCountryInEU($countryCode, $storeId = null)
    {
        $euCountries = explode(',', Mage::getStoreConfig(self::XML_PATH_EU_COUNTRIES_LIST, $storeId));
        $euCountries[] = 'EL'; //for greece
        return in_array($countryCode, $euCountries);
    }
}