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
    --env MAHARA_WWW_SERVERNAME \
    --env MAHARA_WWW_SERVEADMIN \
    --env MAHARA_DB_TYPE \
    --env MAHARA_DB_HOST \
    --env MAHARA_DB_USER \
    --env MAHARA_DB_NAME \
    --env MAHARA_DB_PASSWD \
    --env MAHARA_DATA_ROOT \
    --env MAHARA_WWW_ROOT \
    --env MAHARA_SEND_EMAIL \
    --env MAHARA_SEND_ALL_EMAIL_TO \
    --env MAHARA_PRODUCTION_MODE \
    --env MAHARA_PERF_TO_FOOT \
    --env MAHARA_SKINS \
    --env MAHARA_ISOLOATED_INSTITUTIONS \
    --env MAHARA_DB_PREFIX \
    --env MAHARA_SITE_THEME_PREFS \
    --env MAHARA_CLEAN_URLS \
    --env MAHARA_PUBLIC_SEARCH_ALLOWED \
    --env MAHARA_PROBATION_ENABLED \
    --env MAHARA_SHOW_LOGIN_INSIDE_BLOCK \
    --env MAHARA_EXTERNAL_LOGIN \
    --env MAHARA_URL_SECRET \
    --env MAHARA_PASSWORD_SALT_MAIN \
    --env MAHARA_PASSWORD_SALT_ALT1 \
    --env MAHARA_SESSION_HANDLER \
    --env MAHARA_REDIS_SERVER \
    --env MAHARA_REDIS_SENTINEL_SERVERS \
    --env MAHARA_REDIS_MASTER_GROUP \
    --env MAHARA_REDIS_PREFIX \
    --env MAHARA_ELASTICSEARCH_HOST \
    --env MAHARA_ELASTICSEARCH_PORT \
    --env MAHARA_ELASTICSEARCH_SCHEME \
    --env MAHARA_ELASTICSEARCH_USERNAME \
    --env MAHARA_ELASTICSEARCH_PASSWD \
    --env MAHARA_ELASTICSEARCH_INDEXING_USERNAME \
    --env MAHARA_ELASTICSEARCH_INDEXING_PASSWD \
    --env MAHARA_ELASTICSEARCH_INDEX_NAME \
    --env MAHARA_ELASTICSEARCH_BYPASS_INDEX_NAME \
    --env MAHARA_ELASTICSEARCH_ANALYZER \
    --env MAHARA_ELASTICSEARCH_TYPES \
    --env MAHARA_ELASTICSEARCH_IGNORE_SSL \
    --env MAHARA_ELASTICSEARCH_REQUEST_LIMIT \
    --env MAHARA_ELASTICSEARCH_REDO_LIMIT \
    --env-file ${MAHARA_BASE}/.env \
    --publish "${MAHARA_DOCKER_PORT}:80" \
    --network ${NETWORK} \
    --detach mahara
else
  echo "Usage $0 [-n <docker-network>] start|stop"
fi
