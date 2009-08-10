#!/bin/bash -ex
# this script accepts two optional parameters
#   nemo - will use firefox-selenium-template and go background
#   3.5  - will use firefox-3.5-selenium-template and stay foreground
# the default (if no parameter) is to use firefox-selenium-template 
# and stay foreground

# we clear the mahara database (the tests start with an install and
# assumes the tables aren't there).  the ff assumes that we can sudo
# to postgres with no password, and that the database and user are
# mahara

echo "drop database mahara;create database mahara with encoding='UTF8';grant all on database mahara to mahara;" | sudo -u postgres psql


DEBUG_SELENIUM_SERVER_OUTPUT=/tmp/selenium-server.$USER.$DISPLAY.tmp

BGTRUE=1
BGFALSE=0
BG=$BGFALSE

PORT=" -port 4444 "

if [ $1 = 'hudson' ]
then
  BG=$BGTRUE
fi

if [ $1 = 'tiger-hudson' ]
then
  PORT=" -port 4445 "
  BG=$BGTRUE
fi

XMS=64m
XMX=256m

# Use this script to start the selenium server, if you're
# going to be running selenium tests via JUnit.
# There should be a java (1.5 and 1.6 will work, 1.4 will
# probably work) executable in your path.

# configs for nemo.  we will likely need to 
if [ `hostname` == 'nemo' ]
then
  export PATH=/usr/lib/iceweasel:$PATH

  # used by the selenium-server. If the browser is in proxy injection mode
  # or is explicitly using localhost:4444 as its proxy then the selenium-server
  # browses outward using this proxy.
  #export PROXYHOST=192.168.2.239
  export PROXYHOST=192.168.2.228
  export PROXYPORT=3128
fi

  # for now, we use a single set of configs (since nemo is now able to
  # use chrome
  #export SINGLEWINDOW="-multiWindow"
  export SINGLEWINDOW="-singleWindow"
  export REUSE="-browserSessionReuse"
  unset PROXYINJECTION

FF35=0

if [ $1 = '3.5' ]
then
  FF35=1
fi

if [ `hostname` = 'quimpo' ]
then
 FF35=1
fi

if [ $FF35 == 1  ]
then
  export FIREFOX_PROFILE_TEMPLATE_DIR="firefox-profiles/3.5"
  CHROME="*chrome /usr/lib/firefox-3.5/firefox-3.5"
else
  # we need to use this (and set localhost:4444 as proxy in the template)
  # because the various correct solutions (*chrome+proxyInjection,
  # pifirefox, firefox+proxyInjection) all fail in some ways (e.g., gallery
  # doesn't return, possibly waiting for stats.telecom.co.nz?).  With this,
  # we can then use *chrome with explicit proxy injection.
  export FIREFOX_PROFILE_TEMPLATE_DIR="fixtures-profiles/3.0"
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

if [ ! -z $DEBUG ]
then
   DEBUG=" -debug "
else
   DEBUG=""
fi

# avoid file descriptor leak per http://wiki.hudson-ci.org/display/HUDSON/Spawning+processes+from+build
# from http://blog.apokalyptik.com/2007/10/24/bash-tip-closing-file-descriptors

if [ $BG == $BGTRUE ]
then
   exec 0>&- # close stdin
   exec 1>&- # close stdout 
   exec 2>&- # close stderr
fi

   cmdline="java $HTTP_PROXY -Xms$XMS -Xmx$XMX -jar ./server//selenium-server.jar $PORT -trustAllSSLCertificates $DEBUG $SINGLEWINDOW $FFTMPL $PROXYINJECTION $REUSE -htmlSuite *chrome http://localmahara.org ./TestSuite.html ./results.html $SELENIUM_EXTRA"

if [ $BG == $BGTRUE ]
then
  echo $cmdline > $DEBUG_SELENIUM_SERVER_OUTPUT
  $cmdline >> $DEBUG_SELENIUM_SERVER_OUTPUT 2>&1 &
  pid=$!
  sleep 10
  echo $pid > selenium-server.$DISPLAY.pid
else
  echo $cmdline 
  $cmdline 
fi

exit 0
