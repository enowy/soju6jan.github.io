#!/bin/sh
PATH_MAKERSS=/volume1/web/makerss
PATH_GIT=/volume1/soju6jan/homes/soju6jan/git/soju6jan.github.io

cd $PATH_MAKERSS
python makerss_main.py
mv $PATH_MAKERSS/*.xml $PATH_GIT
$PATH_GIT/commit.sh

