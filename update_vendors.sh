#!/bin/sh

CURRENT=`pwd`
VENDOR=$CURRENT/vendor

# Symfony
cd $VENDOR/symfony && git pull
cp $VENDOR/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/skeleton/application/php/config/config* $CURRENT/hello/config/
cp $VENDOR/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/skeleton/application/yml/config/config* $CURRENT/hello/config/
cp $VENDOR/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/skeleton/application/xml/config/config* $CURRENT/hello/config/

# Doctrine ORM
cd $VENDOR/doctrine && git pull

# Doctrine DBAL
cd $VENDOR/doctrine-dbal && git pull

# Doctrine common
cd $VENDOR/doctrine-common && git pull

# Doctrine migrations
cd $VENDOR/doctrine-migrations && git pull

# Doctrine MongoDB
cd $VENDOR/doctrine-mongodb && git pull

# Propel
cd $VENDOR/propel && svn up

# Phing
cd $VENDOR/phing && svn up

# Swiftmailer
cd $VENDOR/swiftmailer && git pull

# Twig
cd $VENDOR/twig && git pull

# Zend Framework
cd $VENDOR/zend && git pull
