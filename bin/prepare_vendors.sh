#!/bin/sh

# init
DIR=`php -r "echo realpath(dirname(\\$_SERVER['argv'][0]));"`
cd $DIR/src/
rm -rf vendor
mkdir vendor
TARGET=$DIR/src/vendor
cd $TARGET

if [ ! -d "$DIR/vendor" ]; then
    echo "The master vendor directory does not exist"
    exit
fi

cp -r $DIR/vendor/* .

# Doctrine
cd doctrine && rm -rf UPGRADE* build* bin tests tools lib/vendor/doctrine-common/build* lib/vendor/doctrine-common/tests lib/vendor/doctrine-dbal/bin lib/vendor/doctrine-dbal/tests lib/vendor/doctrine-dbal/tools lib/vendor/doctrine-dbal/build* lib/vendor/doctrine-dbal/UPGRADE*
cd $TARGET

# Doctrine migrations
cd doctrine-migrations && rm -rf tests build*
cd $TARGET

# Doctrine MongoDB
cd doctrine-mongodb && rm -rf tests build* tools
cd $TARGET

# Propel
# git clone git://github.com/fzaninotto/propel.git propel
cd propel && rm -rf contrib docs test WHATS_NEW INSTALL CHANGELOG
cd $TARGET

# Phing
cd phing && rm -rf README bin docs etc pear test
cd $TARGET

# Swiftmailer
cd swiftmailer && rm -rf CHANGES README* build* docs notes test-suite tests create_pear_package.php package*
cd $TARGET

# Symfony
cd symfony && rm -rf README phpunit.xml.dist tests
cd $TARGET

# Twig
cd twig && rm -rf AUTHORS CHANGELOG README.markdown bin doc package.xml.tpl phpunit.xml test
cd $TARGET

# Zend Framework
cd zend && rm -rf INSTALL.txt README* bin demos documentation resources tests tools working; mkdir library/tmp; mv library/Zend/Exception.php library/tmp/; mv library/Zend/Log library/tmp/; rm -rf library/Zend; mv library/tmp library/Zend
cd $TARGET

# cleanup
find . -name .git | xargs rm -rf -
find . -name .gitignore | xargs rm -rf -
find . -name .gitmodules | xargs rm -rf -
find . -name .svn | xargs rm -rf -
cd $DIR
