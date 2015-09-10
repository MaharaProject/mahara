<head data-basehref="{$WWWROOT}">
    <meta name="generator" content="Mahara {$SERIES} (https://mahara.org)" />
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta property="og:title" content="{$PAGETITLE}" />
    <meta property="og:description" content="{$sitedescription4facebook}" />
    <meta property="og:image" content="{$sitelogo4facebook}" />
    <meta name="viewport" id="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=10.0,initial-scale=1.0" />
    {if isset($PAGEAUTHOR)}<meta name="author" content="{$PAGEAUTHOR}">{/if}
    <title>{$PAGETITLE}</title>
    <script type="application/javascript">
    var config = {literal}{{/literal}
        'theme': {$THEMELIST|safe},
        'sesskey' : '{$SESSKEY}',
        'wwwroot': '{$WWWROOT}',
        'loggedin': {$USER->is_logged_in()|intval},
        'userid': {$USER->get('id')},
        'mobile': {if $MOBILE}1{else}0{/if},
        'handheld_device': {if $HANDHELD_DEVICE}1{else}0{/if},
        'cc_enabled': {$CC_ENABLED|intval},
        'mathjax': {ifconfig key=mathjax}1{else}0{/ifconfig}
    {literal}}{/literal};
    </script>
    {$STRINGJS|safe}
{foreach from=$JAVASCRIPT item=script}
    <script type="application/javascript" src="{$script}"></script>
{/foreach}
{if isset($INLINEJAVASCRIPT)}
    <script type="application/javascript">
{$INLINEJAVASCRIPT|safe}
    </script>
{/if}
{foreach from=$HEADERS item=header}
    {$header|safe}
{/foreach}

{foreach from=$STYLESHEETLIST item=cssurl}
    <link rel="stylesheet" type="text/css" href="{$cssurl}">
{/foreach}
    <script type="application/javascript" src="{$WWWROOT}/lib/bootstrap/assets/javascripts/bootstrap.js?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{$WWWROOT}/js/javascript-templates/js/tmpl.min.js?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{$WWWROOT}js/jquery.rating.js?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{theme_url filename='js/masonry.min.js'}?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{$WWWROOT}/js/select2/select2.full.js?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{theme_url filename='js/pieform.js'}?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{theme_url filename='js/block.js'}?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{theme_url filename='js/formtabs.js'}?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{theme_url filename='js/filebrowser.js'}?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{theme_url filename='js/access.js'}?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{theme_url filename='js/notification.js'}?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{theme_url filename='js/dock.js'}?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{theme_url filename='js/theme.js'}?v={$CACHEVERSION}"></script>

    <script type="application/javascript" src="{$WWWROOT}js/css.js?v={$CACHEVERSION}"></script>
    <link rel="shortcut icon" href="{$WWWROOT}favicon.ico?v={$CACHEVERSION}" type="image/vnd.microsoft.icon">
    <link rel="image_src" href="{$sitelogo}">
{if $ADDITIONALHTMLHEAD}{$ADDITIONALHTMLHEAD|safe}{/if}
{if $COOKIECONSENTCODE}{$COOKIECONSENTCODE|safe}{/if}

</head>
{dynamic}{flush}{/dynamic}
