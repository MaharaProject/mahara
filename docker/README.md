# Mahara docker

This directory contains tools for using/testing Mahara using docker.

Docker is great way to try out Mahara without having the hassle of installing
dependencies yourself.

## Customising the test environment

Note that the following scripts and docker commands can read alot of config
variables from the environment.

If there are settings that you always want to make having them set as
environment variables may make your life easier. For exampe:

```
  export MAHARA_DOCKER_PORT=8081
```
Will make the `mahara` docker container publish its http port to http://localhost:8081

Using a tool such as [Direnv](https://direnv.net/) can make this even easier.

## Building/Testing Mahara with docker

Mahara has a mahara-builder docker image that can be used to build and test
Mahara. This image can be built/updated with:

```
  # This command should be run periodically (every week or two) to freshen up
  # the image with any security patches from it's base image
  make docker-builder
```

This image contains the dependencies required for building and testing
Mahara. To use this image to execute `make` targets run:

```
  ./docker/make.sh <target>
  e.g
  ./docker/make.sh css
  ./docker/make.sh phpunit
  ./docker/make.sh behat
  # To run only some test features
  ./docker/make.sh -e BEHAT_TESTS=change_account_settings.feature behat
  # To run some tests with browser head
  ./docker/make.sh -e BEHAT_MODE=rundebug -e BEHAT_TESTS=change_account_settings.feature behat
```

Note that running phpunit/behat tests require a config.php with good settings
for this. Before you run those targets link this file:

```
  cd htdocs
  ln -s config-test.php config.php
  cd ..
```

These test targets also need a DB. You can start/stop a test docker database
with these commands:

```
  docker/test-db.sh start

  # Run you tests while the DB is running

  docker/test-db.sh stop
```

To access the database with psql run:

```
  docker/test-db.sh psql

  # To access the DB used in the docker-compose setup later on you need
  # to change the docker network being used.
  docker/test-db.sh -n docker_default psql
```

## Building the Mahara docker image

The main Mahara Makefile has a `docker-image` target that is used to build the
Mahara docker image. This will create a docker image with apache, php and other
dependencies and Mahara.

```
  make docker-image
```

This build target creates the docker image from the contents of the ./htdocs
directory. Other build targets like `css` should have already been run to
create those assets.

## Using Mahara docker image

The Mahara docker image could be used to create a real Mahara instance. The
`docker-compose.yaml` file could be customized for this or to see what
environment variables are required.

Note that an separate DB server (container) is also required.

## Developer testing

When doing Mahara development it is often useful to run a Mahara server
which is serving the files from your local checkout. We can do this with
docker by:

```
  # First ensure you have a mahara image
  make docker-image

  # You will need a database and most likely you will want to keep
  # the same database for ongoing testing. In that case first create
  # the volume to store the DB.
  docker volume create mahara-dev-data
  # Now start the DB
  docker/test-db.sh -d mahara-dev-data start

  # Now start mahara
  docker/test-mahara.sh start

  # Stop
  docker/test-mahara.sh stop
  docker/test-db.sh stop
```


## Test environment

Docker compose is used to control the Mahara docker environment. As well as
the Mahara web server a database (Postgres) server is also required. Composer
starts/stops both of these for us using the following commands.

```
  # Bootstrap the docker dependencies first. This only needs to be
  # done once.
  docker network create mahara
  docker volume create mahara-db
  docker volume create mahara-data
  docker volume create mahara-elastic

  # Now start the Mahara environment
  docker-compose -f docker/docker-compose.yaml up &

  # Stop it once you are finished
  docker-compose -f docker/docker-compose.yaml down
```

### External data volumes

This docker compose setup uses external data volumes to persist files. This
is used to save the DB and the Mahara data directory. To setup the required
data volumes run the following commands:

```
  docker volume create mahara-db
  docker volume create mahara-data
```

But this could cause problems if you were to run a Mahara version that is older
than the last one that you have run. (These problems would be caused by the DB
appearing to be in an unknown future state from the point of view of the older
Mahara release)

To handle these situations without having to start from scratch every time
the actual docker volumes used can be changed using environment variables.
For example the following commands could be used to run Mahara with a fresh
DB and data directory:

```
  docker volume create mahara-db-fresh
  docker volume create mahara-data-fresh

  export MAHARA_DB_VOLUME=mahara-db-fresh
  export MAHARA_DATA_VOLUME=mahara-data-fresh

  docker-compose -f docker/docker-compose.yaml up &
```
