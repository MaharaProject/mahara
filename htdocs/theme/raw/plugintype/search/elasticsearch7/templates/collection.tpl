{if $record->deleted}
    <h2 class="list-group-item-heading text-inline">
        <span class="icon icon-folder-open left" role="presentation" aria-hidden="true"></span>
        {$record->name}
        <span class="artefacttype text-midtone">({str tag=deleted section=search.elasticsearch7})</span>
    </h2>
{else}
    <h2 class="list-group-item-heading text-inline">
        <span class="icon float-start icon-folder-open left" role="presentation" aria-hidden="true"></span>
        {if $record->viewid}
        <a href="{$WWWROOT}view/view.php?id={$record->viewid}">
            {$record->name}
        </a>
        {else}
            {$record->name}
        {/if}
        <span class="artefacttype text-midtone">({str tag=collection section=search.elasticsearch7})</span>
    </h2>
    <div class="row">
        <div class="col-md-7">
            {if $record->createdbyname}
                <div class="createdby">{str tag=createdby section=search.elasticsearch7 arg1='<a href="`$record->createdby|profile_url`">`$record->createdbyname`</a>'}</div>
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
                <div class="tags text-small">
                    {str tag=tags section=search.elasticsearch7}:
                    {foreach from=$record->tags item=tag name=tags}
                        <a href="{$WWWROOT}{$thispath}?query={$tag}&tagsonly=true">{$tag}</a>{if !$.foreach.tags.last}, {/if}
                    {/foreach}
                </div>
            {/if}
            <!-- end TAGS -->
        </div>
        <div class="col-md-5">
            <!-- PAGES -->
            {if is_array($record->views) && count($record->views) > 0}
                <div class="usedon">
                {if count($record->views) > 1}
                    {str tag=views}:
                {else}
                    {str tag=view}:
                {/if}
                    <ul class="list-group list-unstyled">
                    {foreach from=$record->views key=id item=view name=views}
                        <li><a href="{$WWWROOT}view/view.php?id={$id}">{$view|str_shorten_html:50:true|safe}</a></li>
                    {/foreach}
                    </ul>
                </div>
            {else}
                {str tag=none section=search.elasticsearch7}
            {/if}
        </div>
    </div>
{/if}
