<div id="blocktype_externalfeed_feed" class="external-feed">
    <div id="blocktype_externalfeed_title" >

        {if $description && $description != $entries[0]->description}
        <div class="panel-body">
            <div id="blocktype_externalfeed_desc" class="feed-decription">
                {$description|clean_html|safe}
            </div>
         </div>
        {/if}

        {if $full}
        <div class="list-group mb0">
            {foreach from=$entries item=entry}
            <div class="list-group-item">
                {if $entry->link}<a href="{$entry->link}">{/if}

                <h4 class="title feedtitle list-group-item-heading mb0">
                    {$entry->title}
                </h4>
                <span class="postdetails metadata text-small">
                    {if $entry->pubdate}{str tag=publishedon section=blocktype.externalfeed arg1=$entry->pubdate}{/if}
                </span>

                {if $entry->link}</a>{/if}
                <div class="feedcontent mtl">{$entry->description|clean_html|safe}</div>
             </div>
            {/foreach}
        <div>
        {else}
            <ol class="list-group mb0">
            {foreach from=$entries item=entry}
                <li class="list-group-item {if $entry->link}list-group-item-link{/if}">
                {if $entry->link}<a href="{$entry->link}">{/if}

                {$entry->title}

                <span class="postdetails metadata">
                 - {if $entry->pubdate}{str tag=publishedon section=blocktype.externalfeed arg1=$entry->pubdate}{/if}
                </span>

                {if $entry->link}</a>{/if}
                </li>
            {/foreach}
            </ol>
        {/if}

        <div id="blocktype_externalfeed_lastupdate" class="postdetails text-right ptm mbm metadata">
        {$lastupdated}
        </div>
    </div>
</div>
