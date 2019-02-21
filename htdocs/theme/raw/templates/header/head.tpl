<head data-basehref="{$WWWROOT}">
    <meta name="generator" content="Mahara {$SERIES} (https://mahara.org)" />
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta property="og:title" content="{$PAGETITLE}" />
    <meta property="og:description" content="{$sitedescription4facebook}" />
    <meta property="og:image" content="{$sitelogo4facebook}" />
    <meta property="og:image:width" content="1200" />
    <meta property="og:image:height" content="630" />
    <meta name="viewport" id="viewport" content="width=device-width,minimum-scale=1.0,maximum-scale=10.0,initial-scale=1.0" />
    {if $PAGEAUTHOR}<meta name="author" content="{$PAGEAUTHOR}">{/if}
    <title>{$PAGETITLE}</title>
    <script>
    var config = {literal}{{/literal}
        'theme': {$THEMELIST|safe},
        'sesskey' : '{$SESSKEY}',
        'wwwroot': '{$WWWROOT}',
        'loggedin': {$USER->is_logged_in()|intval},
        'userid': {$USER->get('id')},
        'mobile': {if $MOBILE}1{else}0{/if},
        'handheld_device': {if $HANDHELD_DEVICE}1{else}0{/if},
        'cc_enabled': {$CC_ENABLED|intval},
        'mathjax': {ifconfig key=mathjax}1{else}0{/ifconfig},
        'select2_lang': '{if $select2_language}{$select2_language}{/if}',
        'maxuploadsize': '{$maxuploadsize}',
        'maxuploadsizepretty': '{$maxuploadsizepretty}'
    {literal}}{/literal};
    </script>
    {$STRINGJS|safe}
{foreach from=$JAVASCRIPT item=script}
    <script src="{$script}"></script>
{/foreach}
    <script src="{$WWWROOT}/js/select2/select2.full.js?v={$CACHEVERSION}"></script>
{if $INLINEJAVASCRIPT}
    <script>
{$INLINEJAVASCRIPT|safe}
    </script>
{/if}
{foreach from=$HEADERS item=header}
    {$header|safe}
{/foreach}

{foreach from=$STYLESHEETLIST item=cssurl}
    <link rel="stylesheet" type="text/css" href="{$cssurl}">
{/foreach}
    <script type="application/javascript" src="{$WWWROOT}/lib/popper/popper.min.js?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{$WWWROOT}/lib/bootstrap/assets/javascripts/bootstrap.min.js?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{$WWWROOT}/js/javascript-templates/js/tmpl.min.js?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{$WWWROOT}/js/masonry/masonry.min.js?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{theme_url filename='js/pieform.js'}?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{theme_url filename='js/formtabs.js'}?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{theme_url filename='js/filebrowser.js'}?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{theme_url filename='js/access.js'}?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{theme_url filename='js/notification.js'}?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{theme_url filename='js/dock.js'}?v={$CACHEVERSION}"></script>
    <script type="application/javascript" src="{theme_url filename='js/theme.js'}?v={$CACHEVERSION}"></script>

    <link rel="shortcut icon" href="{$WWWROOT}favicon.ico?v={$CACHEVERSION}" type="image/vnd.microsoft.icon">
    <link rel="image_src" href="{$sitelogo}">
{if $ADDITIONALHTMLHEAD}{$ADDITIONALHTMLHEAD|safe}{/if}
{if $COOKIECONSENTCODE}{$COOKIECONSENTCODE|safe}{/if}

</head>
{dynamic}{flush}{/dynamic}
