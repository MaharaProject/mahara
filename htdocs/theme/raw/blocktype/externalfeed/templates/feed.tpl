<div id="blocktype_externalfeed_feed">
    {if $feedimage}<div class="feedlogoimage">{$feedimage|safe}</div>{/if}
    <div id="blocktype_externalfeed_title">
    <a href="{$url}" target="_blank"><img src="{theme_image_url filename="feed"}"></a>
    {if $link}<a href="{$link}" target="_blank">{/if}
    {$title}
    {if $link}</a>{/if}
    </div>
    {if $description != $entries[0]->description}<div id="blocktype_externalfeed_desc">{$description|clean_html|safe}</div>{/if}
    <div id="blocktype_externalfeed_entries">
        {if $full}
            {foreach from=$entries item=entry}
                <h3 class="title">
                {if $entry->link}<a href="{$entry->link}" target="_blank">{/if}
                {$entry->title}
                {if $entry->link}</a>{/if}
                </h3>
                <div class="postdetails">
                {if $entry->pubdate}{str tag=publishedon section=blocktype.externalfeed arg1=$entry->pubdate}{/if}
                </div>
                <div class="feedcontent">{$entry->description|clean_html|safe}</div>
            {/foreach}
        {else}
            <ol>
            {foreach from=$entries item=entry}
                <li>
                {if $entry->link}<a href="{$entry->link}" target="_blank">{/if}
                {$entry->title}
                {if $entry->link}</a>{/if}<br />
                <span class="postdetails">
                {if $entry->pubdate}{str tag=publishedon section=blocktype.externalfeed arg1=$entry->pubdate}{/if}
                </span>
                </li>
            {/foreach}
            </ol>
        {/if}
    </div>
    <div id="blocktype_externalfeed_lastupdate" class="postdetails">
    {$lastupdated}
    </div>
</div>

