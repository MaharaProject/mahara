#!/bin/sh

# Test the different web service protocols.
#
# @package    mahara
# @subpackage auth-webservice
# @author     Catalyst IT Ltd
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
# @copyright  For copyright information on Mahara, please see the README file distributed with this software.
#

# Example of running the example PHP clients for the
# different core API services: user, group, institution

# protect against execution
if [ -n "$GATEWAY_INTERFACE" ]; then
  echo "Content-type: text/html"
  echo ""
  echo "<html><head><title>ERROR</title></head><body><h1>ERROR</h1></body></html>"
  exit 0
fi

EXAMPLES='user group institution'
j=0
echo "Select one of the following examples to run:"
for i in $EXAMPLES
do
    echo "$j. $i"
    j=`expr $j + 1`
done
j=`expr $j - 1`
echo "Enter your choice (0..$j or x for exit):"
read opt
if [ "$opt" = "x" ]; then
    echo "aborting"
    exit 1
fi
j=0
for i in $EXAMPLES
do
    if [ "$j" = "$opt" ]; then
        echo "running: $i"
        php example_${i}_api.php --username=blah3 --password=blahblah --url=http://mahara.local.net/maharadev/webservice/soap/server.php
        exit 0
    fi
    j=`expr $j + 1`
done
echo "invalid choice selected"
exit
