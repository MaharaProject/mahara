#!/bin/sh

# Test the different web service protocols.
#
# @author     Piers Harding
# @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
# @package    web service
# @copyright  Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
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

