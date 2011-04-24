#!/bin/sh
git update
# git submodule update will update only to version, commited in kanon
cd yuki
git update
cd ..
echo "OK"
