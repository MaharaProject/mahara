#!/bin/sh
#
# Generates the testsuite from the files in this directory
#
# Author: Nigel McNie
# Copyright (C) 2007 Catalyst IT Ltd.
#

for F in $(find . -name '*.html'); do
    echo $F
done
