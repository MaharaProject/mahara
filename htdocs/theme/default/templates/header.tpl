<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>{$title|default:"Mahara"|escape}</title>
        {strip}
            {foreach from=$JAVASCRIPT item=script}
                <script language="javascript" type="text/javascript" src="{$script}">
                </script>
            {/foreach}
            {if isset($INLINEJAVASCRIPT)}
               <script language="javascript" type="text/javascript">
                   {$INLINEJAVASCRIPT}
               </script>
            {/if}   
            {foreach from=$HEADERS item=header}{$header}{/foreach}
        {/strip}
        <style type="text/css">{literal}
        .required label { color: orange; }
        .help { font-size: smaller; vertical-align: sup; }
        .description { font-size: smaller; font-style: italic; }
        .error { color: red; }
        .errmsg { color: red; font-size: smaller; }
        label { vertical-align: top; }
        {/literal}
        </style>
    </head>
    <body>
    {insert name="messages"}
