{if $record->deleted}
    <h3 class="title">{$record->title}</h3>
{else}
    <h3 class="title"><a href="{$WWWROOT}interaction/forum/view.php?id={$record->id}">{$record->title}</a> <span class="artefacttype">({str tag=forum section=search.elasticsearch})</span></h3>
    <div class="detail">{$record->description|str_shorten_html:140:true|safe}</div>
{/if}
