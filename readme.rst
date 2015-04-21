This Magento module allows to validate your customer's VAT id periodically.

Additionally, we allow customer's VAT id to be checked correctly, even if it was entered starting with country code.

Installation
============

Install via Composer_, using package name `quafzi/magento-vat-checker`.
You will need a running Magento cron.

.. _Composer: http://getcomposer.org/

Configuration
=============

Enter your eMail address at System → Configuration → Customer → Customer Configuration → Periodical VAT check.
You may adjust sender and mail template as well, but this is not required.

Contribution
============

Feel free to fork and create pull requests. Contributions are welcome.
