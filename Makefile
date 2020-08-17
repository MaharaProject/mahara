# The mode that behat tests are run with.
BEHAT_MODE = rundebugheadless
# Can limit what behat tests are run, e.g `make -e BEHAT_TESTS=change_account_settings.feature behat`
BEHAT_TESTS = null
# ask for test reports to be generated possible values are <empty>, 'html', 'junit'
BEHAT_REPORT =
# The Ubuntu version that the Mahara base image will be based on
DOCKER_UBUNTU_VERSION = bionic
TEST_ADMIN_PASSWD = Kupuh1pa!
TEST_ADMIN_EMAIL  = user@example.org

# Make expects targets to create a file that matches the target name
# unless the target is phony.
# Refer to: https://www.gnu.org/software/make/manual/html_node/Phony-Targets.html
.PHONY: css clean-css help imageoptim installcomposer initcomposer cleanssphp ssphp \
		cleanpdfexport pdfexport install phpunit behat minaccept jenkinsaccept securitycheck \
		push security docker-image docker-builder

all: css

production = true
css:
ifeq (, $(shell which npm))
	$(error ERROR: Can't find the "npm" executable. Try "sudo apt-get install npm")
endif
ifeq (, $(shell which node))
	$(error ERROR: Can't find the "node" executable. Try "sudo apt-get install nodejs-legacy")
endif
ifeq (, $(shell which gulp))
	$(error ERROR: Can't find the "gulp" executable. Try doing "sudo npm install -g gulp")
endif
ifndef npmsetup
	npm install
endif
	@echo "Building CSS..."
	@if gulp css --production $(production) ; then echo "Done!"; else npm install; gulp css --production $(production);  fi

clean-css:
	find ./htdocs/theme/* -maxdepth 1 -name "style" -type d -exec rm -Rf {} \;

help:
	@echo "Run 'make' to do "build" Mahara (currently only CSS)"
	@echo "Run 'make initcomposer' to install Composer and phpunit"
	@echo "Run 'make phpunit' to execute phpunit tests"
	@echo "Run 'make install' runs the Mahara install script"
	@echo "Run 'make behat' to execute behat tests"
	@echo "Run 'make ssphp' to install SimpleSAMLphp"
	@echo "Run 'make cleanssphp' to remove SimpleSAMLphp"
	@echo "Run 'make imageoptim' to losslessly optimise all images"
	@echo "Run 'make minaccept' to run the quick pre-commit tests"
	@echo "Run 'make checksignoff' to check that your commits are all Signed-off-by"
	@echo "Run 'make push' to push your changes to the repo"
	@echo "Run 'make docker-image' to build a Mahara docker image"
	@echo "Run 'make docker-builder' builds the docker builder image required for docker-build"

imageoptim:
	find . -iname '*.png' -exec optipng -o7 -q {} \;
	find . -iname '*.gif' -exec gifsicle -O2 -b {} \;
	find . -iname '*.jpg' -exec jpegoptim -q -p --strip-all {} \;
	find . -iname '*.jpeg' -exec jpegoptim -q -p --strip-all {} \;

composer := $(shell ls external/composer.phar 2>/dev/null)

installcomposer:
ifdef composer
	@echo "Composer already installed..."
else
	@echo "Installing Composer..."
	@curl -sS https://getcomposer.org/installer | php -- --install-dir=external
endif

initcomposer: installcomposer
	@echo "Updating external dependencies with Composer..."
	@php external/composer.phar --working-dir=external update

simplesamlphp := $(shell ls -d htdocs/auth/saml/extlib/simplesamlphp 2>/dev/null)

cleanssphp:
	@echo "Cleaning out SimpleSAMLphp..."
	rm -rf htdocs/auth/saml/extlib/simplesamlphp

ssphp: initcomposer
ifdef simplesamlphp
	@echo "SimpleSAMLphp already exists - doing nothing"
else
	@echo "Pulling SimpleSAMLphp from download ..."
	@curl -sSL https://github.com/simplesamlphp/simplesamlphp/releases/download/v1.18.7/simplesamlphp-1.18.7.tar.gz | tar  --transform 's/simplesamlphp-[0-9]+\.[0-9]+\.[0-9]+/simplesamlphp/x1' -C htdocs/auth/saml/extlib -xzf - # SimpleSAMLPHP release tarball already has all composer dependencies.
	@php external/composer.phar --working-dir=htdocs/auth/saml/extlib/simplesamlphp require predis/predis
	@echo "Copying www/resources/* files to sp/resources/ ..."
	@cp -R htdocs/auth/saml/extlib/simplesamlphp/www/resources/ htdocs/auth/saml/sp/
	@echo "Deleting unneeded files ..."
#	Delete composer.json and .lock files to avoid leaking minor version info
	@find htdocs/auth/saml/extlib -type f -name composer.json -delete
	@find htdocs/auth/saml/extlib -type f -name composer.lock -delete
	@echo "Done!"
endif

pdfexportfile := $(shell ls -d htdocs/lib/chrome-php/headless-chromium-php-master 2>/dev/null)

cleanpdfexport:
	@echo "Cleaning out PDF export files..."
	rm -rf htdocs/lib/chrome-php

pdfexport: initcomposer
ifdef pdfexportfile
	@echo "PDF export files already exists - doing nothing"
else
	@echo "Pulling Headless-chromium-php from download ..."
	@curl -sSL https://github.com/chrome-php/headless-chromium-php/archive/master.zip -o pdf_tmp.zip && unzip pdf_tmp.zip -d htdocs/lib/chrome-php && rm pdf_tmp.zip
	@php external/composer.phar --working-dir=htdocs/lib/chrome-php/headless-chromium-php-master install
	@find htdocs/lib/chrome-php/headless-chromium-php-master -type f -name composer.json -delete
	@find htdocs/lib/chrome-php/headless-chromium-php-master -type f -name composer.lock -delete
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
mergebase := $(shell git fetch gerrit >/dev/null 2>&1 && git merge-base HEAD gerrit/master)
breakpoints := $(shell git diff-tree --diff-filter=ACM --no-commit-id -r -z -p $(mergebase) HEAD test/behat/features |  grep "I insert breakpoint")

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
	@echo "Pushing the change upstream..."
	@if test -z "$(TAG)"; then \
		git push gerrit HEAD:refs/publish/master; \
	else \
		git push gerrit HEAD:refs/publish/master/$(TAG); \
	fi

security: minaccept
	@echo "Pushing the SECURITY change upstream..."
	@if test -z "$(TAG)"; then \
		git push gerrit HEAD:refs/drafts/master; \
	else \
		git push gerrit HEAD:refs/drafts/master/$(TAG); \
	fi
	ssh $(sshargs) gerrit set-reviewers --add \"Mahara Security Managers\" -- $(sha1chain)

# Builds Mahara server docker image
docker-image:
	docker build --pull --file docker/Dockerfile.mahara-base \
	  --build-arg BASE_VERSION=$(DOCKER_UBUNTU_VERSION) \
		--tag mahara-base:$(DOCKER_UBUNTU_VERSION) .
	docker build --file docker/Dockerfile.mahara-web \
	  --build-arg BASE_IMAGE=mahara-base:$(DOCKER_UBUNTU_VERSION) \
	  --tag mahara .

# Builds a docker image that is able to build Mahara. Useful if you don't want
# to install dependencies on your system.
# The builder is made for the user that will use it. This is so that the built
# files are owned by the user and not some other user (eg not root)
docker-builder:
	docker build --pull --file docker/Dockerfile.mahara-base \
	  --build-arg BASE_VERSION=$(DOCKER_UBUNTU_VERSION) \
		--tag mahara-base:$(DOCKER_UBUNTU_VERSION) .
	docker build --file docker/Dockerfile.mahara-builder \
	  --build-arg BASE_IMAGE=mahara-base:$(DOCKER_UBUNTU_VERSION) \
		--build-arg IMAGE_UID=$(shell id -u) --build-arg IMAGE_GID=$(shell id -g) \
		--tag mahara-builder .
