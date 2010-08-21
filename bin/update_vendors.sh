#!/bin/sh

DIR=`php -r "echo realpath(dirname(\\$_SERVER['argv'][0]));"`
VENDOR=$DIR/vendor

# Symfony
cd $VENDOR/symfony && git pull
cp $VENDOR/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/skeleton/application/php/config/config* $DIR/hello/config/
cp $VENDOR/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/skeleton/application/yml/config/config* $DIR/hello/config/
cp $VENDOR/symfony/src/Symfony/Bundle/FrameworkBundle/Resources/skeleton/application/xml/config/config* $DIR/hello/config/

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
