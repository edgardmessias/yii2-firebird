#!/bin/bash -e

echo " ... Adding repository"
echo "deb http://ppa.launchpad.net/mapopa/firebird3.0/ubuntu precise main" >> /etc/apt/sources.list.d/firebird3.0.list
echo "deb-src http://ppa.launchpad.net/mapopa/firebird3.0/ubuntu precise main" >> /etc/apt/sources.list.d/firebird3.0.list

echo " ... Importing Key"
apt-key adv --recv-keys --keyserver keyserver.ubuntu.com ea316a2f8d6bd55554c23f680be6d09eef648708

echo " ... Updating repository"
apt-get update -qq

echo " ... Installing Firebird 3.0"
apt-get install -qq firebird3.0-server

echo " ... Starting Firebird 3.0"
service firebird3.0-server start