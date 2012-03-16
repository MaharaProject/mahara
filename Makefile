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

minaccept:
	@echo "Running minimum acceptance test..."
	@find htdocs/ -type f -name "*.php" | xargs -n 1 -P 2 php -l > /dev/null && echo All good!
	@find htdocs/ -type f -name "install.xml" -path "*/db/install.xml" | xargs -n 1 -P 2 xmllint --schema htdocs/lib/xmldb/xmldb.xsd --noout
	@if git rev-parse --verify HEAD 2>/dev/null; then git show HEAD ; fi | test/coding-standard-check.pl

jenkinsaccept: minaccept
	@find ./ ! -path './.git/*' -type f | xargs clamscan > /dev/null && echo All good!

push: minaccept
	@echo "Pushing the change upstream..."
	@if test -z "$(TAG)"; then \
		git push gerrit HEAD:refs/for/1.5_STABLE; \
	else \
		git push gerrit HEAD:refs/for/1.5_STABLE/$(TAG); \
	fi
