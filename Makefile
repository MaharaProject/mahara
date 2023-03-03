# The mode that behat tests are run with.
BEHAT_MODE = rundebugheadless
# Can limit what behat tests are run, e.g `make -e BEHAT_TESTS=change_account_settings.feature behat`
BEHAT_TESTS = null
# ask for test reports to be generated possible values are <empty>, 'html', 'junit'
BEHAT_REPORT =

# PHPStan options.
ifeq ($(LEVEL),)
	PHPSTAN_LEVEL =
else
	PHPSTAN_LEVEL = --level $(LEVEL)
endif
ifeq ($(TEST_PATH),)
	PHPSTAN_PATH =
else
	PHPSTAN_PATH = -- $(TEST_PATH)
endif

# The Ubuntu version that the Mahara base image will be based on
TEST_ADMIN_PASSWD = Kupuh1pa!
TEST_ADMIN_EMAIL  = user@example.org
DOCKER_PHP_MAJOR_VERSION ?=7
ifeq "$(strip $(DOCKER_PHP_MAJOR_VERSION))" "8.1"
DOCKER_UBUNTU_VERSION := jammy
COMPOSE_PROJECT_NAME := $(shell basename $$(pwd))-81
else ifeq  "$(strip $(DOCKER_PHP_MAJOR_VERSION))" "8"
DOCKER_UBUNTU_VERSION := impish
COMPOSE_PROJECT_NAME := $(shell basename $$(pwd))-8
else ifeq "$(strip $(DOCKER_PHP_MAJOR_VERSION))" "7.4"
DOCKER_UBUNTU_VERSION := focal
COMPOSE_PROJECT_NAME := $(shell basename $$(pwd))-74
else
DOCKER_UBUNTU_VERSION := bionic
COMPOSE_PROJECT_NAME := $(shell basename $$(pwd))
endif

export DOCKER_UBUNTU_VERSION
# Export these so they are available in the docker-compose calls later.
export COMPOSE_PROJECT_NAME
export MAHARA_DB_HOST = ${COMPOSE_PROJECT_NAME}-mahara-db
export MAHARA_REDIS_SERVER = ${COMPOSE_PROJECT_NAME}-mahara-redis
export MAHARA_DOCKER_PORT = 6142
export MAHARA_WWW_ROOT = http://localhost:${MAHARA_DOCKER_PORT}/${COMPOSE_PROJECT_NAME}
export MAHARA_ELASTICSEARCH_HOST = ${COMPOSE_PROJECT_NAME}-mahara-elastic
export NVM_DIR = ${HOME}/.nvm

CCRED=$(shell echo "\033[0;31m")
CCYELLOW=$(shell echo "\033[0;33m")
CCLIGHTGREEN=$(shell echo "\033[0;92m")
CCMAGENTA=$(shell echo "\033[0;95m")
CCEND=$(shell echo "\033[0m")

# Make expects targets to create a file that matches the target name
# unless the target is phony.
# Refer to: https://www.gnu.org/software/make/manual/html_node/Phony-Targets.html
.PHONY: css clean-css help imageoptim installcomposer initcomposer cleanssphp ssphp \
		cleanpdfexport pdfexport install phpunit behat minaccept jenkinsaccept securitycheck \
		push security docker-image docker-images docker-builder reload hard-reload \
		docker-bash docker-bash-root phpstan-analyze psa initcomposerexternal \
		initcomposermahara initchangeidcommitmsg

all: css

nvmsh := $(shell ls -l ${HOME}/.nvm/nvm.sh 2>/dev/null)

ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))
#The following is a workaround for https://github.com/nvm-sh/nvm/issues/1985.
define nvm_check
	cd .. && . "${HOME}/.nvm/nvm.sh" && cd $(ROOT_DIR) && nvm install
endef

production = true
css:
	$(info Rebuilding CSS on host)
ifeq (, $(shell which npm))
	$(error ERROR: Can't find the "npm" executable. Try "sudo apt-get install npm")
endif
ifeq (, $(shell which node))
	$(error ERROR: Can't find the "node" executable. Try "sudo apt-get install nodejs-legacy")
endif
ifeq (, $(shell which gulp))
	$(error ERROR: Can't find the "gulp" executable. Try doing "sudo npm install -g gulp")
endif
ifndef nvmsh
	$(error ERROR: Can't find the "nvm" executable at path ${HOME}/.nvm/nvm.sh - see https://wiki.mahara.org/wiki/Developer_Area/Developer_Environment for install instructions)
endif

ifndef npmsetup
	@echo "System node version: " && node -v;
	@$(nvm_check) && nvm ls && npm install && cd htdocs && npm install
endif
	@echo "Building CSS..."
	@if $(nvm_check) && npm rebuild node-sass && gulp css --production $(production) ; then echo "Done!"; else $(nvm_check) && npm rebuild node-sass && npm install; gulp css --production $(production);  fi
clean-css:
	find ./htdocs/theme/* -maxdepth 1 -name "style" -type d -exec rm -Rf {} \;

help:
	@echo "Run 'make' to do "build" Mahara (currently only CSS)"
	@echo "Run 'make new-dev-environment' if this is your first checkout"
	@echo "   This will run the 'docker-image', 'css' and 'up' targets"
	@echo "Run 'make up' to bring the docker instance back up if it was shut down"
	@echo "Run 'make down' to shut down the docker instance"
	@echo ""
	@echo "Reviews repository management targets"
	@echo "====================================="
	@echo "   This is for the https://reviews.mahara.org code review system"
	@echo "Run 'make minaccept' to run the quick pre-commit tests"
	@echo "Run 'make push' to push your changes to the reviews repository"
	@echo "Run 'make checksignoff' to check that your commits are all Signed-off-by"
	@echo ""
	@echo "Helper targets"
	@echo "=============="
	@echo "Run 'make initcomposer' to install Composer and phpunit"
	@echo "Run 'make initcomposerdev' to install Composer development tools"
	@echo "Run 'make phpunit' to execute phpunit tests"
	@echo "Run 'make install' runs the Mahara install script"
	@echo "Run 'make behat' to execute behat tests"
	@echo "Run 'make ssphp' to install SimpleSAMLphp"
	@echo "Run 'make cleanssphp' to remove SimpleSAMLphp"
	@echo "Run 'make cleancomposer' to remove the Composer vendor directories"
	@echo "Run 'make imageoptim' to losslessly optimise all images"
	@echo "Run 'make docker-image' to build a Mahara docker image"
	@echo "Run 'make docker-builder' builds the docker builder image required for docker-build"
	@echo "Run 'make phpstan-analyze' to examine the PHP code using PHPStan"

imageoptim:
	find . -iname '*.png' -exec optipng -o7 -q {} \;
	find . -iname '*.gif' -exec gifsicle -O2 -b {} \;
	find . -iname '*.jpg' -exec jpegoptim -q -p --strip-all {} \;
	find . -iname '*.jpeg' -exec jpegoptim -q -p --strip-all {} \;

composer := $(shell ls external/composer.phar 2>/dev/null)

cleancomposer:
	rm -rf htdocs/vendor/
	rm -rf external/vendor/

installcomposer:
ifdef composer
	@echo "Composer already installed..."
else
	@echo "Installing Composer..."
	@curl -sS https://getcomposer.org/installer | php -- --install-dir=external
endif

initcomposerdev: installcomposer initcomposerexternal initcomposermahara initchangeidcommitmsg

initcomposerexternal:
	@echo "Updating external dependencies with Composer for development..."
	@php external/composer.phar --working-dir=external update

initcomposermahara:
	@echo "Installing third-party dependencies with Composer for development..."
	@php external/composer.phar install

initchangeidcommitmsg: initcomposerexternal
	@echo "Appending our Change-Id commit-msg hook if needed"
ifeq ("Change-Id",$(findstring Change-Id,$(shell cat .git/hooks/commit-msg)))
	@echo "Change-Id hook already installed";
else
	@echo "Installing Change-Id hook";
	@echo >> .git/hooks/commit-msg
	@scp -p -P 29418 ${USER}@reviews.mahara.org:hooks/commit-msg .git/hooks/commit-msg.id.tmp
	@cat .git/hooks/commit-msg.id.tmp >> .git/hooks/commit-msg
	@rm .git/hooks/commit-msg.id.tmp
endif

initcomposer: installcomposer
	@echo "Removing htdocs/vendor directory so that we can rebuild it fresh"
	rm -rf htdocs/vendor
	@echo "Updating external dependencies with Composer..."
	@php external/composer.phar --working-dir=external update
	@echo "Installing third-party dependencies with Composer..."
	@php external/composer.phar install --no-dev

simplesamlphp := $(shell ls -d htdocs/auth/saml/extlib/simplesamlphp 2>/dev/null)

cleanssphp:
	@echo "Cleaning out SimpleSAMLphp..."
	rm -rf htdocs/auth/saml/extlib/simplesamlphp

ssphp: initcomposer
ifdef simplesamlphp
	@echo "SimpleSAMLphp already exists - doing nothing"
else
	@echo "Pulling SimpleSAMLphp from download ..."
	@curl -sSL https://github.com/simplesamlphp/simplesamlphp/releases/download/v1.19.7/simplesamlphp-1.19.7.tar.gz | tar  --transform 's/simplesamlphp-[0-9]+\.[0-9]+\.[0-9]+/simplesamlphp/x1' -C htdocs/auth/saml/extlib -xzf - # SimpleSAMLPHP release tarball already has all composer dependencies.
	@php append_composer.php htdocs/auth/saml/extlib/simplesamlphp/composer.json simplesamlphp/composer-module-installer
	@php external/composer.phar --working-dir=htdocs/auth/saml/extlib/simplesamlphp require predis/predis
	@echo "Copying www/resources/* files to sp/resources/ ..."
	@cp -R htdocs/auth/saml/extlib/simplesamlphp/www/resources/ htdocs/auth/saml/sp/
	@echo "Deleting unneeded files ..."
#	Delete composer.json and .lock files to avoid leaking minor version info
	@find htdocs/auth/saml/extlib -type f -name composer.json -delete
	@find htdocs/auth/saml/extlib -type f -name composer.lock -delete
	@echo "Done!"
endif

pdfexportdir := $(shell ls -d htdocs/lib/chrome-php/ 2>/dev/null)
pdfexportfile := $(shell ls -d htdocs/lib/chrome-php/chrome-0.11/ 2>/dev/null)

cleanpdfexport:
	@echo "Cleaning out PDF export files..."
	rm -rf htdocs/lib/chrome-php

pdfexport: initcomposer
ifdef pdfexportdir
ifdef pdfexportfile
	@echo "PDF export files already exists - doing nothing"
else
	@echo "Older PDF export files exists - update via: make cleanpdfexport && make pdfexport"
endif
else
	@echo "Pulling chrome-0.11 from download ..."
	@curl -sSL https://github.com/chrome-php/chrome/archive/0.11.zip -o pdf_tmp.zip && unzip pdf_tmp.zip -d htdocs/lib/chrome-php && rm pdf_tmp.zip
	@php append_composer.php htdocs/lib/chrome-php/chrome-0.11/composer.json bamarni/composer-bin-plugin
	@php external/composer.phar --working-dir=htdocs/lib/chrome-php/chrome-0.11 install
	@find htdocs/lib/chrome-php/chrome-0.11 -type f -name composer.json -delete
	@find htdocs/lib/chrome-php/chrome-0.11 -type f -name composer.lock -delete
	@find htdocs/lib/chrome-php -type d -name Tests -exec rm -r {} +
	@echo "Done!"
endif

vendorphpunit := $(shell external/vendor/bin/phpunit --version 2>/dev/null)

install:
	php htdocs/admin/cli/install.php --adminpassword=$(TEST_ADMIN_PASSWD) --adminemail=$(TEST_ADMIN_EMAIL)

phpunit: install
	@echo "Running phpunit tests..."
ifdef vendorphpunit
	@external/vendor/bin/phpunit --log-junit logs/tests/phpunit-results.xml htdocs/
else
	@phpunit --log-junit logs/tests/phpunit-results.xml htdocs/
endif

behat:
	./test/behat/mahara_behat.sh $(BEHAT_MODE) $(BEHAT_TESTS) $(BEHAT_REPORT)

revision := $(shell git rev-parse --verify HEAD 2>/dev/null)
whitelist := $(shell grep / test/WHITELIST | xargs -I entry find entry -type f | xargs -I file echo '! -path ' file 2>/dev/null)
mergebase := $(shell git fetch gerrit >/dev/null 2>&1 && git merge-base HEAD gerrit/main)
breakpoints := $(shell git diff-tree --diff-filter=ACM --no-commit-id -r -z -p $(mergebase) HEAD test/behat/features :^test/behat/features/manual_checks |  grep "I insert breakpoint")

minaccept:
	@echo "Running minimum acceptance test..."
ifdef breakpoints
	@echo "Oops, you left breakpoints in your tests :/"
	@git diff-tree --diff-filter=ACM --no-commit-id -r -z -p $(mergebase) HEAD test/behat/features |  grep "I insert breakpoint"
	@echo "Please remove breakpoints, commit and push again"
	exit 1
endif
ifdef revision
	@git diff-tree --diff-filter=ACM --no-commit-id --name-only -z -r $(revision) htdocs | grep -z "^htdocs/.*\.php$$" | xargs -0 -n 1 -P 2 --no-run-if-empty php -l
	@php test/versioncheck.php
	@git diff-tree --diff-filter=ACM --no-commit-id --name-only -z -r $(revision) htdocs | grep -z '^htdocs/.*/db/install\.xml$$' | xargs -0 -n 1 -P 2 --no-run-if-empty xmllint --schema htdocs/lib/xmldb/xmldb.xsd --noout
	@git diff-tree --diff-filter=ACM --no-commit-id --name-only -r $(revision) | xargs -I {} find {} $(whitelist) | xargs -I list git show $(revision) -- list | test/coding-standard-check.pl
	@echo "Acceptance test passed. :)"
else
	@echo "No revision found!"
endif

jenkinsaccept: minaccept
	@find ./ ! -path './.git/*' -type f -print0 | xargs -0 clamscan > /dev/null && echo All good!

sshargs := $(shell git config --get remote.gerrit.url | sed -re 's~^ssh://([^@]*)@([^:]*):([0-9]*)/mahara~-p \3 -l \1 \2~')
sha1chain := $(shell git log $(mergebase)..HEAD --pretty=format:%H | xargs)
changeidchain := $(shell git log $(mergebase)..HEAD --pretty=format:%b | grep '^Change-Id:' | cut -d' ' -f2)

securitycheck:
	@if ssh $(sshargs) gerrit query --format TEXT -- $(shell echo $(sha1chain) $(changeidchain) | sed -e 's/ / OR /g') | grep 'status: DRAFT' >/dev/null; then \
		echo "This change has drafts in the chain. Please use make security instead"; \
		false; \
	fi
	@if git log $(mergebase)..HEAD --pretty=format:%B | grep -iE '(security|cve)' >/dev/null; then \
		echo "This change has a security keyword in it. Please use make security instead"; \
		false; \
	fi

push: securitycheck minaccept
	@echo "Pushing the change upstream ..."
	@if test -z "$(TAG)"; then \
		git push gerrit HEAD:refs/for/main; \
	else \
		git push gerrit HEAD:refs/for/main -o topic=$(TAG); \
	fi

wip: securitycheck
	@echo "Pushing the change upstream as WIP ..."
	@if test -z "$(TAG)"; then \
		git push gerrit HEAD:refs/for/main%wip; \
	else \
		git push gerrit HEAD:refs/for/main%wip -o topic=$(TAG); \
	fi

security: minaccept
	@echo "Pushing the SECURITY change upstream ..."
	@if test -z "$(TAG)"; then \
		git push gerrit HEAD:refs/for/main%private; \
	else \
		git push gerrit HEAD:refs/for/main%private -o topic=$(TAG); \
	fi
	ssh $(sshargs) gerrit set-reviewers --add \"Mahara Security Managers\" -- $(sha1chain)

# Builds Mahara server docker image
docker-images: docker-image
docker-image:
	$(info Preparing images for $(DOCKER_UBUNTU_VERSION))
	docker build --pull --file docker/Dockerfile.mahara-base-$(DOCKER_UBUNTU_VERSION) \
	  --build-arg BASE_VERSION=$(DOCKER_UBUNTU_VERSION) \
		--no-cache \
		--tag mahara-base:$(DOCKER_UBUNTU_VERSION) .
	docker build --file docker/Dockerfile.mahara-web-$(DOCKER_UBUNTU_VERSION) \
	  --build-arg BASE_IMAGE=mahara-base:$(DOCKER_UBUNTU_VERSION) \
		--no-cache \
	  --tag mahara:$(DOCKER_UBUNTU_VERSION) .

# Builds a docker image that is able to build Mahara. Useful if you don't want
# to install dependencies on your system.
# The builder is made for the user that will use it. This is so that the built
# files are owned by the user and not some other user (eg not root)
docker-builder:
	docker build --pull --file docker/Dockerfile.mahara-base-$(DOCKER_UBUNTU_VERSION) \
	  --build-arg BASE_VERSION=$(DOCKER_UBUNTU_VERSION) \
		--tag mahara-base:$(DOCKER_UBUNTU_VERSION) .
	docker build --file docker/Dockerfile.mahara-builder \
	  --build-arg BASE_IMAGE=mahara-base:$(DOCKER_UBUNTU_VERSION) \
		--build-arg IMAGE_UID=$(shell id -u) --build-arg IMAGE_GID=$(shell id -g) \
		--tag mahara-builder .

#Connects to the database created by docker compose for this environment
docker-db-connect:
	docker-compose -f docker/docker-compose.yaml -f docker/docker-compose.dev.yaml run db psql -h ${COMPOSE_PROJECT_NAME}-mahara-db -U mahara

docker-db-drop:
	docker-compose -f docker/docker-compose.yaml -f docker/docker-compose.dev.yaml run --rm db /bin/bash -c "PGPASSWORD=\$$POSTGRES_PASSWORD dropdb -h ${COMPOSE_PROJECT_NAME}-mahara-db -U \$$POSTGRES_USER  \$$POSTGRES_DB"

docker-db-create:
	docker-compose -f docker/docker-compose.yaml -f docker/docker-compose.dev.yaml run --rm db /bin/bash -c "PGPASSWORD=\$$POSTGRES_PASSWORD createdb -h ${COMPOSE_PROJECT_NAME}-mahara-db -U \$$POSTGRES_USER  \$$POSTGRES_DB"

docker-db-refresh:
	$(MAKE) docker-db-drop
	$(MAKE) docker-db-create

docker-db-restore:
ifdef dbpath
	$(MAKE) docker-db-refresh
	@echo 'dbpath is defined'
	docker-compose -f docker/docker-compose.yaml -f docker/docker-compose.dev.yaml run --rm  -v $(dbpath):/tmp/dump.pgdump \
	       	db /bin/bash -c	"PGPASSWORD=\$$POSTGRES_PASSWORD pg_restore -O -h ${COMPOSE_PROJECT_NAME}-mahara-db -U \$$POSTGRES_USER -d \$$POSTGRES_DB /tmp/dump.pgdump"
else
	@echo 'Usage :$$ dbpath="/path/to/dbdump" make docker-db-restore'
endif
# Brings up a new development instance.
up:
ifeq (,$(wildcard ./htdocs/config.php))
	cp ./htdocs/config-environment.php ./htdocs/config.php
endif
ifeq (,$(wildcard ./docker/.env))
	cp ./docker/.env-dist ./docker/.env
endif
	$(info Docker php version: ${DOCKER_PHP_MAJOR_VERSION})
	$(MAKE) shared-up
	$(MAKE) dev-up
	$(MAKE) css

# Reload a single development instance.
reload: down up

# Dev reload - shut it down, rebuild images, bring it back up.
hard-reload: down docker-images up

# Take down a single development instance. See `make shared-down`.
down:
	$(info Shutting down site containers: $(COMPOSE_PROJECT_NAME):$(DOCKER_UBUNTU_VERSION))
	docker-compose -f docker/docker-compose.yaml -f docker/docker-compose.dev.yaml down

# Spins up the shared containers. Has a static project name so that only one
# instance of these containers are created.
shared-up:
	$(info Starting shared containers.)
	$(shell export COMPOSE_PROJECT_NAME=shared-mahara && docker-compose -f docker/docker-compose.shared.yaml up -d)

# @TODO: This will error out if there are any containers connected to
# mahara-net. We should check with `docker network inspect mahara-net` to see
# how many containers are connected. If more than 2 (mail/nginx) then do not
# shutdown.
shared-down:
	$(info Shutting down shared containers.)
	$(shell export COMPOSE_PROJECT_NAME=shared-mahara && docker-compose -f docker/docker-compose.shared.yaml down)

docker-bash:
	$(info Docker php version: ${DOCKER_PHP_MAJOR_VERSION})
	$(info Connected as the Webserver user)
	docker-compose -f docker/docker-compose.yaml -f docker/docker-compose.dev.yaml run --user=www-data web /bin/bash

docker-bash-root:
	$(info Docker php version: ${DOCKER_PHP_MAJOR_VERSION})
	$(info Connected as the root user)
	docker-compose -f docker/docker-compose.yaml -f docker/docker-compose.dev.yaml run web /bin/bash

# Brings up a new development instance that assumes the presence of shared
# mailhog and nginx containers.
dev-up:
	docker-compose -f docker/docker-compose.yaml -f docker/docker-compose.dev.yaml up -d
	$(info Your site will be available at ${MAHARA_WWW_ROOT}/)
	$(info You can view logs of the container you are interested in with the `docker logs <container-name>` command.)
	$(info The `docker ps` will give you a list of running containers.)

# PHPStan
#
# Run PHPStan with a specific level overriding phpstan.neon config.
# make psa LEVEL=[n]
#
# Run PHPStan on a specific target.
# make psa TEST_PATH=htdocs/lib
psa: phpstan-analyze
phpstan-analyze: initcomposer
	external/vendor/bin/phpstan analyze $(PHPSTAN_LEVEL) $(PHPSTAN_PATH)
