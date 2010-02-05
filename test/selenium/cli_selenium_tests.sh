#!/bin/bash -ex
# this script accepts two optional parameters
#   nemo - will use firefox-selenium-template and go background
#   3.5  - will use firefox-3.5-selenium-template and stay foreground
# the default (if no parameter) is to use firefox-selenium-template 
# and stay foreground
#
# we don't drop/create the db here.  callers should do that.
# some features may be customised by setting environment variables, e.g.,
# PROXYHOST=192.168.200.200, PROXYPORT=3128 to use a proxy
# MAHARA_URL=http://whatever.com/whatever_else to specify the mahara 
#    instance to test against.
# SUITE=TestSuite.html (an absolute or relative path to the suite.html file
#    to test with.  TestSuite.html is the default.


XMS=64m
XMX=256m

if [ -z $MAHARA_URL ]
then
	MAHARA_URL=http://localmahara.org
fi

if [ -z $SUITE ]
then
	SUITE=./TestSuite.html
fi

# Use this script to start the selenium server. A JDK is required.
# only sun 1.6 JDKs have been tested with this so far.

# for now, we use a single set of configs (assumes *chrome and FF3.0
# or iceweasel equivalent).
  export SINGLEWINDOW="-singleWindow"
  export REUSE="-browserSessionReuse"
  unset PROXYINJECTION

FF35=0

if [ $1 = '3.5' ]
then
  FF35=1
fi

# NOTE: the firefox-3.5* directory keeps changing name as new versions
# are created (3.5.1, 3.5.2).  Create a /usr/lib/firefox-3.5 link that
# points to the real firefox 3.5 directory on your machine.  Ignore this
# if you never use FF3.5. 
# TODO: this will need to change when FF3.5 becomes the default.
if [ $FF35 == 1  ]
then
  export FIREFOX_PROFILE_TEMPLATE_DIR="../firefox-profiles/3.5"
  CHROME="*chrome /usr/lib/firefox-3.5/firefox-3.5"
else
  # we need to use this (and set localhost:4444 as proxy in the template)
  # because the various correct solutions (*chrome+proxyInjection,
  # pifirefox, firefox+proxyInjection) all fail in some ways (e.g., gallery
  # doesn't return, possibly waiting for stats.telecom.co.nz?).  With this,
  # we can then use *chrome with explicit proxy injection.
  export FIREFOX_PROFILE_TEMPLATE_DIR="../firefox-profiles/3.0"
  CHROME="*chrome"
fi

  export SELENIUM_EXTRA=" -ensureCleanSession -trustAllSSLCertificates -forcedBrowserModeRestOfLine $CHROME"

if [ ! -z $PROXYHOST ] 
then
  export HTTP_PROXY="-Dhttp.proxyHost=$PROXYHOST -Dhttp.proxyPort=$PROXYPORT"
else
  export HTTP_PROXY=""
fi

if [ ! -z $FIREFOX_PROFILE_TEMPLATE_DIR ]
then
  FFTMPL=" -firefoxProfileTemplate $FIREFOX_PROFILE_TEMPLATE_DIR "
else
  FFTMPL=""
fi
export FFTMPL

   cmdline="java $HTTP_PROXY -Xms$XMS -Xmx$XMX -jar ./server//selenium-server.jar -trustAllSSLCertificates $SINGLEWINDOW $FFTMPL $PROXYINJECTION $REUSE -htmlSuite *chrome $MAHARA_URL $SUITE ./results.html $SELENIUM_EXTRA"

  	echo $cmdline 
	echo "When the tests are done, run firefox ./results.html to see the test results"
  $cmdline 

exit 0
