<img src="https://static.openpay.com.au/brand/logo/openpay_logo_transparent.svg" width="170" alt="Openpay Logo">

Introduction
---------

### What is Magento Payment Extension?

Magento is an eCommerce platform built on open source technology, which provides online merchants with not only a flexible shopping cart system, but also control over the look and feel, content and the functionality of their online store. Magento has been fully integrated with Openpay’s online payment solutions. Installing and configuring the Hosted Payment Solution module of Openpay provide a simple, secure and convenient option for connecting to Openpay’s online payment processing platform via Magento.

### Signing up for Openpay merchant account

In order to start taking payments through Openpay, you need to sign up and get approval for an Openpay merchant account. Once your application is approved, you can configure Openpay Hosted Payment Solution module and start taking payments.

Openpay provides a test account and users for training and testing purpose.


Compatibility
---------
- PHP 5.6 or later
- Magento 2.x to 2.4.X


Requirements
---------
- At least 5.6 or the later version of PHP
- cURL extension for PHP
- JSON extension for PHP
- Multibyte String extension for PHP
- Magento should be pre-installed on your web server. The extension has been tested on Magento version 2.x to 2.4.X
- SSL: A valid security certificate is required to work over a secure channel (HTTPS) from the Magento Admin Panel or while submitting the form data from the storefront. Self-signed SSL certificates are not supported
- Curl (version 7.20.0 - 7.4.0)

[Magento 2.3 technology stack requirements](https://devdocs.magento.com/guides/v2.3/install-gde/system-requirements.html)

[Magento 2.4 system requirements](https://devdocs.magento.com/guides/v2.4/install-gde/system-requirements.html)

For further info on Magento 2 System Requirements. Please read [here](https://www.simicart.com/blog/magento-2-system-requirements).

Installation
---------

### Composer

Use [composer](https://getcomposer.org) to install the Openpay payment module. Follow the [installation instructions](https://getcomposer.org/doc/00-intro.md) if you do not already have
composer installed.

Once composer is installed, execute the following command in your project root to install this library:

```sh
composer require opy/module-payment
```


Using server SSH Access, please go to project root folder and run the following commands to ensure the configuration tasks are run, database schema is updated and Magento's cache is cleared.

- php bin/magento setup:upgrade

- php bin/magento setup:static-content:deploy -f

- php bin/magento cache:flush

Or, you can do it manually from the admin by following the path: `System` > `Tools` > `Cache Management` and then clicking on the `Flush Magento Cache` button.

Configuration
---------

In the backend, please click on the `Stores` tab in the left sidebar and go to `Configuration -> Sales-> Payment Methods`. 

Under `Recommended Solutions`, you will find the Openpay option with the link to configure different settings available for the Openpay Payment module i.e fields to enter the Auth Token provided by Openpay and use the relevant mode.

License
---------
This code is a closed-source software and released under the proprietary License by [Openpay (Pty) Ltd](https://www.openpay.com.au/).