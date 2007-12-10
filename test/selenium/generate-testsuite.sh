#!/bin/sh
# Generates the testsuite from the files in this directory
#
# Author: Nigel McNie
# Copyright (C) 2007 Catalyst IT Ltd.
set -e

FILENAME="TestSuite.html"

# Helper function - inserts a test suite into the helper file
function insert_testsuite {
    testname=$(echo $1 | cut -c 3-)
    cat $1/index.html | grep '<td><a href' | sed "s/href=\"/href=\"$testname\//g" >> $FILENAME
}

# Output header
cat > $FILENAME <<EOF
<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
        <title>Mahara Full Test Suite</title>
    </head>
    <body>
        <table cellpadding="1" cellspacing="1" border="1">
            <tbody>
                <tr><td><strong>Mahara Full Test Suite</strong></td></tr>
EOF

# First part of the full test suite is to install the whole system
insert_testsuite ./basic-install

# Insert all test suites
for TS in $(find . -mindepth 1 -type d -not -name 'shared' -not -name '*install*'); do
    insert_testsuite $TS
done

# Output footer
cat >> $FILENAME <<EOF
            </tbody>
        </table>
    </body>
</html>
EOF
