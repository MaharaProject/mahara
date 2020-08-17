#!/bin/bash
# Runs make within the mahara-builder docker container.
#
# Usage ./docker/make.sh [build-target...]
#
MAHARA_TEST_NETWORK=mahara-test
MAHARA_BASE=`realpath $(dirname $0)/..`

# This directory will be volume mounted into the docker contain
# and used as the behat data root. Test reports/artifacts can
# be found here after a behat test run.
BEHAT_DATA_ROOT=${BEHAT_DATA_ROOT:-${MAHARA_BASE}/behat-root}
if [ ! -d "${BEHAT_DATA_ROOT}" ] ; then
  mkdir -p ${BEHAT_DATA_ROOT}
fi

# Ensure the ${MAHARA_TEST_NETWORK} exists. This is required if tests are being
# run as they need access to the test DB.
#First need to ensure the network exists
docker network inspect ${MAHARA_TEST_NETWORK} > /dev/null 2>&1
if [ $? != 0 ] ; then
  echo "Creating docker network"
  docker network create ${MAHARA_TEST_NETWORK}
fi

# Run make in the builder container
# - interactive to allow running behat tests with breakpoints
# - X11 forwarding to run behat tests in browser (not headless)
# - .ssh/known_hosts is mounted to Makefile does not give warning for unknown
#   hosts.
docker run --rm \
  --privileged \
  --interactive --tty \
  --security-opt seccomp=${MAHARA_BASE}/docker/chromium.json \
  --volume $HOME/.ssh/known_hosts:/home/builder/.ssh/known_hosts \
  --volume ${MAHARA_BASE}:/opt/mahara \
  --volume ${BEHAT_DATA_ROOT}:/tmp/behat \
  --volume /tmp/.X11-unix:/tmp/.X11-unix \
  --volume $HOME/.Xauthority:/home/builder/.Xauthority \
  --network ${MAHARA_TEST_NETWORK} \
  --env DISPLAY=$DISPLAY \
  --env MAHARA_BEHAT_DATA_ROOT=/tmp/behat \
  --env MAHARA_DB_HOST=mahara-db \
  mahara-builder "$@"
