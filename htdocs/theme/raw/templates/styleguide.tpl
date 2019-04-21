{include file="header.tpl"}
<span id="top"></span>

<p>{$description}</p>

<ul id="category-tabs" class="nav nav-tabs">
</ul>

{*
    examples go here,
    each one should be formatted like so:
<section data-markdown data-category="category-name-goes-here">
### Title of element
Description of element, this can include any markdown formatting, multiple paragraphs etc (optional).
```
<code for the element goes in between the triple backticks - there should only be one triple backtick part per section - Make sure you dont include any blank lines in the code>
```
</section>

(A Dwoo precompiler in styleguide.php copies the backtick sections to
display the rendered example, and unrendered example code, for each one.)

*}

<section data-markdown data-category="buttons">
### Add button
This button has padding on the right of the icon due to the plus class.
```
<button class="btn-secondary button btn">
    <span class="icon icon-plus icon-lg left" role="presentation"></span>
    {str tag=add section=mahara}
</button>
```
</section>

<section data-markdown data-category="buttons">
### Add button (small)
This button is used for adding items to a list or table, e.g. URLs and users.
```
<button class="btn-secondary btn-sm btn">
    <span class="icon icon-plus icon-lg" role="presentation"></span>
</button>
```
</section>

<section data-markdown data-category="buttons">
### Default button
This button is generally the one you use for most things.
```
<button class="btn-secondary button btn">
    Default button
</button>
```
</section>

<section data-markdown data-category="buttons">
### Primary button
This button is used for accepting something. It is used for the primary action on a page.
```
<button class="btn-primary button btn">
    Primary button
</button>
```
</section>

<section data-markdown data-category="buttons">
### Yes/no button
This pair of buttons is used for deleting or editing an item.
```
<div id="delete_submit_container" class=" default submitcancel form-group">
    <button type="submit" class="btn-secondary submitcancel submit btn" name="submit" tabindex="0">
        {str tag='yes'}
    </button>
    <input type="submit" class="btn-secondary submitcancel cancel" name="cancel_submit" tabindex="0" value="{str tag='no'}">
</div>
```
</section>

<section data-markdown data-category="buttons">
### Block edit buttons
This pair of buttons is used for editing or deleting a block item on a page.
```
<div class="card-header">
<span class="float-left btn-group btn-group-top">
    <button class="configurebutton btn btn-inverse btn-sm">
        <span class="icon icon-cog icon-lg"></span>
    </button>
    <button class="deletebutton btn btn-inverse btn-sm">
        <span class="icon icon-trash text-danger icon-lg"></span>
    </button>
</span>
</div>
```
</section>

<section data-markdown data-category="buttons">
### Button group
A group of buttons.
```
<div class=" btn-group">
    <a href="#" class="btn btn-secondary">
        Button group
    </a>
    <a href="#" class="btn btn-secondary">
        Button group
    </a>
</div>
```
</section>

<section data-markdown data-category="buttons">
### Button group top
A group of buttons aligned at the top. Note: The box around the buttons is only to show the placement of the buttons.
```
<div style="border: 1px solid #cfcfcf; min-height: 50px; padding-right: 10px; width: 500px;">
    <div class="btn-top-right btn-group btn-group-top">
        <a class="btn btn-secondary addpost" href="">
            Button group top
        </a>
        <a class="btn btn-secondary settings" href="">
            Button group top
        </a>
    </div>
    <div class="col-md-4">
        <h4>Context</h4>
    </div>
</div>
```
</section>

<section data-markdown data-category="buttons">
### Display page
This button is used to display a page you have just edited.
```
<button class="btn-secondary button btn">
    {str tag=displayview section=view}
    <span class="icon icon-arrow-circle-right right" role="presentation"></span>
</button>
```
</section>

<section data-markdown data-category="buttons">
### Text link
This type of button is normally used in lists. An example can be found when you view the "Image" block. It takes you to the artefact page.
```
<a href="" class="detail-link link-blocktype">
    <span class="icon icon-link" role="presentation" aria-hidden="true"></span>
    {str tag=Details section=artefact.file}
</a>
```
</section>

<section data-markdown data-category="buttons">
### Switchbox
Switchboxes are used for Yes/No, On/Off or other true/false type fields. They are used in place of regular check boxes.
```
<div class="form-switch ">
    <div class="switch " style="width:61px">
        <input type="checkbox" class="switchbox" name="dropdownmenu" tabindex="0">
        <label class="switch-label" for="siteoptions_dropdownmenu" aria-hidden="true">
            <span class="switch-inner"></span>
            <span class="switch-indicator"></span>
            <span class="state-label on">{str tag=switchbox.yes section=pieforms}</span>
            <span class="state-label off">{str tag=switchbox.no section=pieforms}</span>
        </label>
    </div>
</div>
```
</section>

<section data-markdown data-category="buttons">
### More options button
This button is used to show there are more options available. An example can be found on a regular portfolio page where the items of the "Watchlist" and "Objectionable content" are available via the "More options" button. Note: Styles are only added for layout here in the style guide.
```
<div class="btn-group" style="margin-left: 200px;">
    <button type="button" class="btn btn-secondary dropdown-toggle" data-toggle="dropdown" title="{str tag='moreoptions'}" aria-expanded="false">
        <span class="icon icon-ellipsis-h icon-lg" role="presentation" aria-hidden="true"></span>
        <span class="sr-only">{str tag=moreoptions}</span>
    </button>
    <ul class="dropdown-menu dropdown-menu-right" role="menu">
        <li class="dropdown-item">
            <a id="toggle_watchlist_link" class="watchlist" href="">
                <span class="icon icon-eye left" role="presentation" aria-hidden="true"></span>
                {str tag=addtowatchlist section=view}
            </a>
        </li>
        <li class="dropdown-item">
            <a id="objection_link" href="#" data-toggle="modal" data-target="#report-form">
                <span class="icon icon-lg icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                {str tag=reportobjectionablematerial}
            </a>
        </li>
    </ul>
</div>
```
</section>

<section data-markdown data-category="navigation">
### Main navigation
The Mahara navigation is displayed in collapsible format with drop-down menus. The navigation is
split up into main navigation, administration navigation and user navigation each having their own
icons and drop-down menus.

Please see <a class="follow" href="https://wiki.mahara.org/wiki/Customising/Themes/17.04">Mahara Wiki</a> for more
details on navigation styles.

<!-- Styles to fix searchbar positioning - used in this styleguide only -->
<style>
section .navbar-main .navbar-collapse.nav-one,
section .navbar-main .navbar-collapse.nav-two,
section .navbar-main .navbar-collapse.nav-three {
    position: relative;
    top: 0;
    right: 0;
}
section .navbar-form.navbar-collapse.search-form {
    position: static;
    top: auto;
    right: auto;
}
@media only screen and (max-width: 768px) {
    section .navbar-form.navbar-collapse.search-form {
        position: initial;
    }
}
@media (min-width: 768px) {
    section .nav-toggle-area .user-icon {
        left: 0;
    }
}
</style>
```
<div class="row">
    <div class="navbar-default navbar-main float-right">
        <div class="nav-toggle-area">
            <!-- Nav One Button -->
            <button class="nav-one-toggle navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".nav-one" aria-expanded="false" aria-controls="nav-one" title="Nav one">
                <span class="sr-only">Show nav one</span>
                <span class="icon icon-bars icon-lg" role="presentation" aria-hidden="true"></span>
            </button>
            <!-- Nav Two Button -->
            <button class="nav-two-toggle navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".nav-two" aria-expanded="false" aria-controls="nav-two" title="Nav two">
                <span class="sr-only">Show nav two</span>
                <span class="icon icon-wrench icon-large" role="presentation" aria-hidden="true"></span>
            </button>
            <!-- Nav Three Button and icon -->
            <a href="" class="user-icon" title="Profile page">
                <img src="{$WWWROOT}theme/raw/images/no_userphoto25.png">
            </a>
            <button class="user-toggle nav-three-toggle navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".nav-three" aria-expanded="false" aria-controls="nav-three" title="Nav three">
                <span class="sr-only">Show nav three</span>
                <span class="icon icon-chevron-down collapsed"></span>
            </button>
            <!-- Hide Search When on Desktop -->
            <button class="search-toggle navbar-toggle collapsed" type="button" data-toggle="collapse" data-target=".navbar-form" aria-expanded="false" aria-controls="navbar-form">
                <span class="icon icon-search icon-lg" role="presentation" aria-hidden="true"></span>
                <span class="nav-title sr-only">{str tag=showsearch}</span>
            </button>
        </div>
        <!-- Nav One -->
        <nav id="nav-one" class="nav collapse navbar-collapse nav-one" role="tabcard">
           <ul id="navone" class="nav navbar-nav">
              <li>
                  <a href="">Link 1</a>
              </li>
              <li>
                 <a href="">Link 2</a>
                 <button type="button" class="navbar-showchildren navbar-toggle dropdown-toggle collapsed" data-toggle="collapse" data-parent="#navone" data-target="#subnavone">
                     <span class="icon icon-chevron-down" role="presentation" aria-hidden="true"></span>
                     <span class="nav-title sr-only">Link 2</span>
                 </button>
                 <ul id="subnavone" class=" collapse child-nav" role="menu">
                    <li><a href="">Sublink 2</a></li>
                    <li><a href="">Sublink 2</a></li>
                 </ul>
              </li>
              <li>
                 <a href="">Link 2</a>
                 <button type="button" class="navbar-showchildren navbar-toggle dropdown-toggle collapsed" data-toggle="collapse" data-parent="#navone" data-target="#subnavtwo">
                     <span class="icon icon-chevron-down" role="presentation" aria-hidden="true"></span>
                     <span class="nav-title sr-only">Link 2</span>
                 </button>
                 <ul id="subnavtwo" class=" collapse child-nav" role="menu">
                    <li><a href="">Sublink 1</a></li>
                    <li><a href="">Sublink 2</a></li>
                 </ul>
              </li>
              <li>
                 <a href="">Link 4</a>
                 <button type="button" class="navbar-showchildren navbar-toggle dropdown-toggle collapsed" data-toggle="collapse" data-parent="#navone" data-target="#subnavthree">
                     <span class="icon icon-chevron-down" role="presentation" aria-hidden="true"></span>
                     <span class="nav-title sr-only">Link 4</span>
                 </button>
                 <ul id="subnavthree" class=" collapse child-nav" role="menu">
                    <li><a href="">Sublink 1</a></li>
                    <li><a href="">Sublink 2</a></li>
                 </ul>
              </li>
           </ul>
        </nav>
        <!-- Nav Two -->
        <nav id="nav-two" class="nav navbar-collapse nav-two collapse" role="tabcard" aria-expanded="false">
            <ul id="navtwo" class="nav navbar-nav">
                <li>
                    <a href="">Link 1</a>
                    <button type="button" class="navbar-showchildren navbar-toggle dropdown-toggle collapsed" data-toggle="collapse" data-parent="#navtwo" data-target="#subnavfour">
                        <span class="icon icon-chevron-down" role="presentation" aria-hidden="true"></span>
                        <span class="nav-title sr-only">Link 1</span>
                    </button>
                    <ul id="subnavfour" class=" collapse child-nav" role="menu">
                        <li><a href="">Sublink 1</a></li>
                        <li><a href="">Sublink 2</a></li>
                    </ul>
                </li>
                <li>
                    <a href="">Link 2</a>
                    <button type="button" class="navbar-showchildren navbar-toggle dropdown-toggle collapsed" data-toggle="collapse" data-parent="#navtwo" data-target="#subnavfive">
                        <span class="icon icon-chevron-down" role="presentation" aria-hidden="true"></span>
                        <span class="nav-title sr-only">Link 2</span>
                    </button>
                    <ul id="subnavfive" class=" collapse child-nav" role="menu">
                        <li><a href="">Sublink 1</a></li>
                        <li><a href="">Sublink 2</a></li>
                    </ul>
                </li>
                <li>
                    <a href="">Link 3</a>
                    <button type="button" class="navbar-showchildren navbar-toggle dropdown-toggle collapsed" data-toggle="collapse" data-parent="#navtwo" data-target="#subnavsix">
                        <span class="icon icon-chevron-down" role="presentation" aria-hidden="true"></span>
                        <span class="nav-title sr-only">Link 3</span>
                    </button>
                    <ul id="subnavsix" class=" collapse child-nav" role="menu">
                        <li><a href="">Sublink 1</a></li>
                        <li><a href="">Sublink 2</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- Nav Three -->
        <nav id="nav-three" class=" nav collapse navbar-collapse nav-three" role="tabcard">
            <ul id="navthree" class="nav navbar-nav">
                <li class="has-icon">
                    <a href="">
                        <span class="icon icon-user" role="presentation" aria-hidden="true"></span>
                        <span class="nav-title">Link 1</span>
                    </a>
                </li>
                <li class="has-icon dropdown-item">
                    <a href="">
                        <span class="icon icon-cogs" role="presentation" aria-hidden="true"></span>
                        <span class="nav-title">Link 2</span>
                    </a>
                    <button type="button" class="navbar-showchildren navbar-toggle dropdown-toggle collapsed" data-toggle="collapse" data-parent="#navuser" data-target="#subnavseven">
                        <span class="icon icon-chevron-down" role="presentation" aria-hidden="true"></span>
                        <span class="nav-title sr-only">Link 2</span>
                    </button>
                    <ul id="subnavseven" class=" collapse child-nav" role="menu">
                        <li><a href="">Sublink 1</a></li>
                        <li><a href="">Sublink 2</a></li>
                    </ul>
                </li>
                <li class="has-icon dropdown-item">
                    <a href="">
                        <span class="icon icon-envelope" role="presentation" aria-hidden="true"></span>
                        <span class="navcount unreadmessagecount">Link 3</span>
                    </a>
                </li>
                <li class="has-icon">
                    <a href="">
                        <span class="icon icon-sign-out" role="presentation" aria-hidden="true"></span>
                        <span class="nav-title">Link 4</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>
```
</section>

<section data-markdown data-category="navigation">
### Pagination
The pagination has "Previous" and "Next" buttons.
```
<ul class="pagination pagination-sm">
    <li class="page-item"><span class="page-link">«<span class="sr-only">{str tag=prevpage section=collection}</span></span></li>
    <li class="active page-item"><span class="page-link">1</span></li>
    <li class="page-item"><a class="page-link" title="" href="link">2</a></li>
    <li class="page-item"><a class="page-link" title="Next page" href="link"> »<span class="sr-only">{str tag=nextpage section=collection}</span></a></li>
</ul>
```
</section>

<section data-markdown data-category="navigation">
### Pagination with "Results per page" drop-down menu
The pagination has "Previous" and "Next" buttons buttons and a drop-down menu to select how many results are shown per page. An example can be found on the pages overview page when you have more than 10 pages.
```
<div>
    <div class="pagination-wrapper">
        <div class="lead text-small results float-right">
            11 {str tag=results}
        </div>
        <ul class="pagination pagination-sm">
            <li class="page-item">
                <span class="page-link">«<span class="sr-only">{str tag=prevpage section=collection}</span></span>
            </li>
            <li class="active page-item">
                <span class="page-link">1</span>
            </li>
            <li class="page-item">
                <a class="page-link" href="" title="">2</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="" title="Next page">
                    »
                    <span class="sr-only">{str tag=nextpage section=collection}</span>
                </a>
            </li>
        </ul>
        <form class="form-pagination js-pagination form-inline pagination-page-limit dropdown" action="/view/index.php?orderby=atoz" method="POST">
            <label for="setlimitselect" class="set-limit">
                {str tag=maxitemsperpage1}
            </label>
            <span class="picker form-control-sm">
                <select id="setlimitselect" class="js-pagination form-control-sm select form-control" name="limit">
                    <option value="1"> 1 </option>
                    <option value="10" selected="selected"> 10 </option>
                    <option value="20"> 20 </option>
                    <option value="50"> 50 </option>
                    <option value="100"> 100 </option>
                    <option value="500"> 500 </option>
                </select>
            </span>
            <input class="currentoffset" type="hidden" name="offset" value="0">
            <input class="pagination js-hidden d-none" type="submit" name="submit" value="Change">
        </form>
    </div>
</div>
```
</section>
<section data-markdown data-category="navigation">
### Navigation tabs
These are tabs to switch between pages within one section, for example in the "Profile" under "Content". The tabs navigation style is used when the entire section has only one "Save" button.
```
<ul class="nav nav-tabs" role="tablist">
    <li role="presentation">
        <a href="#" role="tab" data-toggle="tab" aria-expanded="true" class="active">Tab 1</a>
    </li>
    <li role="presentation">
        <a href="#" role="tab" data-toggle="tab" aria-expanded="false">Tab 2</a>
    </li>
    <li role="presentation">
        <a href="#" role="tab" data-toggle="tab" aria-expanded="false">Tab 3</a>
    </li>
    <li role="presentation">
        <a href="#" role="tab" data-toggle="tab" aria-expanded="false">Tab 4</a>
    </li>
</ul>
```
</section>

<section data-markdown data-category="navigation">
### Arrow bar
This style of tabs is used for third-level navigation in areas where each page within this section is saved separately, for example in a group, in the résumé or in the web services configuration. When the primary school theme is selected, each arrow bar can be a different colour. This is determined by the class in the very first div. The 2 choices are 'group' (green) and 'resume' (red). There is a default option (blue) when no extra class is given.
```
<div class="arrow-bar group">
    <span class="arrow d-none d-md-block">
        <span class="text">
            Tabs
        </span>
    </span>
    <span class="right-text">
        <ul class="nav nav-pills nav-inpage">
            <li class=" current-tab active">
                <a class=" current-tab" href="#">
                    Tab 1
                    <span class="accessible-hidden sr-only">({str tag=tab} {str tag=selected})</span>
                </a>
            </li>
            <li class=" current-tab">
                <a class=" current-tab" href="#">
                    Tab 2
                    <span class="accessible-hidden sr-only">({str tag=tab})</span>
                </a>
            </li>
            <li class=" current-tab ">
                <a class=" current-tab" href="#">
                    Tab 3
                    <span class="accessible-hidden sr-only">({str tag=tab})</span>
                </a>
            </li>
            <li class=" current-tab">
                <a class=" current-tab" href="#">
                    Tab 4
                    <span class="accessible-hidden sr-only">({str tag=tab})</span>
                </a>
            </li>
        </ul>
    </span>
</div>
```
</section>


<section data-markdown data-category="cards">
### card
A basic card.
```
<div class="card">
    <h3 class="card-header has-link">
        <a href="#">Basic card</a>
    </h3>
    <div class="tagblock card-body">
        <a title="1 item" href="#" class="tag">Mahara</a>
        <a title="1 item" href="#" class="tag">{str tag=myportfolio}</a>
    </div>
</div>
```
</section>

<section data-markdown data-category="cards">
### Delete card
A delete card.
```
<div class="card bg-danger view-container">
    <h2 class="card-header">{str tag=delete}</h2>
    <div class="card-body">
        <p><strong>{str tag=Title}</strong></p>
        <p>{str tag=deleteinstitutionconfirm section=admin}</p>
        <div class=" default submitcancel form-group">
            <button type="submit" class="btn-secondary submitcancel submit btn" tabindex="0">{str tag='yes'}</button>
            <input type="submit" class="btn-secondary submitcancel cancel" tabindex="0" value="{str tag='no'}">
        </div>
    </div>
</div>
```
</section>

<section data-markdown data-category="cards">
### Side card
A side card is used in the sideblock area, e.g. on the dashboard for "Online users".
```
<div class="col-md-3 sidebar">
    <div class="card">
        <h3 class="card-header">
            Side card
            <br>
            <span  class="text-small text-midtone">({str tag=description})</span>
        </h3>
        <ul class="list-group">
            <li class="list-group-item list-unstyled list-group-item-link">
                <a>
                    Side card link
                </a>
            </li>
        </ul>
        <a href="" class="card-footer text-small">
            Side card footer
            <span class="icon icon-arrow-circle-right float-right"></span>
        </a>
    </div>
</div>
```
</section>

<section data-markdown data-category="cards">
### Side card (no footer)
A side card without a footer. An examples is the "Tags" sideblock on the dashboard.
```
<div class="col-md-3 sidebar">
    <div id="sb-tags">
        <div class="card">
            <h3 class="card-header has-link">
                <a href="">Side card<span class="icon icon-arrow-right float-right" role="presentation" aria-hidden="true"></span></a>
            </h3>
            <div class="tagblock card-body">
                <div class="no-results-small text-small">Lorem ipsum</div>
            </div>
        </div>
    </div>
</div>
```
</section>

<section data-markdown data-category="cards">
### Dropdown card
A dropdown card.
```
<div class="last form-group collapsible-group">
    <fieldset class="pieform-fieldset last collapsible">
        <legend>
            <h4>
                <a href="#dropdown" data-toggle="collapse" aria-expanded="false" aria-controls="dropdown" class="collapsed">
                    Drop-down
                    <span class="icon icon-chevron-down collapse-indicator right float-right"> </span>
                </a>
            </h4>
        </legend>
        <div class="fieldset-body collapse " id="dropdown">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut lobortis metus orci, in posuere nulla tempus quis. Curabitur aliquet, turpis sit amet fermentum euismod, nisl massa posuere nulla, sed tempor lorem magna a urna. In porttitor lobortis mauris, et tristique ipsum hendrerit a. In et quam fringilla, accumsan enim et, fermentum diam. Ut risus lectus, feugiat eget dolor sed, fringilla fringilla nulla. Vivamus laoreet mollis ex ut pulvinar. Praesent ultrices enim sem, vel mattis tellus feugiat et.
        </div>
</fieldset>
</div>
```
</section>

<section data-markdown data-category="cards">
### Blocks drop-down card
This type of drop-down card is used in blocks, for example the "Inbox" block.
```
<div class="bt-inbox card card-secondary clearfix collapsible">
    <h3 class="title card-header js-heading">
        <a data-toggle="collapse" href="#target" aria-expanded="true" class="outer-link"></a>
        Blocks drop-down
        <span class="icon icon-chevron-up collapse-indicator float-right inner-link" role="presentation" aria-hidden="true"></span>
    </h3>
    <div class="block collapse show" id="target" aria-expanded="true">
        <div class="inboxblock list-group">
            <div class="has-attachment card collapsible list-group-item">
                <a class="collapsed link-block" data-toggle="collapse" href="#item1" aria-expanded="false">
                    <span class="icon icon-university text-default left" role="presentation" aria-hidden="true"></span>
                    Item 1
                    <span class="icon icon-chevron-down collapse-indicator float-right text-small" role="presentation" aria-hidden="true"></span>
                </a>
                <div class="collapse" id="item1">
                    <p class="content-text">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus at turpis commodo, pretium turpis ac, porttitor dolor.
                    </p>
                </div>
            </div>
            <div class="has-attachment card collapsible list-group-item">
                <a class="collapsed link-block" data-toggle="collapse" href="#item2" aria-expanded="false">
                    <span class="icon icon-wrench text-default left" role="presentation" aria-hidden="true"></span>
                    Item 2
                    <span class="icon icon-chevron-down collapse-indicator float-right text-small" role="presentation" aria-hidden="true"></span>
                </a>
                <div class="collapse" id="item2">
                    <p class="content-text">
                        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus at turpis commodo, pretium turpis ac, porttitor dolor.
                    </p>
                </div>
            </div>
        </div>
        <div class="artefact-detail-link">
            <a class="link-blocktype last" href="">
                <span class="icon icon-arrow-circle-right" role="presentation" aria-hidden="true"></span>
                {str tag=More section=blocktype.inbox}
            </a>
        </div>
    </div>
</div>
```
</section>

<section data-markdown data-category="cards">
### Block layout
This is the general layout of blocks. An example of this being used is the 'Latest changes I can view' block on the dashboard.
```
<div class="bt-newviews card clearfix">
    <h3 class="title card-header js-heading">Block</h3>
    <div class="block">
        <div class="list-group">
            <div class="list-group-item">
                <h4 class="list-group-item-heading text-inline">
                    <a href="">Page 1</a>
                </h4>
                <span class="text-small text-midtone"></span>
                <div class="groupuserdate text-small">
                    <a href="" class="text-link">Admin User (admin)</a>
                    <span class="postedon text-midtone"> -
                        {str tag=Created} 31 March 2016 </span>
                </div>
            </div>
            <div class="list-group-item">
                <h4 class="list-group-item-heading text-inline">
                    <a href="">Page 2</a>
                </h4>
                <span class="text-small text-midtone"></span>
                <div class="groupuserdate text-small">
                    <a href="" class="text-link">Admin User (admin)</a>
                    <span class="postedon text-midtone"> -
                        {str tag=Updated} 31 March 2016 </span>
                </div>
            </div>
        </div>
    </div>
</div>
```
</section>

<section data-markdown data-category="cards">
### Collection navigation
```
<div class="collection-nav">
    <button type="button" class="btn btn-secondary prevpage">
        <span class="icon left icon-chevron-left" role="presentation" aria-hidden="true"></span>
        {str tag=prevpage section=collection}
    </button>
    <button type="button" class="btn btn-secondary nextpage">
        {str tag=nextpage section=collection}
        <span class="icon right icon-chevron-right" role="presentation" aria-hidden="true"></span>
    </button>
    <h2>{str tag=Collection section=collection}: Collection 1</h2>
    <p class="navlabel">{str tag=navtopage section=collection}</p>
    <nav class="custom-dropdown dropdown">
        <ul id="pagelist" class="collapse">
            <li>
                <a href="" data-index="0">Page 1</a>
            </li>
            <li>
                <span data-index="1">Page 2</span>
            </li>
            <li>
                <a href="" data-index="2">Page 3</a>
            </li>
        </ul>
        <span class="picker form-control" tabindex="0" data-toggle="collapse" data-target="#pagelist" aria-expanded="false" role="button" aria-controls="#pagelist">{str tag=viewingpage section=collection}
            <span id="currentindex" data-currentindex="1">2</span>
            /3
        </span>
    </nav>
</div>
```
</section>

<section data-markdown data-category="cards">
## Page card
This card is used to show a page.
```
<div class="card-quarter card-view">
    <div class="card">
        <h3 class="card-header has-link">
            <a class="title-link title" href="" title="Dashboard page">Dashboard page</a>
        </h3>
        <div class="card-body">
            <div class="detail">
                <div class="detail">Your dashboard page is what you see on the homepage when you first log in. Only you have access to it.</div>
            </div>
        </div>
        <div class="card-footer">
            <div class="page-access"></div>
            <div class="page-controls">
                <a href="#" class="dropdown-toggle moremenu btn btn-link" data-toggle="dropdown" aria-expanded="false" title="{str tag='moreoptions'}">
                    <span class="icon icon-chevron-down open-indicator" role="presentation" aria-hidden="true"></span>
                    <span class="icon icon-ellipsis-v close-indicator" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">More options for "Dashboard page"</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li class="dropdown-item">
                        <a href="" title="Edit content and layout">
                        <span class="icon icon-pencil left" role="presentation" aria-hidden="true"></span>
                        <span class="link-text">Edit</span>
                        <span class="sr-only">Edit "Dashboard page"</span>
                        </a>
                    </li>
                    <li class="view-details dropdown-item">
                        Created 18 Jan 2017,  9:02
                        <br>
                        Modified 15 Jan 2018, 11:29
                        <br>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
```
</section>

<section data-markdown data-category="cards">
## Submitted Page card
This card is used to show a submitted page.
<style>
/* Styles for submitted and collection cards */
section .card-quarter:nth-child(4n) .page-access .dropdown-menu {
    left: 0;
}
</style>
```
<div class="card-quarter card-view">
    <div class="card card bg-warning">
        <h3 class="card-header has-link">
            <a class="title-link title" href="" title="Dashboard page">Unnamed page</a>
        </h3>
        <div class="card-body">
            <div class="detail">
                <div class="detail">Lorem ipsum</div>
            </div>
        </div>
        <div class="card-footer">
            <div class="page-access">
                <a href="#" class="dropdown-toggle btn btn-link" data-toggle="dropdown" aria-expanded="false" title="Manage access">
                    <span class="icon icon-chevron-down open-indicator" role="presentation" aria-hidden="true"></span>
                    <span class="icon icon-unlock-alt close-indicator" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">Access rules for "Unnamed page"</span>
                </a>
                <ul class="dropdown-menu" role="menu">
                    <li class="dropdown-item">
                        <a class="seperator" href="">
                            <span class="icon icon-unlock-alt left" role="presentation" aria-hidden="true"></span>
                            <span class="link-text">Manage access</span>
                            <span class="sr-only">Manage access for "Unnamed page"</span>
                        </a>
                    </li>
                    <li class="dropdown-item">
                        <a href="">
                            <span class="icon icon-users left" role="presentation" aria-hidden="true"></span>
                            <span class="link-text">group (Submitted)</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="page-controls">
                <a href="#" class="dropdown-toggle moremenu btn btn-link" data-toggle="dropdown" aria-expanded="false" title="{str tag='moreoptions'}">
                    <span class="icon icon-chevron-down open-indicator" role="presentation" aria-hidden="true"></span>
                    <span class="icon icon-ellipsis-v close-indicator" role="presentation" aria-hidden="true"></span>
                    <span class="sr-only">More options for "Dashboard page"</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li class="dropdown-item">
                        <a href="" title="Edit content and layout">
                        <span class="icon icon-pencil left" role="presentation" aria-hidden="true"></span>
                        <span class="link-text">Edit</span>
                        <span class="sr-only">Edit "Unnamed page"</span>
                        </a>
                    </li>
                    <li class="view-details dropdown-item">
                        Created 18 Jan 2017,  9:02
                        <br>
                        Modified 15 Jan 2018, 11:29
                        <br>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
```
</section>


<section data-markdown data-category="cards">
## Collections card
This card is used to show a collection.
<style>
/* Styles for submitted and collection cards */
section .card-quarter:nth-child(4n) .page-access .dropdown-menu {
    left: 0;
}
</style>
```
<div class="card-quarter card-collection">
    <div class="card">
        <h3 class="card-header has-link">
            <a class="title-link title" href="" title="collection uno">
            collection
            </a>
        </h3>
        <div class="card-body">
            <div class="detail"></div>
        </div>
        <div class="card-footer">
            <div class="page-access">
                <a href="#" class="dropdown-toggle btn btn-link" data-toggle="dropdown" aria-expanded="false" title="Manage access">
                <span class="icon icon-chevron-down open-indicator" role="presentation" aria-hidden="true"></span>
                <span class="icon icon-unlock-alt close-indicator" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">Access rules for "collection"</span>
                </a>
                <ul class="dropdown-menu" role="menu">
                    <li class="dropdown-item">
                        <a class="seperator" href="">
                        <span class="icon icon-unlock-alt left" role="presentation" aria-hidden="true"></span>
                        <span class="link-text">Manage access</span>
                        <span class="sr-only">Manage access for "collection"</span>
                        </a>
                    </li>
                    <li class="dropdown-item">
                        <a href="">
                        <span class="icon icon-globe left" role="presentation" aria-hidden="true"></span>
                        <span class="link-text">Public</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="page-controls">
                <a href="#" class="dropdown-toggle moremenu btn btn-link" data-toggle="dropdown" aria-expanded="false" title="{str tag='moreoptions'}">
                <span class="icon icon-chevron-down open-indicator" role="presentation" aria-hidden="true"></span>
                <span class="icon icon-ellipsis-v close-indicator" role="presentation" aria-hidden="true"></span>
                <span class="sr-only">More options for "collection"</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                    <li class="dropdown-item">
                        <a href="" title="Manage pages">
                        <span class="icon icon-list left" role="presentation" aria-hidden="true"></span>
                        <span class="link-text">Manage</span>
                        <span class="sr-only">Manage pages in "collection"</span>
                        </a>
                    </li>
                    <li class="dropdown-item">
                        <a href="" title="Edit title and description">
                        <span class="icon icon-pencil left" role="presentation" aria-hidden="true"></span>
                        <span class="link-text">Edit</span>
                        <span class="sr-only">Edit "collection"</span>
                        </a>
                    </li>
                    <li class="dropdown-item">
                        <a href="" title="Delete collection">
                        <span class="icon icon-trash text-danger left" role="presentation" aria-hidden="true"></span>
                        <span class="link-text">Delete</span>
                        <span class="sr-only">Delete "collection"</span>
                        </a>
                    </li>
                    <li class="view-details dropdown-item">
                        Created 30 Jan 2017,  8:09
                        <br>
                        Modified 17 Jan 2018, 15:26
                        <br>
                    </li>
                </ul>
            </div>
            <div class="collection-list" title="1 page in collection">
                <a href="#" class="dropdown-toggle btn btn-link" data-toggle="dropdown" aria-expanded="false">
                <span class="icon icon-chevron-down open-indicator" role="presentation" aria-hidden="true"></span>
                <span class="page-count">1</span>
                <span class="icon icon-file close-indicator" role="presentation" aria-hidden="true">
                </span>
                </a>
                <ul class="dropdown-menu" role="menu">
                    <li class="dropdown-item">
                        <a href="">
                        <span class="icon icon-file-o left" role="presentation" aria-hidden="true"></span>
                        <span class="link-text">Untitled page</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="collection-stack "></div>
</div>
```
</section>

<section data-markdown data-category="alerts">
### Warning alert
This is used to indicate that you should make the recommended change.
```
<div class="admin-warning alert alert-warning">
    <h3>Warning</h3>
    <span class="icon icon-lg icon-exclamation-triangle left" role="presentation" aria-hidden="true"></span> This is a warning alert.
</div>
```
</section>

<section data-markdown data-category="alerts">
### Danger alert
Used to show that there is an error, which must be fixed before you can continue.
```
<div class="alert alert-danger">
    <h3>Danger</h3>
    <span class="icon icon-lg icon-times text-danger left" role="presentation" aria-hidden="true"></span>This is a danger alert.
</div>
```
</section>

<section data-markdown data-category="alerts">
### Success alert
Used to show that an action was successful.
```
<div class="alert alert-success">
    <h3>Success</h3>
    <span class="icon icon-lg icon-check text-success left" role="presentation" aria-hidden="true"></span> This is a success alert.
</div>
```
</section>

<section data-markdown data-category="alerts">
### Info alert
Used to show information about Mahara. Usually, this is only shown to administrators.
```
<div class="alert alert-info">
    <h3>Info</h3>
    <span class="icon icon-lg icon-info-circle left" role="presentation" aria-hidden="true"></span> This is a info alert.
</div>
```
</section>

<section data-markdown data-category="modals">
### Modal docked
A slide-out modal. This is used to show a block's configuration for example.
```
<button type="button" class="btn btn-primary" data-toggle="modal-docked" data-target="#modal-docks">
    Launch demo modal
</button>
<div class="modal modal-docked modal-docked-right modal-shown closed" id="modal-docks" tabindex="-1" role="dialog" aria-labelledby="#modal-docks-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button class="deletebutton close" data-dismiss="modal-docked" aria-label="{str tag=Close}">
                  <span class="times">×</span>
                  <span class="sr-only">{str tag=Close}</span>
                </button>
                <h4 class="modal-title blockinstance-header text-inline modal-docks-title">Modal heading</h4>
            </div>
            <div class="modal-body">
                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer pretium, magna in tempor accumsan, augue lacus pretium urna, fringilla malesuada orci eros iaculis dui. Donec blandit urna sed condimentum ullamcorper. Vestibulum commodo hendrerit suscipit. Etiam eget fermentum risus. Etiam faucibus elit at tortor molestie rutrum at nec ex. Mauris id elit sed neque rhoncus iaculis. Maecenas id dui turpis.
            </div>
        </div>
    </div>
</div>
```
</section>

<section data-markdown data-category="modals">
### Modal
A fold down modal. This is typially used to report objectionable content.
```
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#report-form">
    Launch demo modal
</button>
<div class="modal fade" id="report-form" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="{str tag=Close}">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title">
                    <span class="icon icon-lg icon-flag text-danger left" role="presentation" aria-hidden="true"></span>
                    Report objectionable material
                </h4>
            </div>
            <div class="modal-body">
                <form class="pieform" name="objection_form" method="post" id="objection_form">
                    <div class="form-group requiredmarkerdesc">
                        Fields marked by '*' are required.
                    </div>
                    <div id="objection_form_message_container" class="under-label required textarea form-group">
                        <label for="objection_form_message">
                            Complaint
                            <span class="requiredmarker">*</span>
                        </label>
                        <textarea rows="5" cols="80" class="form-control under-label required textarea resizable" id="objection_form_message" name="message" tabindex="0" aria-required="true"></textarea>
                    </div>
                    <div id="objection_form_submit_container" class=" default submitcancel form-group">
                        <button type="submit" class="btn-secondary submitcancel submit btn" data-confirm="Are you sure you wish to report this page as containing objectionable material?" id="objection_form_submit" name="submit" tabindex="0">
                            Notify administrator
                        </button>
                        <input type="submit" class="btn-secondary submitcancel cancel" id="cancel_objection_form_submit" name="cancel_submit" tabindex="0" value="Cancel">
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
```
</section>


<section data-markdown data-category="tables">
### Table
This is a normal table, e.g. found in a forum.
```
<table class="table fullwidth table-padded">
    <thead>
        <tr>
            <th>Column 1</th>
            <th>Column 2</th>
            <th class="text-center">Column 3</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <h3 class="title">
                    <a href="">Item 1</a>
                </h3>
                <div class="forumpath text-small text-midtone">
                    Some description information <a href="" class="topicforum  text-midtone">Description link</a>
                </div>
            </td>
            <td>
                <p class="postdetail">
                    Item 2
                </p>
            </td>
            <td class="text-center">Item 3</td>
        </tr>
    </tbody>
</table>
```
</section>

<section data-markdown data-category="tables">
### Striped table
A striped table is most frequently found in the administration area where tables can be quite long and contain a lot of data.
```
<table class="fullwidth table table-striped">
    <thead>
        <tr>
            <th>Column 1</th>
            <th class="center">Column 2</th>
            <th class="center">Column 3</th>
            <th>Column 4</th>
            <th>Column 5</th>
            <th><span class="accessible-hidden sr-only">{str tag=edit}</span></th>
        </tr>
    </thead>
    <tbody>
        <tr class="r0">
            <td><a href="">Item 1</a></td>
            <td class="center">3</td>
            <td class="center">1</td>
            <td>Item 1 info</td>
            <td>Item 1 stuff</td>
            <td class="right">
                <div class="btn-group">
                    <a class="btn btn-secondary btn-sm" title="{str tag=groupmanage section=admin}" href="">
                        <span class="icon icon-cog icon-lg"></span><span class="sr-only">{str tag=groupmanagespecific section=admin arg1='Item 1'}</span>
                    </a>
                    <a class="btn btn-secondary btn-sm" title="{str tag=delete}" href="">
                        <span class="icon icon-trash text-danger icon-lg"></span><span class="sr-only">{str tag=deletespecific section=mahara arg1='Item 1'}</span>
                    </a>
                </div>
            </td>
        </tr>
        <tr class="r1">
            <td><a href="">Item 2</a></td>
            <td class="center">5</td>
            <td class="center">2</td>
            <td>Item 2 info</td>
            <td>Item 2 stuff</td>
            <td class="right">
                <div class="btn-group">
                    <a class="btn btn-secondary btn-sm" title="{str tag=groupmanage section=admin}" href="">
                        <span class="icon icon-cog icon-lg"></span><span class="sr-only">{str tag=groupmanagespecific section=admin arg1='Item 2'}</span>
                    </a>
                    <a class="btn btn-secondary btn-sm" title="{str tag=delete}" href="">
                        <span class="icon icon-trash text-danger icon-lg"></span><span class="sr-only">{str tag=deletespecific section=mahara arg1='Item 2'}</span>
                    </a>
                </div>
            </td>
        </tr>
    </tbody>
</table>
```
</section>

<section data-markdown data-category="drop-downs">
### Basic drop-down menu
A drop-down select box.
```
<div class="input-small select form-group">
    <label for="searchviews_orderby">
        Drop-down:
    </label>
    <span class="picker">
        <select class="form-control input-small select" name="orderby" tabindex="0" style="">
            <option value="1" selected="selected">Option 1</option>
            <option value="2">Option 2</option>
            <option value="3">Option 3</option>
            <option value="4">Option 4</option>
        </select>
    </span>
</div>
```
</section>

<section data-markdown data-category="drop-downs">
### Drop-down menu with text entry
This drop-down select box allows you to enter text which is then searched using the option as filter. An example can be found in the user search in the administration area when a site has multiple institutions.
```
<form class="pieform form-inline with-heading" name="search" method="post">
    <div class="dropdown-group js-dropdown-group form-group">
        <fieldset class="pieform-fieldset dropdown-group js-dropdown-group">
            <div class="with-dropdown js-with-dropdown text form-group">
                <label for="search_query">
                    {str tag=search}:
                </label>
                <input type="text" class="form-control with-dropdown js-with-dropdown text autofocus" name="query" tabindex="0" value="" placeholder="Option 1">
            </div>
            <div id="search_filter_container" class="dropdown-connect js-dropdown-connect select form-group">
                <label for="search_filter">
                    {str tag=filter}:
                </label>
                <span class="picker">
                    <select class="form-control dropdown-connect js-dropdown-connect select" id="search_filter" name="filter" tabindex="0" style="">
                        <option value="1" selected="selected">Option 1</option>
                        <option value="2">Option 2</option>
                        <option value="3">Option 3</option>
                        <option value="4">Option 4</option>
                    </select>
                </span>
            </div>
        </fieldset>
    </div>
</form>
```
</section>

<section data-markdown data-category="drop-downs">
### Drop-down menu with select2
This drop-down select box uses the select2 library. An example can be found on the compose message page in user's inbox.
```
<script type="text/javascript">
jQuery(function() {
  jQuery(".js-example-basic-single").select2();
});
</script>
<select class="js-example-basic-single">
    <option value="AK">Auckland</option>
    <option value="WN">Wellington</option>
    <option value="CH">Christchurch</option>
    <option value="DN">Dunedin</option>
</select>
```
</section>

<section data-markdown data-category="profile-pictures">
### Profile side card
The profile picture size that is used on side cards. The example is the profile side card on the dashboard.
```
<div class="col-md-3 sidebar">
    <div id="sb-profile" class="sideblock-1 user-card">
        <div class="card">
            <h3 class="card-header profile-block">
                <a href="" class="username">Side card</a> <a href="" title="{str tag=editprofileicon section=artefact.file}" class="user-icon user-icon-60">
                    <img src="{profile_icon_url user=$sbdata.id maxheight=60 maxwidth=60}" alt="{str tag=editprofileicon section=artefact.file}">
                </a>
            </h3>
            <div class="list-group"></div>
        </div>
    </div>
</div>
```
</section>

<section data-markdown data-category="profile-pictures">
### Small profile picture
This size of profile picture is used mainly on comment blocks.
```
<a href="">
    <span class="user-icon">
        <img src="{profile_icon_url user=$sbdata.id maxheight=20 maxwidth=20}" alt="{str tag=editprofileicon section=artefact.file}" class="profile-icon-container">
    </span>
</a>
```
</section>

<section data-markdown data-category="profile-pictures">
### Friends list
This size and style of profile picture is used in the friends list.
```
<div class="user-thumbnails">
    <a href="" class="item user-icon metadata user-icon-100 {cycle values='d0,d1'}">
        <img src="{profile_icon_url user=$sbdata.id maxheight=100 maxwidth=100}" alt="{str tag=profileimagetext section=mahara arg1='John Smith'}">
        <p class="member-name">John Smith</p>
    </a>
</div>
```
</section>

<section data-markdown data-category="text">
### Heading 1
Used as the main heading of a page.
```
<h1>Heading 1</h1>
```
</section>

<section data-markdown data-category="text">
### Heading 2
Used as a subheading of a page.
```
<h2>Heading 2</h2>
```
</section>

<section data-markdown data-category="text">
### Heading 3
Used as the sub subheading of a page.
```
<h3>Heading 3</h3>
```
</section>

<section data-markdown data-category="text">
### card header
Used as the heading of a block or card.
```
<h3 class="title card-header">card header</h3>
```
</section>

<section data-markdown data-category="text">
### Normal text
Used as the default text across pages.
```
<p>Normal text</P>
```
</section>

<section data-markdown data-category="text">
### Bold text
Used as bold or strong text.
```
<strong>Bold text</strong>
```
</section>

<section data-markdown data-category="text">
### Italic text
Used as italic or strong text.
```
<i>Italic text</i>
```
</section>

<section data-markdown data-category="text">
### Description text
Used as a description for an item. Note: the div is only there to apply the form group class.
```
<div class="form-group">
    <span class="description">Description text</span>
</div>
```
</section>

<section data-markdown data-category="icons">
### Add user
As seen on the "Add user" page in the administration area.
```
<i class="icon icon-user-plus" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Annotation
As used for the Annotations" block.
```
<i class="icon icon-annotation" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Angle double left
Shows that a card can be expanded or collapsed to the left.
```
<i class="icon icon-angle-double-left" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Angle double right
Shows that a card can be expanded or collapsed to the right.
```
<i class="icon icon-angle-double-right" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Area chart
As seen on the "Statistics" page in the administration area.
```
<i class="icon icon-area-chart" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Arrow circle right
Usually shows moving to a next step.
```
<i class="icon icon-arrow-circle-right" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Arrow right
Arrow pointing right. This usually means "next step".
```
<i class="icon icon-arrow-right" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Arrows
Shows that an object can be dragged and dropped.
```
<i class="icon icon-arrows" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Ban
Used to signify banning a user.
```
<i class="icon icon-ban" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Bars
Used on menu buttons.
```
<i class="icon icon-bars" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Birthday cake
Shows the date something was created, for example a group.
```
<i class="icon icon-birthday-cake" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Book
As used on the "Journals" page.
```
<i class="icon icon-book" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Calendar
Usually used on buttons to signify a pop-up date selctor.
```
<i class="icon icon-calendar" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Caret down
Used to show a drop-down menu or used on columns where sorting is possible.
```
<i class="icon icon-caret-down" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Check
Used to signify a successful action.
```
<i class="icon icon-check" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Circle check
Used to signify SmartEvidence has been assessed as completed.
```
<i class="icon icon-check-circle completed" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Circle cross
Used to signify either removable columns on view or SmartEvidence item has been assessed as incomplete.
```
<i class="icon icon-times-circle incomplete" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Circle half open
Used to signify SmartEvidence item has been assessed as partially complete.
```
<i class="icon icon-adjust partial" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Circle open
Used to signify SmartEvidence has begun on the SmartEvidence martix table.
```
<i class="icon icon-circle-o begun" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Code
Used to show the HTML block.
```
<i class="icon icon-code" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Cog
Used on "Settings" buttons.
```
<i class="icon icon-cog" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Cogs
As seen on the configuration screen for blocks.
```
<i class="icon icon-cogs" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Columns
Used on the Edit layout" button.
```
<i class="icon icon-columns" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Comments
Used to show comments and used for the "Recent forum posts" block.
```
<i class="icon icon-comments" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Copy to clipboard
As used on the secret URLs page.
```
<i class="icon icon-files-o" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Cross
Used as "Delete" button. The class "text-danger" makes the icon red. Note: The trash icon should be used instead in the future.
```
<i class="icon icon-times text-danger" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Down
Arrow pointing down usually signifies collapsible elements.
```
<i class="icon icon-chevron-down" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Download
Used to signify downloadable content.
```
<i class="icon icon-download" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Ellipsis
Shows there are more options available.
```
<i class="icon icon-ellipsis-h" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Envelope
Used as "Inbox" icon.
```
<i class="icon icon-envelope" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Exchange
As seen on the "Networking" page.
```
<i class="icon icon-exchange" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Exclamation triangle
Used to signify an unsuccessful action or a warning.
```
<i class="icon icon-exclamation-triangle" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Extensions
As used on the "Extensions" pages in the administration area.
```
<i class="icon icon-puzzle-piece" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### File download
Used for the "File(s) to download" block.
```
<i class="icon icon-filedownload" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### File image
As seen on the "Site files" page in the administration area.
```
<i class="icon icon-file-image-o" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### File text
As seen on the "Site pages" page.
```
<i class="icon icon-file-text" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Filter
Used to signify filtering items.
```
<i class="icon icon-filter" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Flag
Used to signify flagging objectionable content.
```
<i class="icon icon-flag" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Folder
Used on the folder block.
```
<i class="icon icon-folder" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Folder open
Used to signify a folder of items.
```
<i class="icon icon-folder-open" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Globe
Shows the secret URL button.
```
<i class="icon icon-globe" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Group
As used for the "Group portfolios" block.
```
<i class="icon icon-users" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Heart
Shows that something has been favourited.
```
<i class="icon icon-heart" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Heart hollow
Shows something can be favourited.
```
<i class="icon icon-heart-o" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Help
Used as a help icon.
```
<i class="icon icon-info-circle" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Image
Used for the "Image" block.
```
<i class="icon icon-image" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Inbox
Used in the third navigation level in "Notifications".
```
<i class="icon icon-inbox" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Internal media
Used for the "Embedded media" block.
```
<i class="icon icon-internalmedia" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Key
As seen on the "Share" page in the site administration area.
```
<i class="icon icon-key" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Legal
As used on the "Licences" page in the administration area.
```
<i class="icon icon-legal" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Level up
Used in the file browser to signify going up to the parent folder.
```
<i class="icon icon-level-up" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Lock
Used as a security icon, for example on the "Shared by me" page.
```
<i class="icon icon-lock" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Long arrow down
Long arrow pointing down. It is used for sorting items vertically.
```
<i class="icon icon-long-arrow-down" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Long arrow left
Long arrow pointing left. It is used for sorting items horizontally.
```
<i class="icon icon-long-arrow-left" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Long arrow right
Long arrow pointing right. It is used for sorting items horizontally.
```
<i class="icon icon-long-arrow-right" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Long arrow up
Long arrow pointing up. It is used for sorting items vertically.
```
<i class="icon icon-long-arrow-up" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Media
Used for the "Media" category in the content chooser.
```
<i class="icon icon-fileimagevideo" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Navigation
Used for the "Navigation" block.
```
<i class="icon icon-navigation" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Next
Arrow pointing right usually signifies collapsible elements.
```
<i class="icon icon-chevron-right" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Outbox
Used in the third navigation level in "Notifications".
```
<i class="icon icon-paper-plane" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Paint brush
Used to signify themes and styling.
```
<i class="icon icon-paint-brush" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Paperclip
Used to show attachments.
```
<i class="icon icon-paperclip" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### PDF
Used for the "PDF" block.
```
<i class="icon icon-pdf" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Pencil
Used on "Edit" buttons.
```
<i class="icon icon-pencil" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Plug
Used to show webservices 'connection manager' connections.
```
<i class="icon icon-plug" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Plus
Used on "Add new" buttons. The class "text-success" makes the icon green.
```
<i class="icon icon-plus text-success" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Plus circle
Used to show adding something new. Usually a new table row.
```
<i class="icon icon-plus-circle" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Previous
Arrow pointing left usually signifies collapsible elements.
```
<i class="icon icon-chevron-left" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Recent posts
Used for the "Recent journal entries" block.
```
<i class="icon icon-recentposts" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Refresh
As seen on the button on the "Networking" page.
```
<i class="icon icon-refresh" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Remove
Pretty much the same as the times icon. Should use the trash icon instead.
```
<i class="icon icon-remove" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Remove user
Remove users.
```
<i class="icon icon-user-times" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Reply
Used to show you can reply to something, usually comments or messages.
```
<i class="icon icon-reply" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Reply all
Used to show you can reply to multiple people.
```
<i class="icon icon-reply-all" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### RSS
Used to show external sources.
```
<i class="icon icon-rss" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Search
Used to signify searchable items
```
<i class="icon icon-search" role="presentation"></i>
```
</section>
<section data-markdown data-category="icons">
### Shield
As used on the "Cookie consent" page in the administration area.
```
<i class="icon icon-shield" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Sign in
Used to show the sign in button.
```
<i class="icon icon-sign-in" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Sign out
Used to show the sign out button.
```
<i class="icon icon-sign-out" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Spinner
Used to signify loading. The class "icon-pulse" makes it spin.
```
<i class="icon icon-spinner icon-pulse" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Square
Used as an un-checked check box, for example on an uncompleted task of a plan.
```
<i class="icon icon-square-o" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Square checked
Used as a checked check box, for example on complete tasks of a plan.
```
<i class="icon icon-check-square-o" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Star
As used on the "Register site" page in the administration area.
```
<i class="icon icon-star" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Table
Shows you can export statistics.
```
<i class="icon icon-table" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Tagged posts
Used for the "Tagged journal entries" block.
```
<i class="icon icon-taggedposts" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Tags
Used to show tags.
```
<i class="icon icon-tags" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Text
Used for the "Text" block.
```
<i class="icon icon-text" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Trash
Used on buttons to signify deleting an item. The class "text-danger" makes the icon red.
```
<i class="icon icon-trash text-danger" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Undo
Used to show something can be undone or refreshed.
```
<i class="icon icon-undo" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### University
Used on the "Administer institutions" page in the administration area.
```
<i class="icon icon-university" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Unlock

```
<i class="icon icon-unlock-alt" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Up
Arrow pointing up usually signifies collapsible elements.
```
<i class="icon icon-chevron-up" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### User
As seen on the "User search" page in the administration area.
```
<i class="icon icon-user" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### User secret
Used to show you can log in as another user.
```
<i class="icon icon-user-secret" role="presentation"></i>
```
</section>

<section data-markdown data-category="icons">
### Wrench
Used as icon for a system notification.
```
<i class="icon icon-wrench" role="presentation"></i>
```
</section>

{*
    end of examples
*}

<div id="scroll-to-top" class="container">
    <a href="#top" class="btn btn-primary">{$scrollup}</a>
</div>

<script src="{$wwwroot}js/marked/marked.min.js"></script>
<script src="{$wwwroot}js/clipboard/clipboard.min.js"></script>
<script type="text/javascript">
    // using inline js here because it's so specific to the use case of the style guide
    // this is all done on the client side and would be to inefficient for anything other than the styleguide

    var categories = [];

    (function styleguide(){

      [].forEach.call( document.querySelectorAll('[data-markdown]'), function  fn(elem, i){

        // modified from https://gist.github.com/paulirish/1343518
        // strip leading whitespace so it isn't evaluated as code
        var text      = elem.innerHTML.replace(/\n\s*\n/g,'\n\n'),
            // set indentation level so your markdown can be indented within your HTML
            leadingws = text.match(/^\n?(\s*)/)[1].length,
            regex     = new RegExp('\\n?\\s{' + leadingws + '}','g'),
            md        = text.replace(regex,'\n'),
            html      = marked(md);

        elem.innerHTML = html;

        // add copy button
        var codeElem = $j(elem).find('code');
        codeElem.attr('id', 'code-block-' + i);
        codeElem.before('<button class="copy" role="presentation" data-clipboard-target="#code-block-' + i + '" title="{$copy}"><i class="icon icon-files-o"></i></button>');

        // add the category to the sections index
        var category = $j(elem).data('category');

        if ($j.inArray(category, categories) === -1) {
            categories.push(category);
        }

        // hide this section if it isn't part of the first category in the array
        if (category !== categories[0]) {
            $j(elem).hide();
        }
      });

      // init copy to clipboard buttons
      new ClipboardJS('.copy');

      // build section tabs
      $j.each(categories, function(i, category) {
          var readableName = category.replace("-", " ");
          if (i === 0) {
              $j('#category-tabs').append('<li><a class="active" href="#" data-category="' + category + '">' + readableName + '</a></li>');
          } else {
              $j('#category-tabs').append('<li><a href="#" data-category="' + category + '">' + readableName + '</a></li>');
          }
      });

      // handle tab click
      $j('#category-tabs a').on("click", function(event) {
          var category = $j(this).data('category');
          event.preventDefault();
          $j(this).parent().siblings().children().removeClass('active');
          $j(this).first().addClass('active');

          $j('[data-markdown]').each(function(){
              if ($j(this).data('category') !== category) {
                  $j(this).hide();
              } else {
                  $j(this).show();
              }
          });

      });

      // prevent example clicks going elsewhere unless it is a link
      // we do want a user to follow, eg help info
      $j('[data-markdown] a:not(.follow)').on("click", function(event) {
          event.preventDefault();
      });

      // scroll to top button position
      $j(window).on("scroll", function() {
          var scroll = $j(window).scrollTop();
          if (scroll < 100) {
              $j('#scroll-to-top').removeClass('fixed');
          } else {
              $j('#scroll-to-top').addClass('fixed');
          }
      });

      $j('#scroll-to-top a').on("click", function(event) {
          event.preventDefault();
          $j(window).scrollTop(0);
      });


    }());

</script>

{include file="footer.tpl"}
