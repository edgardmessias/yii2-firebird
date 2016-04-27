#!/bin/bash

apt-get install -qq firebird2.1-super firebird2.1-dev expect
export DEBIAN_FRONTEND=readline
expect ${TRAVIS_BUILD_DIR}/tests/ci/travis/dpkg_firebird2.1.exp
export DEBIAN_FRONTEND=dialog

service firebird2.1-super start