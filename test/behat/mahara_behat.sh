#!/bin/bash

# Quit on error
set -e

# Get action and Mahara dir
ACTION=$1
SCRIPTPATH=`readlink -f "${BASH_SOURCE[0]}"`
MAHARAROOT=`dirname $( dirname $( dirname "$SCRIPTPATH" ))`
SERVER=0

# Wait and check if the selenium server is running in maximum 15 seconds
function is_selenium_running {
    for i in `seq 1 15`; do
        sleep 1
        res=$(curl -o /dev/null --silent --write-out '%{http_code}\n' http://localhost:4444/wd/hub/status)
        if [ $res == "200" ]; then
            return 0;
        fi
    done
    return 1;
}

function cleanup {
    echo "Shutdown Selenium"
    curl -o /dev/null --silent http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer

    if [[ $SERVER ]]
    then
        echo "Shutdown PHP server"
        kill $SERVER
    fi

    if [[ $1 ]]
    then
        exit $1
    else
        exit 255
    fi
}

# Check we are not running as root for some weird reason
if [[ "$USER" = "root" ]]
then
    echo "This script should not be run as root"
    exit 1
fi

cd $MAHARAROOT

# Trap errors so we can cleanup
trap cleanup ERR
trap cleanup INT

if [ "$ACTION" = "action" ]
then

    # Wrap the util.php script

    PERFORM=$2
    php htdocs/testing/frameworks/behat/cli/util.php --$PERFORM

elif [ "$ACTION" = "run" -o "$ACTION" = "runheadless" -o "$ACTION" = "rundebug" -o "$ACTION" = "runfresh" ]
then

    if [ $ACTION = 'runheadless' -o "$ACTION" = "runfresh" ]
    then
        echo "Drop the old test site if exist"
        php htdocs/testing/frameworks/behat/cli/util.php --drop
    fi

    # Initialise the behat environment
    php htdocs/testing/frameworks/behat/cli/init.php

    # Run the Behat tests themselves (after any intial setup)
    TAGS=$2

    if is_selenium_running; then
        echo "Selenium is running"
    else
        echo "Start Selenium..."

        SELENIUM_VERSION_MAJOR=2.45
        SELENIUM_VERSION_MINOR=0

        SELENIUM_FILENAME=selenium-server-standalone-$SELENIUM_VERSION_MAJOR.$SELENIUM_VERSION_MINOR.jar
        SELENIUM_PATH=./test/behat/$SELENIUM_FILENAME

        # If no Selenium installed, download it
        if [ ! -f $SELENIUM_PATH ]; then
            echo "Downloading Selenium..."
            wget -q -O $SELENIUM_PATH http://selenium-release.storage.googleapis.com/$SELENIUM_VERSION_MAJOR/$SELENIUM_FILENAME
            echo "Downloaded"
        fi

        if [[ $ACTION == 'runheadless' ]]
        then
            # we want to run selenium headless on a different display - this allows for that ;)
            echo "Starting Xvfb ..."
            Xvfb :10 -ac > /dev/null 2>&1 & echo "PID [$!]"

            DISPLAY=:10 nohup java -jar $SELENIUM_PATH > /dev/null 2>&1 & echo $!
        else
            java -jar $SELENIUM_PATH &> /dev/null &
        fi

        if is_selenium_running; then
            echo "Selenium started"
        else
            echo "Selenium can't be started"
            exit 1
        fi
    fi

    echo "Start PHP server"
    php --server localhost:8000 --docroot $MAHARAROOT/htdocs &>/dev/null &
    SERVER=$!

    BEHATCONFIGFILE=`php htdocs/testing/frameworks/behat/cli/util.php --config`
    echo "Run Behat..."

    OPTIONS=''
    if [ "$TAGS" ]
    then
        echo "Only run tests with the tag: $TAGS"
        OPTIONS=$OPTIONS" --tags "$TAGS
    else
        echo "Run all tests"
    fi

    echo
    echo "=================================================="
    echo

    if [[ $ACTION == 'rundebug' ]]
    then
        OPTIONS=$OPTIONS" --format=pretty"
    fi

    ./external/vendor/bin/behat --config $BEHATCONFIGFILE $OPTIONS

    echo
    echo "=================================================="
    echo
    echo "Shutdown"
    cleanup 0
else
    # Help text if we got an unexpected (or empty) first param
    echo "Expected something like one of the following:"
    echo
    echo "# Run all tests:"
    echo "mahara_behat run"
    echo ""
    echo "# Run tests with specific tag:"
    echo "mahara_behat run @tagname"
    echo ""
    echo "# Run tests with extra debug output:"
    echo "mahara_behat rundebug"
    echo "mahara_behat rundebug @tagname"
    echo ""
    echo "# Enable test site:"
    echo "mahara_behat action enable"
    echo ""
    echo "# Disable test site:"
    echo "mahara_behat action disable"
    echo ""
    echo "# List other actions you can perform:"
    echo "mahara_behat action help"
    exit 1
fi
