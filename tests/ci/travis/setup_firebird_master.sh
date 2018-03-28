#!/bin/bash -e

echo " ... Downloading source"
git clone --depth=1 -b master --single-branch https://github.com/FirebirdSQL/firebird.git

echo " ... Preparing source"
cd firebird
apt-get install -qq expect docbook docbook-to-man libatomic-ops-dev libbsd-dev libedit-dev libsp1c2 sgml-data sp libtommath-dev g++-4.8 libtomcrypt-dev

export CXX="g++-4.8"

./autogen.sh

echo " ... Compiling source"
make -j `nproc`

echo " ... Installing"
export DEBIAN_FRONTEND=readline
expect ${TRAVIS_BUILD_DIR}/tests/ci/travis/dpkg_firebird_master.exp
export DEBIAN_FRONTEND=dialog

ln -s /usr/local/firebird/bin/isql /usr/local/firebird/bin/isql-fb

echo " ... Starting Firebird Master"
service firebird start