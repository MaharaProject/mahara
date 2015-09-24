<div id="blocktype_externalfeed_feed" class="external-feed">
    {if $feedimage}<div class="feedlogoimage">{$feedimage|safe}</div>{/if}
    <div id="blocktype_externalfeed_title" >
        {if $description && $description != $entries[0]->description}
        <div class="panel-body">
            <div id="blocktype_externalfeed_desc" class="feed-decription text-lighttone text-small">
                {$description|clean_html|safe}
            </div>
         </div>
        {/if}

        {if $full}
            <div class="list-group">
                {foreach from=$entries item=entry}
                <div class="list-group-item">
                    {if $entry->link}<a href="{$entry->link}">{/if}

                    <h4 class="title list-group-item-heading mb0">
                        {$entry->title}
                    </h4>
                    <span class="postdetails metadata text-small">
                        {if $entry->pubdate}{str tag=publishedon section=blocktype.externalfeed arg1=$entry->pubdate}{/if}
                    </span>

                    {if $entry->link}</a>{/if}
                    <div class="feedcontent mtl text-small">{$entry->description|clean_html|safe}</div>
                 </div>
                {/foreach}
            </div>
        {else}
            <ol class="list-group">
            {foreach from=$entries item=entry}
                <li class="list-group-item {if $entry->link}list-group-item-link{/if}">

                {if $entry->link}<a href="{$entry->link}">{/if}

                <h4 class="list-group-item-heading text-inline">{$entry->title}</h4>

                <span class="postdetails text-small text-midtone">
                 - {if $entry->pubdate}{str tag=publishedon section=blocktype.externalfeed arg1=$entry->pubdate}{/if}
                </span>

                {if $entry->link}</a>{/if}
                </li>
            {/foreach}
            </ol>
        {/if}

        <div id="blocktype_externalfeed_lastupdate" class="postdetails text-right text-small text-lighttone">
        {$lastupdated}
        </div>
    </div>
</div>
