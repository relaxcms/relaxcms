#!/bin/bash 

CDIR=`dirname $0`
CDIR=$(cd $CDIR;pwd)
DDIR=$CDIR/../../../public/docs/admin
mkdir -p $DDIR


#pandoc user.md -t html5 -o user.html --toc --smart --template=pm-template
pandoc user.md -t html5 -o $DDIR/user.html --toc --smart --template=../../../public/themes/man/uikit
pandoc user.md -o $DDIR/user.pdf --toc --smart --template=../../../public/themes/man/pm-template --latex-engine=xelatex -Vmainfont=SimSun

pandoc system.md -t html5 -o $DDIR/system.html --toc --smart --template=../../../public/themes/man/uikit
pandoc system.md -o $DDIR/system.pdf --toc --smart --template=../../../public/themes/man/pm-template --latex-engine=xelatex -Vmainfont=SimSun

#pandoc pandoc.md -t html5 -o pandoc.html --toc --smart --template=pm-template
#copy img
cp -rf $CDIR/img $DDIR/