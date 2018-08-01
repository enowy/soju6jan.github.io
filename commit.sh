#!/bin/bash
export LANG=en_US.utf8
export HOME=/var/services/homes/soju6jan
NowDate=$(date +%Y%m%d)-$(date +%H%M) 
echo $NowDate
cd /volume1/homes/soju6jan/git/soju6jan.github.io
git add avnori_ymav.xml 
git add avnori_nmav.xml
git add klive.xml
git add xmltv.xml
git commit -m $NowDate
git push
