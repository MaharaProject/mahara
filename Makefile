all:
	@echo "Run 'make imageoptim' to losslessly optimise all images"
	@echo "Run 'make minaccept' to run the quick pre-commit tests"
	@echo "Run 'make checksignoff' to check that your commits are all Signed-off-by"
	@echo "Run 'make push' to push your changes to the repo"

imageoptim:
	find . -iname '*.png' -exec optipng -o7 -q {} \;
	find . -iname '*.gif' -exec gifsicle -O2 -b {} \;
	find . -iname '*.jpg' -exec jpegoptim -q -p --strip-all {} \;
	find . -iname '*.jpeg' -exec jpegoptim -q -p --strip-all {} \;

revision := $(shell git rev-parse --verify HEAD 2>/dev/null)
whitelist := $(shell grep / test/WHITELIST | xargs -I entry find entry -type f | xargs -I file echo '! -path ' file 2>/dev/null)

minaccept:
	@echo "Running minimum acceptance test..."
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
mergebase := $(shell git fetch gerrit >/dev/null 2>&1 && git merge-base HEAD gerrit/1.10_STABLE)
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
		git push gerrit HEAD:refs/publish/1.10_STABLE; \
	else \
		git push gerrit HEAD:refs/publish/1.10_STABLE/$(TAG); \
	fi

security: minaccept
	@echo "Pushing the SECURITY change upstream..."
	@if test -z "$(TAG)"; then \
		git push gerrit HEAD:refs/drafts/1.10_STABLE; \
	else \
		git push gerrit HEAD:refs/drafts/1.10_STABLE/$(TAG); \
	fi
	ssh $(sshargs) gerrit set-reviewers --add \"Mahara Security Managers\" -- $(sha1chain)
