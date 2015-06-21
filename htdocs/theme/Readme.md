# Mahara theming

## Index
* [Theme Developer Set-up](#theme-developer-set-up)
  * [NodeJS](#nodejs)
  * [Gulp](#gulp)
* [Working with themes](#working-with-themes)
  * [Folder structure (raw)](#folder-structure-raw-)
  * [Sub theme](#sub-theme)
    * [CSS](#css)
    * [Sass and Gulp](#sassandgulp)
    * [Sass: Advanced customisation](#sass-advanced-customisation)
  * [Templates](#templates)

## Tools
* [NodeJS](https://nodejs.org/)
* [GulpJS](http://gulpjs.com/)
* [Sass](http://sass-lang.com/)
* [Bootstrap](http://getbootstrap.com/)

## Theme Developer Set-up
Mahara's raw and default themes are based on the sass port of bootstrap 3.3.1. We use gulp to turn sass into css (via lib-sass), add vendor prefixes, and minimize.

### NodeJS
Gulp uses Node, so if you haven’t already, you may need to [install it](https://github.com/joyent/node/wiki/Installing-Node.js-via-package-manager)
. If you are unsure whether you have node, open a terminal (commandline) and type:

	node -v

If you get a version number, then congrats, you have node!

If not, you can install via a terminal:

Linux:

	curl -sL https://deb.nodesource.com/setup_0.12 | sudo bash -
	sudo apt-get install -y nodejs

MacOSX (with homebrew):

	brew install node

Windows:
Hit the install button on the [Node website](https://nodejs.org/), and follow the instructions.

### Gulp
If you haven't used gulp before, or don't have it installed, you’ll need to globally install it using the Node Package Manager (npm) from the terminal:

	npm install -g gulp

From the terminal navigate to the theme you are working on (e.g):

	cd mahara/htdocs/theme/raw

Install the dependencies:

	npm install

Run the default gulp tasks:

	gulp

This will watch all the scss files in your project for changes, and recompile your css.

## Working with themes
Each time you work on a Mahara theme using gulp, you will need to use a terminal to navigate to the place the gulpfile.js is located for that theme (e.g):

	cd mahara/htdocs/theme/raw

...and run

	gulp

### Folder structure (raw)
The raw theme sass has been split out into partials based on the purpose it serves to the theme.  A partial is a Sass file named with a leading underscore (e.g. _partial.scss). The underscore lets Sass know that the file is only a partial file and that it should not be generated into a CSS file. Sass partials are used with the @import directive.


* components - blocks of css used throughout Mahara across many different features. These are often extensions to bootstraps core components. These include buttons, switches, list-groups, labels etc.

* features - feature specific css. We have tried to avoid these where possible and rely on collections of components, but there are some places where feature specific partials made sense: filebrowsers, comments, dashboard-widgets etc

* form - form  related components. Including base elements, alerts, form-groups etc

* layout - sass realted to the laying out of a page. This includes navigation, custom column layouts, and the panels found throughout mahara, as well as specific page regions such as the header and footer.

* lib - sass related to js or smaller sass libraries (i.e font-awesome)

* typography - components that serve a type specific purpose. Styling of the base typography elements (paragrpahs, headings, lists, blockquotes etc), and inclusion of font families.

* utilities - sass variables, utility classes, mixins, and _index_ files (files that only include references to other sass files/partials).

* style.scss - This is the core index file for the theme. It _includes_ a reference to each of the partial sass files needed to build the theme. When gulp is run, the resulting file (found in e.g. raw/css) will be named style.css, taking it's name from our primary index file. tinymce.scss serves the same purpose, but for styles to be imported into tinymce's editing window. The other non-partial files found in the raw theme belong to libraries we have not been able to consolidate yet.

* _custom.scss - pieces of css too small to justify a component or a feature.

* _shame.scss - a file for hotfixes, hacks, and anything that a developer doesn't have time to test or implement properly. This can also be a place for browser or environment specific fixes that may be able to be removed at a later date.


### Sub themes
There are a couple of ways of working with subthemes (themes that extend raw).

#### 1. CSS
If sass and gulp are more complex than you need, you can choose to work with regular css (or whatever other css tools you prefer).

1. Create a new folder for your theme, and copy themeconfig.php from the default theme. Make sure you **REMOVE THIS LINE** (we want the css from raw in this case):

	$theme->overrideparentcss = true;
2. Fill themeconfig.php with your new themes details
3. Create css/style.css
4. Add your css to css/style.css

#### 2. Sass and Gulp
If you want more flexibility, are comfortable with sass and gulp (or are working with others who can help you out), then you can use the raw sass files directly (as the default theme does). As a quick starting point, you may wish to copy the default theme and rename it.

To set up a theme to work with sass and gulp way you should override the parent theme's css by placing this line in your new themeconfig.php:

	/* This theme includes all css via sass, so we don't need raw's css. */
	$theme->overrideparentcss = true;

You will need (you can copy these from default):
*	gulpfile.js
* 	package.json
*	sass/style.scss

If you want to override variables (colours, fonts, etc), you should also copy these files:
* utilities/_brand-variables.scss - for brand color definitions
* utilities/_bootstrap-variables.scss - the original variables file from bootstrap. Here we can assign our brand variables to bootstrap's component based variables.
* utilities/_custom-variables.scss - like bootstraps variables file but for custom components.

_custom.scss and _shame.scss are optional. If you don't choose to copy them over, remember to remove the reference to them from style.scss!

Point style.scss at your theme copies of these files (this should already be the case if you copied from the default theme).

From the terminal navigate to your new theme (e.g.):

	cd mahara/htdocs/theme/your_theme

Then run:

	npm install

This will ensure you have all the dependencies mentioned in gulpfile.js in order to build your sass into css.

Create your own component partials in your_theme/sass directory and and import them in style.scss (e.g):

	@import "components/_mycomponent";


### Sass: Advanced customisation
There may be cases where you want to replace an entire component or feature file, rather than just over ridding the css. There are two files in the raw theme that act as indexes for all (non-variable) partials:
* raw/sass/utilities/_bootstrap-index";
* raw/sass/utilities/_index";

The first is the index for all bootstrap partials. If you want to exclude any part of bootstrap from your custom theme, this is the file you will need to copy. Be aware that some parts of this may be used by other files in raw, so excluding a bootstrap dependency may not be straight forward. In particular - we have made heavy use of panels throughout the raw theme.

The second is an index to the modifications and additions made to bootstrap for the raw theme. It is perhaps more likely you will want to replace components referenced from within this file.

1. Copy across the index file with the component you want to remove/replace
2. replace the reference to the raw index file in your theme's style.scss with the new location.
3. If needed (ie for _index.scss), update the import locations to point back at the raw theme (@import "../typography/fonts" will become @import "../../../raw/sass/typography/fonts")
4. remove the line that references the component you no longer need, and replace it with your own version.

It's a bit of set-up work, but you will only need to do this once. Afterwards you can replace any component you like.


## Templates
If a template in the raw theme doesn't suit you, simply copy it into a similar location in your theme (e.g. your/theme/templates/header/head.tpl) and edit it. Your theme's copy of the file should be chosen over the original raw version.
