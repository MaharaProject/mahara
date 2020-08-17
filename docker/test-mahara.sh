#!/bin/bash
# Starts or stops a mahara container with the local htdocs mounted in to
# allow for dynamic updates. That is once started you can:
# - view page (in browser)
# - edit the page source in host machine
# - reload page to see your changes
#
# This script will run the latest `mahara` image known locally. If you
# don't have one yet run `make docker-image` to build it.
#
# Usage: ./docker/test-mahara.sh (start | stop)

MAHARA_BASE=`realpath $(dirname $0)/..`

CONTAINER_NAME=mahara
NETWORK=${DOCKER_NETWORK:-mahara-test}

# The port that the container will publish
MAHARA_DOCKER_PORT=${MAHARA_DOCKER_PORT:-80}

while getopts "n:" flag; do
  case "$flag" in
    n) NETWORK=$OPTARG;;
  esac
done

ACTION=${@:$OPTIND:1}

# Ensure the ${MAHARA_TEST_NETWORK} exists.
docker network inspect ${NETWORK} > /dev/null 2>&1
if [ $? != 0 ] ; then
  echo "Creating docker network"
  docker network create ${NETWORK}
fi

if [[ "${ACTION}" == "stop" ]] ; then
  docker container stop ${CONTAINER_NAME}
elif [[ "$1" == "start" ]] ; then
  docker run --rm --name ${CONTAINER_NAME} \
    --volume ${MAHARA_BASE}/htdocs:/mahara/htdocs \
    --volume ${MAHARA_DATA_VOLUME:-mahara-data}:/mahara/data \
    --env-file ${MAHARA_BASE}/.env \
    --publish "${MAHARA_DOCKER_PORT}:80" \
    --network ${NETWORK} \
    --detach mahara
else
  echo "Usage $0 [-n <docker-network>] start|stop"
fi
