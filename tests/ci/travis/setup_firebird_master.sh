#!/bin/bash

apt-get install -qq expect curl psmisc libtommath0 libicu52 libtomcrypt0

FB_FOLDER=$FB

if [ "$FB_FOLDER" == "master" ]; then
    FB_FOLDER="trunk"
fi


FIREBIRD_URL="http://web.firebirdsql.org/download/snapshot_builds/linux/fb${FB_FOLDER}/"
FIREBIRD_PKG=$(curl $FIREBIRD_URL | grep -o -E "Firebird-(\w|\.|-)+\.amd64\.tar\.gz" | head -1)

cd /tmp

echo " ... Downloading source"
curl "${FIREBIRD_URL}${FIREBIRD_PKG}" -o firebird.tar.gz

echo " ... Preparing source"
mkdir firebird_install
tar -zxvf firebird.tar.gz -C firebird_install --strip-components 1
cd firebird_install

echo " ... Installing"
export DEBIAN_FRONTEND=readline
expect ${TRAVIS_BUILD_DIR}/tests/ci/travis/dpkg_firebird_${FB}.exp
export DEBIAN_FRONTEND=dialog

echo " ... Linking isql-fb"
ln -s /opt/firebird/bin/isql /opt/firebird/bin/isql-fb

echo " ... Starting Firebird ${FB}"
service firebird start
