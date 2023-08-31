#!/bin/bash 

CDIR=`dirname $0`
CDIR=$(cd $CDIR;pwd)
DDIR=$CDIR/../public/docs/devel
MANDIR=$CDIR/../public/themes/man
mkdir -p $DDIR


#pandoc user.md -t html5 -o user.html --toc --smart --template=pm-template
pandoc readme.md -t html5 -o $DDIR/readme.html --toc --smart --template=$MANDIR/uikit
pandoc readme.md -o $DDIR/readme.pdf --toc --smart --template=$MANDIR/pm-template --latex-engine=xelatex -Vmainfont=SimSun

#copy img
cp -rf $CDIR/img $DDIR/