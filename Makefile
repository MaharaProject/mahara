all:
	@echo "Run 'make initcomposer' to install Composer and phpunit"
	@echo "Run 'make phpunit' to execute phpunit tests"
	@echo "Run 'make imageoptim' to losslessly optimise all images"
	@echo "Run 'make minaccept' to run the quick pre-commit tests"
	@echo "Run 'make checksignoff' to check that your commits are all Signed-off-by"
	@echo "Run 'make push' to push your changes to the repo"

imageoptim:
	find . -iname '*.png' -exec optipng -o7 -q {} \;
	find . -iname '*.gif' -exec gifsicle -O2 -b {} \;
	find . -iname '*.jpg' -exec jpegoptim -q -p --strip-all {} \;
	find . -iname '*.jpeg' -exec jpegoptim -q -p --strip-all {} \;

composer := $(shell ls external/composer.phar 2>/dev/null)

initcomposer:
ifdef composer
	@echo "Updating Composer..."
	@php external/composer.phar --working-dir=external update
else
	@echo "Installing Composer..."
	@curl -sS https://getcomposer.org/installer | php -- --install-dir=external
	@php external/composer.phar --working-dir=external install
endif

vendorphpunit := $(shell external/vendor/bin/phpunit --version 2>/dev/null)

phpunit:
	@echo "Running phpunit tests..."
ifdef vendorphpunit
	@external/vendor/bin/phpunit --log-junit logs/tests/phpunit-results.xml htdocs/
else
	@phpunit --log-junit logs/tests/phpunit-results.xml htdocs/
endif


revision := $(shell git rev-parse --verify HEAD 2>/dev/null)
whitelist := $(shell grep / test/WHITELIST | xargs -I entry find entry -type f | xargs -I file echo '! -path ' file 2>/dev/null)

minaccept:
	@echo "Running minimum acceptance test..."
ifdef revision
	@find htdocs -type f -name "*.php" -print0 | xargs -0 -n 1 -P 2 php -l > /dev/null && echo All good!
	@php test/versioncheck.php
	@find htdocs -type f -name "install.xml" -path "*/db/install.xml" -print0 | xargs -0 -n 1 -P 2 xmllint --schema htdocs/lib/xmldb/xmldb.xsd --noout
	@git diff-tree --diff-filter=ACMR --no-commit-id --name-only -r $(revision) | xargs -I {} find {} $(whitelist) | xargs -I list git show $(revision) list | test/coding-standard-check.pl
else
	@echo "No revision found!"
endif

jenkinsaccept: minaccept
	@find ./ ! -path './.git/*' -type f -print0 | xargs -0 clamscan > /dev/null && echo All good!

sshargs := $(shell git config --get remote.gerrit.url | sed -re 's~^ssh://([^@]*)@([^:]*):([0-9]*)/mahara~-p \3 -l \1 \2~')
mergebase := $(shell git fetch gerrit >/dev/null 2>&1 && git merge-base HEAD gerrit/master)
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
