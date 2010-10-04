<div id="blocktype_externalfeed_feed">
    {if $feedimage}<div class="fr">{$feedimage|safe}</div>{/if}
    <div id="blocktype_externalfeed_title">
    <a href="{$url}"><img src="{theme_url filename="images/rss.gif"}"></a>
    {if $link}<a href="{$link}">{/if}
    {$title}
    {if $link}</a>{/if}
    </div>
    {if $description != $entries[0]->description}<div id="blocktype_externalfeed_desc">{$description|clean_html|safe}</div>{/if}
    <div id="blocktype_externalfeed_entries">
        {if $full}
            {foreach from=$entries item=entry}
                <h4>
                {if $entry->link}<a href="{$entry->link}">{/if}
                {$entry->title}
                {if $entry->link}</a>{/if}
                </h4>
                <div class="s">{$entry->description|clean_html|safe}</div>
            {/foreach}
        {else}
            <ol>
            {foreach from=$entries item=entry}
                <li>
                {if $entry->link}<a href="{$entry->link}">{/if}
                {$entry->title}
                {if $entry->link}</a>{/if}
                </li>
            {/foreach}
            </ol>
        {/if}
    </div>
    <div id="blocktype_externalfeed_lastupdate">
    {$lastupdated}
    </div>
</div>

