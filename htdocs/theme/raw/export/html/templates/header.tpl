<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
        <title>{str tag=usersportfolio section=export.html args=$user|full_name|escape}</title>
        <link rel="stylesheet" type="text/css" href="{$rootpath}static/views.css">
{foreach from=$stylesheets item=sheet}
        <link rel="stylesheet" type="text/css" href="{$rootpath}static/{$sheet}"{if substr($sheet, -9) == 'print.css'} media="print"{/if}>
{/foreach}
    </head>
    <body>
        <div id="mahara-logo">
            <a href="http://mahara.org/"><img src="{$maharalogo}" alt="Mahara export"></a>
        </div>
        <h1><a href="{$rootpath}index.html">{$page_heading}</a></h1>
        <div id="content">
            {if !$nobreadcrumbs}<div id="breadcrumbs" class="breadcrumbs">
                <ul>
                    <li>{str tag=youarehere section=export}: <a href="{$rootpath}index.html">{str tag=home}</a></li>
{foreach from=$breadcrumbs item=crumb}
                    <li>&raquo; {if $crumb.path}<a href="{$crumb.path}">{$crumb.text}</a>{else}{$crumb.text}{/if}</li>
{/foreach}
                </ul>
            </div>
            <div id="breadcrumbs-footer"></div>{/if}
