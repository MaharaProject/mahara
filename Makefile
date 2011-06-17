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

branch := $(GERRIT_REFSPEC)
ifeq (, $(branch))
branch := $(shell bash -c "git branch | grep \* | sed -e 's/ *\* *//'" )
endif

ifeq ("(no branch)", "$(branch)")
remote := origin
else
remote := $(shell bash -c "git config --get branch.$(branch).remote" )
endif

ifeq (".", "$(remote)")
remote := origin
endif
ifeq ("", "$(remote)")
remote := origin
endif

commitid := $(shell bash -c "git merge-base $(remote)/master HEAD")

minaccept:
	@echo "Running minimum acceptance test..."
	@find htdocs/ -type f -name "*.php" | xargs -n 1 -P 2 php -l > /dev/null && echo All good!
	@find htdocs/ -type f -name "install.xml" -path "*/db/install.xml" | xargs -n 1 -P 2 xmllint --schema htdocs/lib/xmldb/xmldb.xsd --noout
	@if git rev-parse --verify HEAD 2>/dev/null; then git diff-index -p -M --cached $(commitid) -- ; fi | test/coding-standard-check.pl

push: minaccept
	@echo "Pushing the change upstream..."
	@if test -z "$(TAG)"; then \
		git push gerrit HEAD:refs/for/master; \
	else \
		git push gerrit HEAD:refs/for/master/$(TAG); \
	fi
