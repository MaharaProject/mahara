# Mahara Docker

This directory contains tools for using or testing Mahara using Docker.

Docker is a great way to try out Mahara without having the hassle of installing
dependencies yourself.

The instructions have been tested on Ubuntu.

## Docker quick start

Test if you have Docker:

```
  docker run ubuntu echo "Yes, I have Docker."
```

If you have Docker, and it is set up, the last line of the output will have
"Yes, I have Docker.". You can skip the rest of this section.

Note for the installation steps below: If you work for a company that works
with Docker, you may wish to check your internal documentation first, as there
may be some special instructions for you to follow instead.

To install and configure Docker on a recent Ubuntu desktop:

```
  sudo apt install docker.io
  # Add yourself to the Docker group
  sudo usermod -aG docker $USER
  newgrp docker

```

Rerun the Docker test above to confirm.

Note: You shouldn't need to run Docker with sudo. Attempting to run some of the
commands in this README with sudo are likely to cause errors.

## Customising the Docker environment

Note that the following scripts and Docker commands can read a lot of config
variables from the environment.

If there are settings that you always want to make, having them set as
environment variables may make your life easier. For example:

```
  export MAHARA_DOCKER_PORT=8081
```
Will make the `mahara` Docker container publish its http port to http://localhost:8081

Using a tool such as [Direnv](https://direnv.net/) can make this even easier
because you can define the variables persistently.

For example, for both local development and running phpunit or Behat tests, you
can use an environment variables file to house all necessary config settings
instead of putting them into config.php itself. You may not wish to do so for a
production environment though when you have multiple Mahara instances running
on a single server. To link the config.php file to the environment variables
file, run:

```
  cd htdocs
  ln -s config-environment.php config.php
  cd ..
```

Note: Instead of putting values into config-environment.php, you can set up a .envrc
file to contain your custom variables as that will not be pushed to the repository
when you commit changes. It is excluded in .gitignore.

## Building and testing Mahara with Docker

Mahara has a mahara-builder Docker image that can be used to build and test
Mahara. This image can be built and updated with:

```
  # This command should be run periodically (every week or two) to freshen up
  # the image with any security patches from its base image
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

The running of phpunit and Behat tests via Docker is in the beginning stages,
and there are changes that need to be made to get it to run fully. We keep the
instructions in here though.

These targets also need a DB. You can start and stop a test Docker database
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

## Building the Mahara Docker image

The main Mahara Makefile has a `docker-image` target that is used to build the
Mahara Docker image. This will create a Docker image with Apache, PHP, and other
dependencies and Mahara.

```
  make docker-image
```

This build target creates the Docker image from the contents of the ./htdocs
directory. Other build targets like `css` should have already been run to
create those assets.

## Using the Mahara Docker image

The Mahara Docker image could be used to create a real Mahara instance. The
`docker-compose.yaml` file could be customised for this or to see what
environment variables are required.

Note: A separate DB server (container) is also required.

Before you use your Mahara Docker image on a production instance, please test it thoroughly.

## Developer testing

When doing Mahara development it is often useful to run a Mahara server
which is serving the files from your local checkout. We can do this with
Docker by:

```
  # First ensure you have a Mahara image
  make docker-image

  # You will need a database and most likely you will want to keep
  # the same database for ongoing testing. In that case first create
  # the volume to store the DB.
  docker volume create mahara-dev-data
  # Now start the DB
  docker/test-db.sh -d mahara-dev-data start

  # Now start Mahara
  docker/test-mahara.sh start

  # Stop Mahara and the DB
  docker/test-mahara.sh stop
  docker/test-db.sh stop
```


## Test environment

Docker compose is used to control the Mahara Docker environment. A database is required besides a
web server. We use PostgreSQL. Composer starts and stops both of these for us using the
following commands.

```
  # Bootstrap the Docker dependencies first. This only needs to be
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

This Docker compose setup uses external data volumes to persist files. This
is used to save the DB and the Mahara data directory. To set up the required
data volumes run the following commands:

```
  docker volume create mahara-db
  docker volume create mahara-data
```

But this could cause problems if you were to run a Mahara version that is older
than the last one that you have run. For example, these problems would be caused by the DB
appearing to be in an unknown future state from the point of view of the older
Mahara release.

To handle these situations without having to start from scratch every time,
the actual Docker volumes used can be changed using environment variables.
For example, the following commands could be used to run Mahara with a fresh
DB and data directory:

```
  docker volume create mahara-db-fresh
  docker volume create mahara-data-fresh

  export MAHARA_DB_VOLUME=mahara-db-fresh
  export MAHARA_DATA_VOLUME=mahara-data-fresh

  docker-compose -f docker/docker-compose.yaml up &
```
