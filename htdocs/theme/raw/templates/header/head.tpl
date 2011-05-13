<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
    {if isset($PAGEAUTHOR)}<meta name="author" content="{$PAGEAUTHOR}">{/if}
    <title>{$PAGETITLE}</title>
    <script type="text/javascript">
    var config = {literal}{{/literal}
        'theme': {$THEMELIST|safe},
        'sesskey' : '{$SESSKEY}',
        'wwwroot': '{$WWWROOT}',
        'loggedin': {$USER->is_logged_in()|intval},
        'userid': {$USER->get('id')}
    {literal}}{/literal};
    </script>
    {$STRINGJS|safe}
{foreach from=$JAVASCRIPT item=script}
    <script type="text/javascript" src="{$script}"></script>
{/foreach}
{foreach from=$HEADERS item=header}
    {$header|safe}
{/foreach}
{if isset($INLINEJAVASCRIPT)}
    <script type="text/javascript">
{$INLINEJAVASCRIPT|safe}
    </script>
{/if}
	<!--[if lt IE 7.]>
		<script defer type="text/javascript" src="{$WWWROOT}js/pngfix.js"></script>
	<![endif]-->
{foreach from=$STYLESHEETLIST item=cssurl}
    <link rel="stylesheet" type="text/css" href="{$cssurl}">
{/foreach}
    <link rel="stylesheet" type="text/css" href="{theme_url filename='style/print.css'}" media="print">
    <script type="text/javascript" src="{$WWWROOT}js/css.js"></script>
    <link rel="shortcut icon" href="{$WWWROOT}favicon.ico" type="image/vnd.microsoft.icon">
</head>
{dynamic}{flush}{/dynamic}
