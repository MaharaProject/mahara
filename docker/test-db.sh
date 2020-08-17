#!/bin/bash
# Starts or stops a postgres DB server that contains an empty mahara
# DB. Used when running phpunit or behat tests in the dockerized mahara-builder
# container.
#
# Usage: ./docker/test-db.sh (start | stop)

CONTAINER_NAME=mahara-db
NETWORK=${DOCKER_NETWORK:-mahara-test}
DATA_VOLUME=${MAHARA_DB_VOLUME}

MAHARA_PASSWORD=mahara

while getopts "n:d:" flag; do
  case "$flag" in
    n) NETWORK=$OPTARG;;
    d) DATA_VOLUME=$OPTARG;;
  esac
done

if [ -n ${DATA_VOLUME} ] ; then
  DATA_VOLUME_ARG="--volume ${DATA_VOLUME}:/var/lib/postgresql/data"
fi

ACTION=${@:$OPTIND:1}

# Ensure the ${MAHARA_TEST_NETWORK} exists.
docker network inspect ${NETWORK} > /dev/null 2>&1
if [ $? != 0 ] ; then
  echo "Creating docker network"
  docker network create ${NETWORK}
fi

if [[ "${ACTION}" == "stop" ]] ; then
  docker container stop ${CONTAINER_NAME}
elif [[ "${ACTION}" == "start" ]] ; then
  docker run --rm --name ${CONTAINER_NAME} ${DATA_VOLUME_ARG} \
    --env POSTGRES_DB=mahara \
    --env POSTGRES_PASSWORD=${MAHARA_PASSWORD} \
    --env POSTGRES_USER=mahara \
    --network ${NETWORK} \
    --detach postgres:9.5
elif [[ "${ACTION}" == "psql" ]] ; then
  docker run --rm --interactive --tty \
    --network ${NETWORK} \
    --env PGPASSWORD=${MAHARA_PASSWORD} \
    postgres:9.5 psql -h ${CONTAINER_NAME} -U mahara
else
  echo "\
Controls a postgres server for Mahara testing

If db-volume is specified or env variable MAHARA_DB_VOLUME is set
then the specified docker volume will be mounted allowing for the
DB to be persisted. If not specified a fresh DB will be created and
it will be discarded when stopped.

Usage $0 [-d <db-volume>] [-n <docker-network>] start|stop|psql
"
fi
