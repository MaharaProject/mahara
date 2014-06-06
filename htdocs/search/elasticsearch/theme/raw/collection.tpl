{if $record->deleted}
    <h3 class="title">{$record->name} <span class="artefacttype">({str tag=deleted section=search.elasticsearch})</span></h3>
{else}
    <h3 class="title"><a href="{$WWWROOT}view/view.php?id={$record->viewid}">{$record->name}</a> <span class="artefacttype">({str tag=collection section=search.elasticsearch})</span></h3>
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