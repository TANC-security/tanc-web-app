#!/bin/bash

if [ -z $BASH ]
then
	echo "must run with bash and not sh."
	exit 1
fi
REV=$1
if [ -z $REV ]
then
	echo "must supply revision as argument ./package-tar.sh 0.1.2"
	exit 1
fi
PACKAGE_NAME=tanc-web-app
PACKAGE_ROOT=build/tar/$PACKAGE_NAME


rm -Rf $PACKAGE_ROOT/
mkdir -p $PACKAGE_ROOT/opt/tanc/www/
cp -r local $PACKAGE_ROOT/opt/tanc/www/
cp -r src $PACKAGE_ROOT/opt/tanc/www/
cp -r bin $PACKAGE_ROOT/opt/tanc/www/
cp -r templates $PACKAGE_ROOT/opt/tanc/www/
cp -r etc $PACKAGE_ROOT/opt/tanc/www/
cp  index.php $PACKAGE_ROOT/opt/tanc/www/
cp  composer.json $PACKAGE_ROOT/opt/tanc/www/
cp  composer.lock $PACKAGE_ROOT/opt/tanc/www/

#clean unneeded files
find $PACKAGE_ROOT/opt/tanc/www/ -name 'README*' -type f -exec rm {} +
find $PACKAGE_ROOT/opt/tanc/www/ -name '*xml.dist' -type f -exec rm {} +
find $PACKAGE_ROOT/opt/tanc/www/ -name '.git' -type d -exec rm -Rf {} \;
find $PACKAGE_ROOT/opt/tanc/www/ -name 'tests' -type d -exec rm -Rf {} +
find $PACKAGE_ROOT/opt/tanc/www/ -name 'examples' -type d -exec rm -Rf {} +
find $PACKAGE_ROOT/opt/tanc/www/ -name 'doc' -type d -exec rm -Rf {} +

mkdir $PACKAGE_ROOT/opt/tanc/www/var/
mkdir -p $PACKAGE_ROOT/opt/tanc/www/var/{db,cache,sess,templates,demoCA}

for x in `find var/ -type d`
do
	mkdir -p $PACKAGE_ROOT/opt/tanc/www/$x
done

cd $PACKAGE_ROOT
tar --strip-components=1 -czvf ../$PACKAGE_NAME-$REV.tar.gz opt
