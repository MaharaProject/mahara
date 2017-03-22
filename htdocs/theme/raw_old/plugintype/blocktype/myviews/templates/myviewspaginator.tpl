{foreach from=$items item=view}
    <div class="list-group-item">
        <a href="{$view.fullurl}" class="outer-link">
            <span class="sr-only">{$view.title}</span>
        </a>

        <h4 class="title list-group-item-heading text-inline">
            <a href="{$view.fullurl}">{$view.title}</a>
        </h4>

        {if $view.collid}
        <span class="text-small text-midtone">
            ({str tag=nviews section=view arg1=$view.numpages})
        </span>
        {/if}
        
        {if $view.description}
        <div class="list-group-item-text text-small">
            {$view.description|str_shorten_html:100:true|strip_tags|safe}
        </div>
        {/if}

        {if $item.tags}
        <div class="tags">
            <strong>{str tag=tags}:</strong> {list_tags owner=$view.owner tags=$view.tags}
        </div>
        {/if}
    </div>
{/foreach}
