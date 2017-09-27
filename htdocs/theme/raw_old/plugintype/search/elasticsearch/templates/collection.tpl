{if $record->deleted}
    <h3 class="title list-group-item-heading text-inline">
        <span class="icon icon-folder-open left" role="presentation" aria-hidden="true"></span>
        {$record->name}
    </h3>
    <span class="artefacttype text-midtone">({str tag=deleted section=search.elasticsearch})</span>
{else}
    <h3 class="title">
        <span class="icon icon-folder-open left" role="presentation" aria-hidden="true"></span>
        {if $record->viewid}
        <a href="{$WWWROOT}view/view.php?id={$record->viewid}">
            {$record->name}
        </a>
        {else}
            {$record->name}
        {/if}
    </h3>
    <span class="artefacttype">({str tag=collection section=search.elasticsearch})</span>
    {if $record->createdbyname}
        <div class="createdby">{str tag=createdby section=search.elasticsearch arg1='<a href="`$record->createdby|profile_url`">`$record->createdbyname`</a>'}</div>
    {/if}
    <div class="detail">
        {if $record->highlight}
            {$record->highlight|safe}
        {else}
            {$record->description|str_shorten_html:140:true|safe}
        {/if}
    </div>
    <!-- PAGES -->
    <div class="tags">
        <strong>{str tag=pages section=search.elasticsearch}:</strong>
        {if $record->views}
            {foreach from=$record->views key=id item=view name=foo}
                <a href="{$WWWROOT}view/view.php?id={$id}">{$view}</a>{if !$.foreach.foo.last}, {/if}
            {/foreach}
        {else}
            {str tag=none section=search.elasticsearch}
        {/if}
    </div>
    <!-- TAGS -->
    {if $record->tags|count gt 0}
        <div class="tags">
            <strong>{str tag=tags section=search.elasticsearch}:</strong>
            {foreach from=$record->tags item=tag name=tags}
                <a href="{$WWWROOT}search/elasticsearch/index.php?query={$tag}&tagsonly=true">{$tag}</a>{if !$.foreach.tags.last}, {/if}
            {/foreach}
        </div>
    {/if}
    <!-- end TAGS -->
{/if}
