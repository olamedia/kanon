#!/bin/sh
git pull
# git submodule update will update only to version, commited in kanon
cd yuki
git pull
cd ..
echo "OK"
