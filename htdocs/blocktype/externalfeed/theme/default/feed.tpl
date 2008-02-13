<div id="blocktype_externalfeed_feed">
    <div id="blocktype_externalfeed_title">
    {if $link}<a href="{$link|escape}">{/if}
    {$title|escape}
    {if $link}</a>{/if}
    </div>
    <div id="blocktype_externalfeed_desc">{$description|escape}</a></div>
    <div id="blocktype_externalfeed_entries">
        {foreach from=$entries item=entry}
            <p>
            {if $entry->link}<a href="{$entry->link|escape}">{/if}
            {$entry->title|escape}
            {if $entry->link}</a>{/if}
            </p>
        {/foreach}
    </div>
    <div id="blocktype_externalfeed_lastupdate">
    {$lastupdated}
    </div>
</div>
