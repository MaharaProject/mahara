<!doctype html>
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
        <title>{str tag=usersportfolio section=export.html args=$user|full_name|escape}</title>
{foreach from=$stylesheets item=sheet}
        <link rel="stylesheet" type="text/css" href="{$rootpath}{if !$exportingoneview}HTML/{/if}static/{$sheet}">
{/foreach}
        <script>
        var config = {literal}{{/literal}
            'wwwroot': '{$WWWROOT}',
        {literal}}{/literal};
        </script>
{foreach from=$scripts item=script}
        <script type='text/javascript' src='{$script}'></script>
{/foreach}
    </head>
    <body>
        <header class="header fixed-top no-site-messages">
            <div class="navbar navbar-default navbar-main">
                <div class="container">
                    <div id="logo-area" class="logo-area">
                        <a class="logo change-to-small" href="https://mahara.org/">
                            <img src="{$rootpath}{if !$exportingoneview}HTML/{/if}{$maharalogo}" alt="Mahara">
                        </a>
                        <a href="https://mahara.org/" class="logoxs change-to-small-default">
                            <img src="{$rootpath}{if !$exportingoneview}HTML/{/if}{$maharalogosmall}" alt="Mahara">
                        </a>
                    </div>
                </div>
            </div>
        </header>
        <div class="pageheader">
            <div class="container pageheader-content">
                <div class="row">
                    <div class="col-md-12 main">
                        <div class="main-column">
                            <div id="pageheader-column-container">
                                <h1 id="viewh1" class="page-header">
                                    <span class="section-heading">{$page_heading}</span>
                                </h1>
                                <div class="text-small">
                                {if !$nobreadcrumbs}
                                    <div id="breadcrumbs" class="breadcrumbs">
                                    <ul>
                                        <li>{str tag=youarehere section=export}: <a href="{$rootpath}index.html">{str tag=home}</a></li>
                                        {foreach from=$breadcrumbs item=crumb}
                                            <li>&raquo; {if $crumb.path}<a href="{$crumb.path}">{$crumb.text}</a>{else}{$crumb.text}{/if}</li>
                                        {/foreach}
                                    </ul>
                                    </div>
                                {/if}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container main-content">
            <div class="row">
                <main id="main" class="col-md-12 main">
                    <div id="content" class="main-column">
                        <div id="main-column-container">
