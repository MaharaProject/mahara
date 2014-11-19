#!/bin/sh

# Test the different web service protocols.
#
# @package    mahara
# @subpackage auth-webservice
# @author     Catalyst IT Ltd
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
# @copyright  For copyright information on Mahara, please see the README file distributed with this software.
#

# Example of running ALL the phpunit tests

# protect against execution
if [ -n "$GATEWAY_INTERFACE" ]; then
  echo "Content-type: text/html"
  echo ""
  echo "<html><head><title>ERROR</title></head><body><h1>ERROR</h1></body></html>"
  exit 0
fi

sudo -u www-data phpunit "RunAllTests"

