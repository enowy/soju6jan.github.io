#!/bin/bash
export LANG=en_US.utf8
export HOME=/var/services/homes/soju6jan
NowDate=$(date +%Y%m%d)-$(date +%H%M) 
cd /volume1/video/git/soju6jan.github.io
git add *
git commit -m $NowDate
git push
