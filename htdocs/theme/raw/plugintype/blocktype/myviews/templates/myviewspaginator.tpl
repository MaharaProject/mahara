{foreach from=$items item=view}
    <div class="list-group-item flush">
        <h3 class="title list-group-item-heading text-inline">
            <a href="{$view.fullurl}">{$view.title}</a>
        </h3>

        {if $view.collid}
        <span class="text-small text-midtone">
            ({str tag=nviews1 section=view arg1=$view.numpages})
        </span>
        {/if}

        {if $view.description}
        <div class="list-group-item-text text-small">
            {$view.description|str_shorten_html:100:true|strip_tags|safe}
        </div>
        {/if}

        {if $item.tags}
        <div class="tags">
            {str tag=tags}: {list_tags owner=$view.owner tags=$view.tags}
        </div>
        {/if}
    </div>
{/foreach}
