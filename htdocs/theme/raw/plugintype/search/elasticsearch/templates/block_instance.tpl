<h2 class="list-group-item-heading text-inline">
    <span class="icon icon-file left" role="presentation" aria-hidden="true"></span>
{if $record->link}
        <a href="{$WWWROOT}{$record->link}">{$record->title|str_shorten_html:50:true|safe}</a>
{else}
        {$record->title|str_shorten_html:50:true|safe}
{/if}
    <span class="artefacttype text-midtone">
    ({str tag=document section=search.elasticsearch})
    {if $record->deleted}
        ({str tag=deleted section=search.elasticsearch})
    {/if}
    </span>
</h2>
<div class="row">
    <div class="col-md-7">
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
        <!-- TAGS -->
        {if is_array($record->tags) && count($record->tags) > 0}
        <div class="tags text-small">{str tag=tags section=search.elasticsearch}:
            {foreach from=$record->tags item=tag name=tags}
                <a href="{$WWWROOT}search/elasticsearch/index.php?query={$tag}&tagsonly=true">{$tag}</a>{if !$.foreach.tags.last}, {/if}
            {/foreach}
        </div>
        {/if}
    </div>
    <!-- VIEWS -->
    {if is_array($record->views) && count($record->views) > 0}
    <div class="col-md-5">
        <div class="usedon">
            {if $record->views}
                <strong>{str tag=usedonpage section=search.elasticsearch}:</strong>
                <ul class="list-group list-unstyled">
                {foreach from=$record->views key=id item=view}
                    <li><a href="{$WWWROOT}view/view.php?id={$id}">{$view|str_shorten_html:50:true|safe}</a>
                    </li>
                {/foreach}
                </ul>
            {/if}
        </div>
    </div>
    {/if}
</div>
