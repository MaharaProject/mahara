{if $record->deleted}
    <h3 class="title list-group-item-heading text-inline">
        <span class="icon icon-folder-open left"></span>
        {$record->name}
    </h3>
    <span class="artefacttype text-lighttone">({str tag=deleted section=search.elasticsearch})</span>
{else}
    <h3 class="title">
        <span class="icon icon-folder-open left"></span>
        <a href="{$WWWROOT}view/view.php?id={$record->viewid}">
            {$record->name}
        </a>
    </h3>
    <span class="artefacttype">({str tag=collection section=search.elasticsearch})</span>
    {if $record->createdbyname}
        <div class="createdby">{str tag=createdby section=search.elasticsearch arg1='<a href="`$record->createdby|profile_url}">`$record->createdbyname|safe`</a>'}</div>
    {/if}
    <div class="detail">{$record->description|str_shorten_html:140:true|safe}</div>
    <div class="tags"><strong>{str tag=pages section=search.elasticsearch}:</strong>
        {foreach from=$record->views key=id item=view name=foo}
            <a href="{$WWWROOT}view/view.php?id={$id}">{$view}</a>{if !$.foreach.foo.last}, {/if}
        {/foreach}
    </div>
{/if}