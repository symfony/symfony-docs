#!/bin/sh

DIR=`pwd`
VERSION=2_0_PR3
rm -rf /tmp/sandbox
mkdir /tmp/sandbox
cp -r hello /tmp/sandbox/
cp -r src /tmp/sandbox/
cp -r web /tmp/sandbox/
cp -r README /tmp/sandbox/
cp -r LICENSE /tmp/sandbox/
cd /tmp/sandbox
perl -p -i -e "s#/../vendor#/vendor#" src/autoload.php
sudo rm -rf hello/cache/* hello/logs/* .git*
chmod 777 hello/cache hello/logs
cd ..
# avoid the creation of ._* files
export COPY_EXTENDED_ATTRIBUTES_DISABLE=true
export COPYFILE_DISABLE=true
tar zcpf $DIR/sandbox_$VERSION.tgz sandbox
sudo rm -f $DIR/sandbox_$VERSION.zip
zip -rq $DIR/sandbox_$VERSION.zip sandbox
