#!/bin/bash
REV=$1
if [ -z $REV ]
then
	echo "must supply revision as argument ./package-tar.sh 0.1.2"
	exit
fi
PACKAGE_NAME=tanc-web-app
PACKAGE_ROOT=build/tar/$PACKAGE_NAME

rm -Rf $PACKAGE_ROOT/
mkdir -p $PACKAGE_ROOT/opt/tanc/www/
cp -r local $PACKAGE_ROOT/opt/tanc/www/
cp -r src $PACKAGE_ROOT/opt/tanc/www/
cp -r templates $PACKAGE_ROOT/opt/tanc/www/
cp -r etc $PACKAGE_ROOT/opt/tanc/www/
cp  index.php $PACKAGE_ROOT/opt/tanc/www/
cp  composer.json $PACKAGE_ROOT/opt/tanc/www/
cp  composer.lock $PACKAGE_ROOT/opt/tanc/www/

mkdir $PACKAGE_ROOT/opt/tanc/www/var/
mkdir -p $PACKAGE_ROOT/opt/tanc/www/var/{db,cache,sess,templates}

for x in `find var/ -type d`
do
	mkdir -p $PACKAGE_ROOT/opt/tanc/www/$x
done

cd $PACKAGE_ROOT
tar --strip-components=1 -czvf ../$PACKAGE_NAME-$REV.tar.gz opt
