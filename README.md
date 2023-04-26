# Omnipay: NMI (Network Merchants Inc.)

**NMI (Network Merchants Inc.) driver for the Omnipay PHP payment processing library**


[Omnipay](https://github.com/thephpleague/omnipay) is a framework agnostic, multi-gateway payment
processing library for PHP 5.3+. This package implements [NMI](https://www.nmi.com/) (Network Merchants Inc.) support for Omnipay.


## Payment-API Tips
This library _**does not**_ use recurring payments like the other gateways.
Tests are in the tests folder.

Message folder contains all the code for requests and responses to Paytrace.  These are accessed through the `*Gateway.php` classes.

You'll need Payment-API checked out in the same parent folder to this repo.  That payment-api will need a valid  `.env` file, or you'll have failures whenever it tries to write the DB.

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

```json
{
    "require": {
        "mfauveau/omnipay-nmi": "~2.0"
    }
}
```

And run composer to update your dependencies:

$ curl -s http://getcomposer.org/installer | php
$ php composer.phar update

## Basic Usage

The following gateways are provided by this package:

* NMI (Network Merchants Inc.)

For general usage instructions, please see the main [Omnipay](https://github.com/thephpleague/omnipay)
repository.

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release anouncements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/mfauveau/omnipay-nmi/issues),
or better yet, fork the library and submit a pull request.
