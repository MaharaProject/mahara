# Patching NPM-managed libraries

This file holds the .patches for the npm libraries we customise

## Separating dev patches from run time patches

Find patches related to `mahara/package.json` in the `patches/root`. These are related JS libraries required for development only.
Find patches related to `mahara/htdocs/package.json` in the `patches/htdocs`. These are related JS resources required in production.

## How to add more patches

- Fix a bug in one of your dependencies `vim node_modules/some-package/brokenFile.js` and save
- Run patch-package command in the parent directory of the node_modules directory you have edited to create a .patch file.
- Commit the patch file to share the fix with your team

```bash
# 'some-package' is the name of the JS library, these are listed in the respective package.json file

# OR Patch a production-use package
npx patch-package some-package --patch-dir ../patches/htdocs

# OR Patch a development-use package
npx patch-package some-package --patch-dir ../patches/root

# Add the patch to git by force as it ignores patch files by default
git add <path to patch file> -f
git commit -m "fix brokenFile.js in some-package"
```

## What to do when patch-package fails

Check the upstream version's files to check if our customisations still apply.

1. If they've fixed the problem our customisation is patching, we can delete that section in the patch file.
2. If the files have changed slightly, manually make the customsation into the file in node_modules and run `patch-package` again to update the `.patch` file.