{foreach from=$items item=view}
    <li class="list-group-item flush">
        <h4 class="list-group-item-heading text-inline">
            <a href="{$view.fullurl}">{$view.displaytitle}</a>
            {if $view.collid}(<span class="text-small text-midtone">{str tag=nviews section=view arg1=$view.numpages})</span>{/if}
            <br />
            <span class="postedon text-small text-midtone">
                {if $view.mtime == $view.ctime}
                    {str tag=Created}
                {else}
                    {str tag=Updated}
                {/if}
                {$view.mtime|strtotime|format_date}
            </span>
        </h4>

        {if $view.template}
            <div class="btn-group btn-group-top only-button">
                {$view.form|safe}
            </div>
        {/if}

        {if $view.description}
            <div class="detail text-small">
                {$view.description|str_shorten_html:100:true|strip_tags|safe}
            </div>
        {/if}

        {if $view.tags}
            <div class="tags text-small">
                {str tag=tags}: {list_tags owner=$view.owner tags=$view.tags}
            </div>
        {/if}
    </li>
{/foreach}
