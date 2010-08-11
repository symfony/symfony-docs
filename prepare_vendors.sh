#!/bin/sh

# init
cd src/
rm -rf vendor
mkdir vendor
cd vendor

cp -r ../../vendor/* .

# Doctrine
cd doctrine
rm -rf UPGRADE* build* bin tests tools lib/vendor/doctrine-common/build* lib/vendor/doctrine-common/tests lib/vendor/doctrine-dbal/bin lib/vendor/doctrine-dbal/tests lib/vendor/doctrine-dbal/tools lib/vendor/doctrine-dbal/build* lib/vendor/doctrine-dbal/UPGRADE*
cd ..

# Doctrine migrations
cd doctrine-migrations
rm -rf tests build*
cd ..

# Doctrine MongoDB
cd doctrine-mongodb
rm -rf tests build* tools
cd ..

# Propel
# git clone git://github.com/fzaninotto/propel.git propel
cd propel
rm -rf contrib docs test WHATS_NEW INSTALL CHANGELOG
cd ..

# Phing
cd phing
rm -rf README bin docs etc pear test
cd ..

# Swiftmailer
cd swiftmailer
rm -rf CHANGES README* build* docs notes test-suite tests create_pear_package.php package*
cd ..

# Symfony
cd symfony
rm -rf README phpunit.xml.dist tests
cd ..

# Twig
cd twig
rm -rf AUTHORS CHANGELOG README.markdown bin doc package.xml.tpl phpunit.xml test
cd ..

# Zend Framework
cd zend
rm -rf INSTALL.txt README* bin demos documentation resources tests tools working
mkdir library/tmp
mv library/Zend/Exception.php library/tmp/
mv library/Zend/Log library/tmp/
rm -rf library/Zend
mv library/tmp library/Zend
cd ..

# cleanup
find . -name .git | xargs rm -rf -
find . -name .gitignore | xargs rm -rf -
find . -name .gitmodules | xargs rm -rf -
find . -name .svn | xargs rm -rf -
cd ../..
