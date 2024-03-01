#!/bin/bash

CDIR=`dirname $0`
CDIR=$(cd $CDIR;pwd)
WEBDIR=`dirname $CDIR`
LOGFILE=/dev/null

# this shell in : <WEBDIR>/bin/

UNAME=`uname -a`
APIURL=https://www.relaxcms.com/api
#APIURL=http://192.168.0.115/rc9/api

if [ ! -d $WEBDIR/bin ]; then
	echo "no $WEBDIR/bin!";
	exit 1
fi

if [ ! -d $WEBDIR/lib ]; then
	echo "no $WEBDIR/lib!";
	exit 1
fi

if [ ! -f $WEBDIR/lib/version.php ]; then
	echo "no $WEBDIR/lib/version.php!";
	exit 1
fi

if [ ! -f $WEBDIR/bin/showversion.php ]; then
	echo "WARNING:no $WEBDIR/bin/showversion.php!";
	exit 1
fi

#current version
CURRENTVERSION=`/opt/crab/bin/php $WEBDIR/bin/showversion.php`

echo "Current version : $CURRENTVERSION"

#download relaxcms
URL="$APIURL/getAppOneKeyInstallPackage?name=relaxcms&type=0&currentversion=$CURRENTVERSION&uname=$UNAME"
INSTALLFILE=relaxcms.tgz
echo -n "downloading RELAXCMS from $APIURL ..."
if [ -f /usr/bin/curl ];then 
	curl -k -o $INSTALLFILE -sSLO "$URL";
else 
	wget -O $INSTALLFILE "$URL";
fi;
if [ $? -ne 0 ] ; then
		echo "download relaxcms failed! url=$URL"
		exit 1
fi


echo -n "checking ..."
rm -rf $CDIR/tmp
mkdir -p $CDIR/tmp
cd $CDIR/tmp
tar -xzf ../$INSTALLFILE >> $LOGFILE 2>&1
if [ $? -ne 0 ] ; then
		echo "no upgrade version"
		cd $CDIR
		rm -f $INSTALLFILE
		rm -rf tmp
		exit 1
fi

#setup package
if [ -x setup.sh ] ; then
	echo -n "setup ..."
	if [ -f web/bin/upgrade.sh ]; then
		mv -f web/bin/upgrade.sh $WEBDIR/bin/upgrade.sh.new
	fi
	cp -rf web/* $WEBDIR/
	if [ $? -ne 0 ] ; then
			echo "copy FAILED!"
			exit 1
	fi
fi

if [ -x post_update.sh ] ; then
	echo -n "patch ..."
	cd $CDIR
	/opt/crab/bin/php $WEBDIR/bin/webpatch.php $INSTALLFILE
	if [ $? -ne 0 ] ; then
		echo "patch failed!"
		exit 1
	fi
fi

#fixed permision
chown crab.root $WEBDIR -R

#clean
cd $CDIR
rm -f $INSTALLFILE
rm -rf tmp
#fixed upgrade.sh
if [ -f $WEBDIR/bin/upgrade.sh.new ]; then
	mv $WEBDIR/bin/upgrade.sh.new $WEBDIR/bin/upgrade.sh
fi
echo "OK."
