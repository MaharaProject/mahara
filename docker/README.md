# Mahara Docker

This directory contains tools for running Mahara in Docker.

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

## Preliminary setup of the Docker environment

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

**For development purposes, we recommend that you will set up a .envrc file. Therefore, the
following instructions will assume that.**

### Get all necessary files for running Mahara within Docker

Mahara has a mahara-builder Docker image that can be used to build and test
Mahara. This image can be built and updated with:

```
  # This command should be run periodically (every week or two) to freshen up
  # the image with any security patches from its base image
  make docker-builder
  # Ensure gulp is available then run...
  make css
```

This image contains the dependencies required for building and testing
Mahara. To use this image to execute `make` targets run:

```
  ./docker/make.sh <target>
  e.g
  ./docker/make.sh css
```

### Additional commands for running automated tests

The following is not necessary for a standard developer testing setup.

The running of phpunit and Behat tests via Docker is in the beginning stages, and there are
changes that need to be made to get them to run fully.

```
  ./docker/make.sh phpunit
  ./docker/make.sh behat
  # To run only some test features
  ./docker/make.sh -e BEHAT_TESTS=change_account_settings.feature behat
  # To run some tests with browser head
  ./docker/make.sh -e BEHAT_MODE=rundebug -e BEHAT_TESTS=change_account_settings.feature behat
```

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

## Using Docker for development purposes

We are going to use Docker compose to start a developer environment.

The following command will create the neccesary networks, volumes, and images and then start them.

The compose file `docker/docker-compose.dev.yaml` will also mount the local htdocs directory inside
the container so that you can edit the code outside of the container, i.e. in your regular directory,
and the changes will appear in the container.

```bash
# From the Mahara root.
make new-dev-environment
```
This Makefile target calls `make docker-image` to build the Mahara Docker images, `make css` to ensure the CSS is compiled on the host directories, and `make up` to start the `docker-compose` instance with the `docker-compose.yaml` and `docker-compose.dev.yaml` config files.

You can shut down the containers in the terminal window by pressing Ctrl-c in most terminals.

The `make new-dev-environment` command only needs to be used once.  After this `make up` will bring the instance back should you shut it down.

The `make new-dev-environment/up` commands will remain in the foreground and display a *lot* of logs.

In a new shell, you can run commands inside a container:
```bash
docker ps
docker-compose -f docker/docker-compose.yaml -f docker/docker-compose.dev.yaml \
  run <CONTAINER NAME GOES HERE> /bin/bash
# Please note: By default, the code and data directories are inside /mahara/htdocs and /mahara/data directories.

# For example, to run a Mahara command inside a container, you can run:
docker-compose -f docker/docker-compose.yaml -f docker/docker-compose.dev.yaml \
  run --user="www-data" mahara php /mahara/htdocs/admin/cli/clear_caches.php
```
Checking out older versions:

If you need to check out an older version than the one you are currently developing on, you will
get errors as the volumes are persistent between runs.

Your options are to either create a new database inside the Postgres volume and update:
```
psql -h 0.0.0.0 -U mahara
# The password can be found in your .envrc file.
mahara=# \c postgres
mahara=# ALTER DATABASE mahara RENAME to old_db;
mahara=# CREATE DATABASE mahara WITH OWNER mahara;
```
Or delete the volume entirely because you don't care about the test data you were using earlier:
```
docker volume rm mahara-db
```
Docker compose will regenerate the mahara-db volume for you when you run it again.

## Using the Mahara Docker image

The Mahara Docker image could be used to create a real Mahara instance. The
`docker-compose.yaml` file could be customised for this or to see what
environment variables are required.

Note: A separate DB server (container) is also required.

Before you use your Mahara Docker image on a production instance, please test it thoroughly.