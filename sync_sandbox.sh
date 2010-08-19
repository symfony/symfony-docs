#!/bin/sh

VENDOR=`pwd`/vendor

# Symfony
cp $VENDOR/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/skeleton/application/php/config/config* hello/config/
cp $VENDOR/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/skeleton/application/yml/config/config* hello/config/
cp $VENDOR/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/skeleton/application/xml/config/config* hello/config/
