{if $record->deleted}
    <span class="icon icon-comments-o left text-lighttone"></span>
    <h3 class="title list-group-item-heading">{$record->title}</h3>
    <span class="artefacttype text-lighttone">({str tag=deletedforumpost section=search.elasticsearch})</span>
{else}
    <h3 class="title list-group-item-heading text-inline">
        <span class="icon icon-comments-o left"></span>
        <a href="{$WWWROOT}interaction/forum/view.php?id={$record->id}">
            {$record->title}
        </a>
    </h3>
    <span class="artefacttype text-lighttone">({str tag=forum section=search.elasticsearch})</span>
    <div class="detail">{$record->description|str_shorten_html:140:true|safe}</div>
{/if}
