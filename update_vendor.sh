#!/bin/sh

# init
cd src/
rm -rf vendor
mkdir vendor
cd vendor

# Doctrine
git clone git://github.com/doctrine/doctrine2.git doctrine
cd doctrine
git submodule init
git submodule update
rm -rf UPGRADE* build* bin tests tools lib/vendor/doctrine-common/build* lib/vendor/doctrine-common/tests lib/vendor/doctrine-dbal/bin lib/vendor/doctrine-dbal/tests lib/vendor/doctrine-dbal/tools lib/vendor/doctrine-dbal/build* lib/vendor/doctrine-dbal/UPGRADE*
cd ..

# Doctrine migrations
git clone git://github.com/doctrine/migrations.git doctrine-migrations
cd doctrine-migrations
rm -rf tests build*
cd ..

# Propel
# git clone git://github.com/fzaninotto/propel.git propel
svn co http://svn.propelorm.org/branches/1.5/ propel
cd propel
rm -rf contrib docs test WHATS_NEW INSTALL CHANGELOG
cd ..

# Swiftmailer
git clone git://github.com/swiftmailer/swiftmailer.git swiftmailer
cd swiftmailer
git checkout -b 4.1 origin/4.1
rm -rf CHANGES README* build* docs notes test-suite tests create_pear_package.php package*
cd ..

# Symfony
git clone git://github.com/symfony/symfony.git symfony
cd symfony
rm -rf README phpunit.xml.dist tests
cd ..

# Twig
git clone git://github.com/fabpot/Twig.git twig
cd twig
rm -rf AUTHORS CHANGELOG README.markdown bin doc package.xml.tpl phpunit.xml test
cd ..

# Zend Framework
git clone git://github.com/zendframework/zf2.git zend
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
