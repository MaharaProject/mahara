#!/bin/sh
#
# Downloads and sets up selenium core for running the Mahara selenium tests
#
# Author: Nigel McNie <nigel@catalyst.net.nz>
# Copyright: (C) 2007 Catalyst IT Ltd.
set -e

pushd $(dirname $0)

# Download selenium if we don't have it
if [ ! -r selenium-core-0.8.3.zip ]; then
    wget http://release.openqa.org/selenium-core/0.8.3/selenium-core-0.8.3.zip
fi

# Bail out if we already ran the script
if [ -d ../../htdocs/selenium-core ]; then
    echo "You seem to have already extracted selenium-core to ../../htdocs/selenium-core."
    echo "Remove it if you want to re-install"
    popd
    exit
fi


# Make a temporary directory for extracting selenium to
if [ -d selenium-extract-temp ]; then
    rm -rf selenium-extract-temp
fi
mkdir selenium-extract-temp

unzip -q -d selenium-extract-temp selenium-core-0.8.3.zip
cp -R selenium-extract-temp/core ../../htdocs/selenium-core

# Symlink our tests to where selenium core likes them to be
ln -s ../test/selenium ../../htdocs/tests

# Remove the temporary extract directory
rm -r selenium-extract-temp

popd
