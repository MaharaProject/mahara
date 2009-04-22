<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>{str tag=usersportfolio section=export.html args=$user|full_name|escape}</title>
        <link rel="stylesheet" type="text/css" href="{$rootpath}static/style.css">
    </head>
    <body>
        <div id="mahara-logo">
            <a href="http://mahara.org/"><img src="{$rootpath}static/mahara.png" alt="Mahara export"></a>
        </div>
        <h1><a href="{$rootpath}index.html">{$user|full_name|escape}</a></h1>
        <div id="content">
            <div id="breadcrumbs">
                <ul>
                    <li><a href="{$rootpath}index.html">Home</a></li>
{foreach from=$breadcrumbs item=crumb}
                    <li>&raquo; {if $crumb.path}<a href="{$crumb.path|escape}">{$crumb.text|escape}</a>{else}{$crumb.text|escape}{/if}</li>
{/foreach}
                </ul>
            </div>
            <div id="breadcrumbs-footer"></div>
