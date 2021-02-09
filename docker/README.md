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

To install and configure Docker on a recent Ubuntu desktop (Ubuntu 18.04 Bionic or later):

```
  sudo apt install docker.io
  # Add yourself to the Docker group
  sudo usermod -aG docker $USER
  newgrp docker

```

Rerun the Docker test above to confirm.

Note: You shouldn't need to run Docker with 'sudo'. Attempting to run some of the
commands in this README with 'sudo' are likely to cause errors.

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
on a single server. 

The develpment setup specified below allows for this approach automatically by using the config specified in ./htdocs/config-environment.php by default.  

Note: Instead of putting values into config-environment.php, you can set up a .envrc
file to contain your custom variables as that will not be pushed to the repository
when you commit changes. It is excluded in .gitignore.

**For development purposes, we recommend that you will set up a .envrc file. Therefore, the
following instructions will assume that.**

## Using Docker for development purposes

We are going to use Docker compose to start a new developer environment.

If this is your first time using this Docker approach you will need to run
`make docker-image` to build the Mahara Docker images.

### Starting a new development environment

**Note**: Add Gerrit so you can `make push` later if you want to [contribute your changes](https://wiki.mahara.org/wiki/Developer_Area/Contributing_Code) to the Mahara project.

* Run the command `make up` to start your Mahara Docker environment. This configures your environment automatically.
* Open the URL that you see on screen. It starts with http://localhost:6142/. Mahara is available in a subdirectory, and the name of it is the name of the folder of your repository. If you installed Mahara in the folder 'mahara', then the URL is http://localhost:6142/mahara. If you installed it in the folder '20.10', the URL is http://localhost:6142/20.10.

Useful commands:

* List running containers: `docker ps`
* View logs for a container: `docker logs <container name>`
* Run cli commands inside a contaner `make docker-bash`

**Note**: Site-specific containers are prefixed with the name of the directory of your site, followed by 'mahara', e.g. 'mahara-mahara' or '20.10-mahara' for repositories in the directories 'mahara' and '20.10' respectively. Shared containers are prefixed 'shared-mahara'.


### Shutting down the development environment

From the site directory in your terminal, type `make down`. That will shut down all containers unique to this site, i.e. shared containers are not shut down, e.g. if you run multiple Docker images in parallel.

If you type `docker ps` again, you will see the shared containers that are not shut down by `make down`. All shared containers for the Mahara project are prefixed 'shared-mahara'. At the moment, the only two shared containers are mailhog and nginx. If these are the only two remaining ones, you can shut them down with `make shared-down`.

### Mail setup for development

Mail is delivered to a local mail server called [Mailhog](https://github.com/mailhog/MailHog).

This server is shared between all your development sites.

**This mail server within Docker will not forward any email to a real mail server.** Instead, it will keep all mail output and present it at:

http://localhost:8025

That means that you do not have to configure the config.php variable 'sendallemailto' because mails will not be sent to any address.

### Deleting a Database or Sitedata for a site

During development you may wish to delete the database and or sitedata in order to start again from a fresh instance.
First you should shut down your site.
`make down`
Then you can delete the "volume" which contains the database and or sitedata. 
First we list the volumes:
`docker volume ls`
On this list you will see a list of volumes names.
You will see a volume with the name <Foldername>_mahara-db and <Foldername>_mahara-data.
You can then use the command `docker volume rm <foldername>_mahara-db` in order to remove the database or `docker volume rm <foldername>_mahara-data` to remove the sitedata.  

When you run `make up` new volumes will be created automatically to replace the removed ones providing you with a fresh instance.

### Running automated tests inside containers 

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

### Running automated tests

The following is not necessary for a standard developer testing setup.

The running of phpunit and Behat tests via Docker is in the beginning stages, and there are
changes that need to be made to get them to run fully.

Please note these are not part of the developer environment specified above, and we intend to integrate these into that developer environment in the future.

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


## Using the Mahara Docker image

The Mahara Docker image is **provided for development purposes only** at this stage. If you would like to use it in production, please be aware that you will need to change the Docker compose files and make configuration changes, e.g. passwords, email configuration, caching.
