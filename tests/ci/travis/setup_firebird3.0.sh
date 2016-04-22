#!/bin/bash -e

echo " ... Downloading source"
wget https://sourceforge.net/projects/firebird/files/firebird/3.0-Release/Firebird-3.0.0.32483-0.tar.bz2

echo " ... Extracting source"
tar xjvf Firebird-3.0.0.32483-0.tar.bz2

echo " ... Preparing source"
cd Firebird-3.0.0.32483-0
apt-get install -qq expect docbook docbook-to-man libatomic-ops-dev libbsd-dev libedit-dev libsp1c2 sgml-data sp libtommath-dev
./configure

echo " ... Compiling source"
make -j `nproc`

echo " ... Installing"
export DEBIAN_FRONTEND=readline
expect ${TRAVIS_BUILD_DIR}/tests/ci/travis/dpkg_firebird3.0.exp
export DEBIAN_FRONTEND=dialog

ln -s /usr/local/firebird/bin/isql /usr/local/firebird/bin/isql-fb

echo " ... Starting Firebird 3.0"
service firebird start