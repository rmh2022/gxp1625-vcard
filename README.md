# VCard-based bridge for Grandstream GXP1625 IP phone

## Description

This small PHP script takes a directory that has been populated with VCard (*.vcf) files and generates a suitable XML for the Grandstream GXP1625 IP phone.

The internal VCard directory that is managed by [Radicale](https://radicale.org/) when running in CardDAV mode can be used for this purpose, with some adjustments (see Caveats). It may be useful with other CardDAV servers (haven't tried).

Tested on [Grandstream GXP1625](https://www.grandstream.com/products/ip-voice-telephony-gxp-series-ip-phones/gxp-series-basic-ip-phones/product/gxp1620/gxp1625) with firmware version 1.0.7.13. Probably works in other models.

## Installation

Get VCardParser.php from [VCard PHP library](https://github.com/jeroendesloovere/vcard) and deploy it in lib directory.

Copy etc/config.php.template to etc/config.php and adjust $vcard_dir variable with a directory containing *.vcf files.

In Grandstream GXP1625 administrative interface, access Contacts > Phonebook Management and setup appropriately to download from your server.

## Caveats

Implements no authentication. This can be done in the HTTP layer (e.g. AuthUserFile in Apache).

Only the following phone number types are converted: Home, Work, Mobile (other types are ignored)

If a given contact has more than one phone number of the same type (e.g. two work phones), only the first one is exported.

In a typical setup your www-data can't access the *.vcf files managed by your CardDAV server, as is the case with Radicale. A simple workaround is to setup a cronjob that regularly syncs them into another directory while reseting their permissions. Example for Radicale:

```*/10 *	* * *		rsync -rv --del --chmod=644 --exclude='.Radicale.*' /var/lib/radicale/collections/path_to_your_collection /var/www/whatever/```
