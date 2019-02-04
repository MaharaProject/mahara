<div class="row">
    <div class="col-md-8">
        {if $record->link}
            <h3 class="title list-group-item-heading text-inline">
                <span class="icon icon-file left" role="presentation" aria-hidden="true"></span>
                <a href="{$WWWROOT}{$record->link}">{$record->title|str_shorten_html:50:true|safe}</a>
            </h3>
        {else}
            <h3 class="title list-group-item-heading text-inline">
                <span class="icon icon-file left" role="presentation" aria-hidden="true"></span>
                {$record->title|str_shorten_html:50:true|safe}
            </h3>
        {/if}
        <span class="artefacttype text-midtone">
            ({str tag=document section=search.elasticsearch})
            {if $record->deleted}
                ({str tag=deleted section=search.elasticsearch})
            {/if}
        </span>
        {if $record->createdbyname}
            <div class="createdby">{str tag=createdby section=search.elasticsearch arg1='<a href="`$record->createdby|profile_url`">`$record->createdbyname`</a>'}</div>
        {/if}
        <div class="content-text">
            {if $record->highlight}
                {$record->highlight|safe}
            {else}
                {$record->description|str_shorten_html:140:true|safe}
            {/if}
        </div>
        <!-- TAGS -->
        {if is_array($record->tags) && count($record->tags) > 0}
        <div class="tags"><strong>{str tag=tags section=search.elasticsearch}:</strong>
            {foreach from=$record->tags item=tag name=tags}
                <a href="{$WWWROOT}search/elasticsearch/index.php?query={$tag}&tagsonly=true">{$tag}</a>{if !$.foreach.tags.last}, {/if}
            {/foreach}
        </div>
        {/if}
    </div>
    <!-- VIEWS -->
    {if is_array($record->views) && count($record->views) > 0}
    <div class="col-md-4">
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
