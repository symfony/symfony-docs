#!/bin/bash

function sparse_checkout {
    mkdir sparse_checkout
    cd sparse_checkout
    git init
    git config core.sparsecheckout true
    git remote add -f origin http://github.com/$1/$2
    echo Resources/doc > .git/info/sparse-checkout
    git checkout master
    rm -rf ../bundles/$2
    mv Resources/doc ../bundles/$2
    cd ..
    rm -rf sparse_checkout
}

sparse_checkout sensiolabs SensioFrameworkExtraBundle
sparse_checkout sensiolabs SensioGeneratorBundle
sparse_checkout doctrine DoctrineFixturesBundle
sparse_checkout doctrine DoctrineMigrationsBundle
sparse_checkout doctrine DoctrineMongoDBBundle
rm -rf cmf
git clone http://github.com/symfony-cmf/symfony-cmf-docs cmf

