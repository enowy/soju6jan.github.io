#!/bin/sh
PATH_MAKERSS=/volume1/
PATH_GIT=/volume1/

cd $PATH_MAKERSS
python makerss_main.py
mv $PATH_MAKERSS/*.xml $PATH_GIT
$PATH_GIT/commit.sh

