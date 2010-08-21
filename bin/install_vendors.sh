#!/bin/sh

# initialization
DIR=`php -r "echo realpath(dirname(\\$_SERVER['argv'][0]));"`

if [ -d "$DIR/vendor" ]; then
  rm -rf $DIR/vendor/*
else
  mkdir $DIR/vendor
fi

cd $DIR/vendor

# Symfony
git clone git://github.com/symfony/symfony.git symfony

# Doctrine ORM
git clone git://github.com/doctrine/doctrine2.git doctrine

# Doctrine DBAL
git clone git://github.com/doctrine/dbal.git doctrine-dbal

# Doctrine Common
git clone git://github.com/doctrine/common.git doctrine-common

# Doctrine migrations
git clone git://github.com/doctrine/migrations.git doctrine-migrations

# Doctrine MongoDB
git clone git://github.com/doctrine/mongodb-odm.git doctrine-mongodb

# Propel
svn co http://svn.propelorm.org/branches/1.5/ propel

# Phing
svn co http://svn.phing.info/tags/2.3.3 phing

# Swiftmailer
git clone git://github.com/swiftmailer/swiftmailer.git swiftmailer
git checkout -b 4.1 origin/4.1

# Twig
git clone git://github.com/fabpot/Twig.git twig

# Zend Framework
git clone git://github.com/zendframework/zf2.git zend
