# Mahara Theming

This readme is split into two main sections, aimed at either [theme developers](#customising or creating themes) creating new themes or [Mahara developers](#mahara-developer-guide) doing maintenance or adding new features to existing themes.

## Index
* [Customising or creating themes](#customising-or-creating-themes)
  * [Regular CSS only (easiest)](#regular-css-only)
  * [Sass and Gulp (most flexible)](#sass-and-gulp)
    * [Sass: Advanced customisation](#sass-advanced-customisation)
  * [Templates](#templates)
  * [Images, fonts and javascript](#images-fonts-and-javascript)
* [Mahara developer guide](#mahara-developer-guide)
  * [NodeJS](#nodejs)
  * [Gulp](#gulp)
    * [Setting up Gulp](#setting-up-gulp)
    * [Using Gulp to work on themes](#using-gulp-to-work-on-themes)
  * [Folder structure of the raw theme](#folder-structure-of-the-raw-theme)


## Customising or creating themes
Mahara uses subthemes (themes that extend the other themes) as a way of either customising a theme or creating your own theme from scratch. There are a couple of ways of working with subthemes based on how comfortable you are with working with Sass as a CSS pre-processor.

If you have no familiarity with Sass and want to stick with regular CSS, follow the [regular CSS instructions](#regular-css-only). Otherwise, skip to the [Sass and Gulp instructions](#sass-and-gulp).

### Regular CSS only (easiest)
If Sass and Gulp are more complex than you need, you can choose to work with regular CSS (or whatever other CSS tools you prefer).

1. Create a new __empty__ folder for your theme, and copy `themeconfig.php` from the `subthemestarter` theme folder. Your new folder should have no spaces or punctuation in the name.
2. In your new copy of `themeconfig.php`, change the following line which says to inherit the CSS from the the raw theme:
        $theme->overrideparentcss = true;
    so that it is set to false, like so:
        $theme->overrideparentcss = false;

3. Give your theme a name replacing "Sub Theme Starter kit" in the line
        $theme->displayname = 'Sub Theme Starter kit';
    with your own theme's name.
4. Create a new folder in your theme directory, `css`, and put a new file in it, `style.css`
5. Add your custom CSS to `css/style.css`

If you need to customise elements of the theme other than the styling, for example if you want to customise a particular template file, you may wish to create a `template` folder and copy particular template files from the `raw` theme into it so you can change them.

### Sass and Gulp (most flexible)
If you want more flexibility, and you are comfortable with Sass and Gulp (or are working with others who can help you out), then you can use the raw `.scss` files directly.

1. Create a copy of the `subthemestarter` folder and change its name. It should not have any spaces or punctuation. You should end up with a new folder with the following files in:
    * `gulpfile.js`
    * `package.json`
    * `themeconfig.php`
    * `sass/_custom.scss`
    * `sass/style.scss`
    * `sass/utilities/_theme-variables.scss`

2. You need to ensure that you override the parent theme's CSS by making sure that `$theme->overrideparentcss` is set to `true` in your new copy of `themeconfig.php`.

3. Customise the `displayname` to something more meaningful. This name is shown in the theme selection drop-down menu in the administration of your site as well as the user settings page if you allow your users to change their browse theme.

4. You can quickly change the basic colouring of the site by editing the values of the variables in `sass/utilities/_theme-variables.scss`. If you want to override more variables than just the basic theme colours (specific element colours, fonts, etc), you should copy the following files from the __raw__ folder into you new theme (put them in the` utilities` directory in the `sass` folder):
    * `sass/utilities/_bootstrap-variables.scss` - the original variables file from Bootstrap. Here you can assign your theme variables to Bootstrap's component-based variables.
    * `sass/utilities/_custom-variables.scss` - like the Bootstrap's variables file but for custom components.

    Now you need to edit your `sass/style.scss` to point at your theme's copies of these files. For any file that you have copied, you need to change the start of the path from `../../raw/sass/utilities/` to `utilities/`.

5. `_custom.scss` is optional. It is a place for any small bits of CSS that don't warrant creating a new file to include, or for custom overrides of the existing components. If you don't want to use it, delete your new copy and remember to remove the reference to it from `style.scss`!

6. You need to already have NodeJS set up to be able to complete this step. If you don't (or if you aren't sure that you do), follow the [NodeJS set-up](#nodejs) instructions in the [Mahara developers](#mahara-developer-guide) portion of this readme file first.

    From a terminal or command prompt navigate to your new theme, e.g.

        cd mahara/htdocs/theme/yourtheme

    Then run:

        npm install

    This will ensure you have all the dependencies mentioned in `gulpfile.js` in order to build your Sass into CSS.

7. Whenever you work on your theme, you need to run the Gulp program so that changes to your `.scss` are recompiled. From a terminal or command prompt, navigate to your theme folder (as in step 5) and run the Gulp command:

        gulp

    If you want to create your own component partials, you can make them in your `yourtheme/sass` directory and and import them in `style.scss`, e.g.

        @import "components/mycomponent";

    Note that you should give your filename an underscore prefix (e.g. `yourtheme/sass/components/_mycomponent.scss`), but you don't need to put in the underscore in the import statement.

#### Sass: Advanced customisation

There may be cases where you want to replace an entire component or feature file rather than just overriding the CSS. There are two files in the raw theme that act as indexes for all (non-variable) partials:

* `raw/sass/utilities/_bootstrap-index.scss`
* `raw/sass/utilities/_index.scss`

`_bootstrap-index.scss` is the index for all Bootstrap partials. If you want to exclude any part of Bootstrap from your custom theme, this is the file you will need to copy. Be aware that some parts of this may be used by other files in `raw`. Therefore, excluding a Bootstrap dependency may not be straightforward. For example, panels are heavily used throughout the `raw` theme.

`_index.scss` is an index to the modifications and additions made to Bootstrap for the `raw` theme. It is perhaps more likely you will want to replace components referenced from within this file.

1. Copy across the index file with the component you want to remove or replace.
2. Replace the reference to the raw index file in your theme's `style.scss` with the new location.
3. If needed (i.e. for `_index.scss`), update the import locations to point back at the `raw` theme (`@import "../typography/fonts"` will become `@import "../../../raw/sass/typography/fonts"`).
4. Remove the line that references the component you no longer need and replace it with your own version.

It's a bit of set-up work, but you will only need to do this once. Afterwards, you can replace any component you like.

### Templates
If a template in the `raw` theme (which your new theme will be descended from) doesn't suit you, simply copy it into a similar location in your theme (e.g. `yourtheme/templates/header/head.tpl`) and edit it. Your theme's copy of the file should be chosen over the original `raw` version.

### Images, fonts and javascript
If you want to use your own custom fonts or images, you can make `fonts` and `images` and `js` subfolders in your theme directory and then either override existing `raw` files by placing your own files with the same names into these folders or add your own items as desired.

## Mahara developer guide
Mahara's `raw` and `default` themes are based on the [Sass](http://sass-lang.com/) port of [Bootstrap](http://getbootstrap.com/) 3.3.1. We use [Gulp](http://gulpjs.com/) to turn Sass into CSS (via lib-sass), add vendor prefixes, and minimize.

### NodeJS
Gulp uses [Node.JS](https://nodejs.org/). If you haven't already, you may need to [install it](https://github.com/joyent/node/wiki/Installing-Node.js-via-package-manager). If you are unsure whether you have NodeJS installed or not, open a terminal (command prompt) and type:

    node -v

If you get a version number, then congrats, you have NodeJS!

If not, you can install it via the terminal:

Ubuntu/Debian Linux:

    sudo apt-get install nodejs nodejs-legacy npm

Other Linux:

    curl -sL https://deb.nodesource.com/setup_0.12 | sudo bash -
    sudo apt-get install nodejs

MacOSX (with homebrew):

    brew install node

Windows:
Hit the install button on the [Node website](https://nodejs.org/) and follow the instructions.

### Gulp

#### Setting up Gulp

If you haven't used Gulp before or don't have it installed, you'll need to globally install it using the Node Package Manager (npm) from a terminal or command prompt:

    npm install -g gulp

Because we are installing globally you may need to use `sudo` in front of this command to have the right permissions.

From the terminal navigate to the theme you are working on (e.g):

    cd mahara/htdocs/theme/raw

Install the dependencies:

    npm install

You only need to do this once for each theme. The files that npm installs will only be added to your local machine and won't be committed back to the repository if you work with the version control system Git and want to contribute your theme to the community using Git.

#### Using Gulp to work on themes

Now that Gulp is set up, __every time you want to work on a theme__, you will need to use a terminal to navigate to the place where the `gulpfile.js` is located for that theme, e.g.:

    cd mahara/htdocs/theme/raw

...and run

    gulp

This will watch all the `.scss` files in your current theme folder for changes and recompile your `.css`. If you work on multiple themes, you will need to run Gulp from within each theme folder.

### Folder structure of the `raw` theme

The `raw` theme's Sass has been split into partials based on the purpose they serve in the theme. A partial is a Sass file named with a leading underscore, e.g. `_partial.scss`. The underscore lets Sass know that the file is only a partial file and that it should not be generated into a CSS file. Sass partials are used with the `@import` directive.

* _components_ - blocks of CSS used throughout Mahara across many different features. These are often extensions to Bootstrap's core components. These include buttons, switches, list-groups, labels etc.

* _features_ - feature-specific CSS. We have tried to avoid these where possible and rely on collections of components, but there are some places where feature specific partials made sense: filebrowsers, comments, dashboard-widgets etc.

* _form_ - form  related components. Including base elements, alerts, form-groups etc.

* _layout_ - Sass-related to the laying out of a page. This includes navigation, custom column layouts and the panels found throughout Mahara, as well as specific page regions such as the header and footer.

* _lib_ - Sass related to JavaScript or smaller Sass libraries, e.g. font-awesome.

* _typography_ - components that serve a type-specific purpose. Styling of the base typography elements (paragrpahs, headings, lists, blockquotes etc.) and inclusion of font families.

* _utilities_ - Sass variables, utility classes, mixins and _index_ files (files that only include references to other Sass files/partials).

* `style.scss` - This is the core index file for the theme. It _includes_ a reference to each of the partial Sass files needed to build the theme. When Gulp is run, the resulting file (found in e.g. `raw/css`) will be named `style.css`, taking its name from the primary index file. `tinymce.scss` serves the same purpose, but for styles to be imported into TinyMCE's editing window. The other non-partial files found in the `raw` theme belong to libraries we have not been able to consolidate yet.

* `_custom.scss` - pieces of CSS that are too small to justify a component or a feature.

* `_shame.scss` - a file for hotfixes, hacks, and anything that a developer doesn't have time to test or implement properly. This can also be a place for browser or environment specific fixes that may be able to be removed at a later date.
