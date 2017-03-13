# Mahara Theming

This readme is split into two main sections, aimed at either [theme developers](#customising or creating themes) creating new themes or [Mahara developers](#mahara-developer-guide) doing maintenance or adding new features to existing themes.

*************************************************************************
* NOTE: In order to customise or create a new theme, a Mahara installed *
* via Git is used. If you have installed / upgraded via a zip / tar     *
* file, the relevant Gulp / SASS files will not be present.             *
*                                                                       *
* If you want a version with these files, but do not want to use Git,   *
* please go to https://git.mahara.org/mahara/mahara/tags and download   *
* the relevant release of the version of Mahara for which you want to   *
* create a theme.                                                       *
*************************************************************************

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
4. Create a new folder in your theme directory, `style`, and put a new file in it, `style.css`
5. Add your custom CSS to `style/style.css`

If you need to customise elements of the theme other than the styling, for example if you want to customise a particular template file, you may wish to create a `templates` folder and copy particular template files from the `raw` theme into it so you can change them.

### Sass and Gulp (most flexible)
If you want more flexibility, and you are comfortable with Sass and Gulp (or are working with others who can help you out), then you can use the raw `.scss` files directly.

1. Create a copy of the `subthemestarter` folder and change its name. It should not have any spaces or punctuation. You should end up with a new folder with the following files in:
    * `.gitignore`
    * `themeconfig.php`
    * `sass/_custom.scss`
    * `sass/style.scss`
    * `sass/utilities/_theme-variables.scss`

2. You need to ensure that you override the parent theme's CSS by making sure that `$theme->overrideparentcss` is set to `true` in your new copy of `themeconfig.php`.

3. Customise the `displayname` to something more meaningful. This name is shown in the theme selection drop-down menu in the administration of your site as well as the user settings page if you allow your users to change their browse theme.

4. You can quickly change the basic colouring of the site by editing the values of the variables in `sass/utilities/_theme-variables.scss`.

5. (optional) If you want to override more variables than just the basic theme colours (specific element colours, fonts, etc), you should copy the following files from the __raw__ folder into you new theme (put them in the` utilities` directory in the `sass` folder):
    * `sass/utilities/_bootstrap-variables.scss` - the original variables file from Bootstrap. Here you can assign your theme variables to Bootstrap's component-based variables.
    * `sass/utilities/_custom-variables.scss` - like the Bootstrap's variables file but for custom components.

    You need to edit your copies so that every mention of [`!default`](http://sass-lang.com/documentation/file.SASS_REFERENCE.html#variable_defaults_) is removed. This is so your new values properly override the default ones you inherit from raw.

    Now you need to edit your `sass/style.scss` to point at your theme's copies of these files. For any file that you have copied, uncomment the import statement in `style.scss` (___leave the import of `raw` versions___ - this means that if `raw` updates itself, your theme won't break). E.g. you go from:

        //@import "utilities/bootstrap-variables";
        //@import "utilities/custom-variables";

    to:

        @import "utilities/bootstrap-variables";
        @import "utilities/custom-variables";

6. (optional) `_custom.scss` is a place for any small bits of CSS that don't warrant creating a new file to include, or for custom overrides of the existing components. If you don't want to use it, delete your new copy and remember to remove the reference to it from `style.scss`!

7. You need to already have NodeJS set up to be able to complete this step. If you don't (or if you aren't sure that you do), follow the [NodeJS set-up](#nodejs) instructions in the [Mahara developers](#mahara-developer-guide) portion of this readme file first.

    From a terminal or command prompt navigate to the root Mahara folder (you are in the right place if you can see a file in the same folder called `package.json`). Then run:

        npm install

    This will ensure you have all the dependencies mentioned in `gulpfile.js` in order to build your Sass into CSS.

8. Whenever you work on your theme, you need to run the Gulp program so that changes to your `.scss` are recompiled. From a terminal or command prompt, the root Mahara folder (as in step 5) and run the Gulp command:

        gulp

    If you want to create your own component partials, you can make them in your `yourtheme/sass` directory and and import them in `style.scss`, e.g.

        @import "components/mycomponent";

    Note that you should give your filename an underscore prefix (e.g. `yourtheme/sass/components/_mycomponent.scss`), but you don't need to put in the underscore in the import statement.

### Templates
If a template in the `raw` theme (which your new theme will be descended from) doesn't suit you, simply copy it into a similar location in your theme (e.g. `yourtheme/templates/header/head.tpl`) and edit it. Your theme's copy of the file should be chosen over the original `raw` version.

### Images, fonts and javascript
If you want to use your own custom fonts or images, you can make `fonts` and `images` and `js` subfolders in your theme directory and then either override existing `raw` files by placing your own files with the same names into these folders or add your own items as desired.

## Mahara developer guide
Mahara's core themes are based on the [Sass](http://sass-lang.com/) port of [Bootstrap](http://getbootstrap.com/) 3.3.1. We use [Gulp](http://gulpjs.com/) to turn Sass into CSS (via lib-sass), add vendor prefixes, and minimize.

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

From the terminal navigate to Mahara's root folder and install the dependencies:

    npm install

You only need to do this once. The files that npm installs will only be added to your local machine, and won't be committed back to the repository if you work with the version control system Git and want to contribute your theme to the community using Git.

#### Using Gulp to work on themes

Now that Gulp is set up, __every time you want to work on a theme__, you will need to use a terminal to navigate to the root Mahara folder and run the following command:

    gulp

This will watch all the `.scss` files in every theme folder for changes and recompile your `.css`.

    gulp --production false

This will compile the css with debug comments in place.

#### Using Make to work on themes

Other useful commands are `make css` and `make css production=false` that can be run from the site's root directory.

    make css

This will compile all the different theme css at once into the most compressed format for each theme.

    make css production=false

This will compile all the different theme css at once but uncompressed and will add debug comments in place to make understanding of sass -> css workflow better.

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

### Theming components

#### Fixed header and site messages

Site messages such as masquerading information as well as database upgrades are displayed at the very top of the screen in Mahara pushing down all other content. Therefore, we need to ensure that the page header, which is in a fixed position, is set to be further down from the top of the view port.

In order to do that, we use JavaScript to detect the number of possible site messages, see `theme.js`). When there is no site message, a class of "no-site-messages" is added to the header. If there is a site message, a class of "message-count-1" is added and so on with more than one message. The classes added by JavaScript are used for styling the header and main navigation (main-nav) with site message(s), see `_site-messages.scss` in the form folder. We use the Sass @for directive to generate rules for up to 5 messages. It calculates the top position of the header and the margin of an element directly following the header, in this case it is "main-nav".

__IMPORTANT__ - If there are any changes to the header/navigation markup, you may need to amend the site message JavaScript and CSS. For example, if "main-nav" has been removed, then you will need to replace the ".main-nav" selector in the site messages styles with a class of an element that directly follows the header.
