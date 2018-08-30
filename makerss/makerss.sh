#!/bin/sh
PATH_MAKERSS=/volume1/web/makerss
PATH_GIT=/volume1/soju6jan/homes/soju6jan/git/soju6jan.github.io
cd $PATH_GIT
python makerss_main.py
mv *.xml $PATH_MAKERSS/rss/
$PATH_GIT/commit.sh

