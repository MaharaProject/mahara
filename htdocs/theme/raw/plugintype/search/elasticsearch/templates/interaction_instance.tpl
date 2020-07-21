{if $record->deleted}
    <span class="icon icon-comments left text-midtone" role="presentation" aria-hidden="true"></span>
    <h2 class="title list-group-item-heading">{$record->title}</h2>
    <span class="artefacttype text-midtone">({str tag=deletedforumpost section=search.elasticsearch})</span>
{else}
    <h2 class="title list-group-item-heading text-inline">
        <span class="icon icon-comments left" role="presentation" aria-hidden="true"></span>
        <a href="{$WWWROOT}interaction/forum/view.php?id={$record->id}">
            {$record->title}
        </a>
    </h2>
    <span class="artefacttype text-midtone">({str tag=forum section=search.elasticsearch})</span>
    <div class="detail">
        {if $record->highlight}
            {$record->highlight|safe}
        {else}
            {$record->description|str_shorten_html:140:true|safe}
        {/if}
    </div>
{/if}
