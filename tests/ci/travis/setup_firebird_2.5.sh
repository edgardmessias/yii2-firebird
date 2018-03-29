#!/bin/bash

apt-get install -qq firebird2.5-super firebird2.5-dev
export FIREBIRD_SERVER_CONFIG=/etc/default/firebird$FB
sed /ENABLE_FIREBIRD_SERVER=/s/no/yes/ -i $FIREBIRD_SERVER_CONFIG
cat $FIREBIRD_SERVER_CONFIG | grep ENABLE_FIREBIRD_SERVER

service firebird2.5-super start