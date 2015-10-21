{foreach from=$items item=view}
    <li class="list-group-item">
        <a href="{$view.fullurl}" class="outer-link">
            <span class="sr-only">{$view.title}</span>
        </a>
        <h5 class="list-group-item-heading">{$view.title}</h5>
        <span class="postedon text-small text-midtone">
            {if $view.mtime == $view.ctime}{str tag=Created}{else}{str tag=Updated}{/if}
            {$view.mtime|strtotime|format_date}
        </span>
        {if $view.template}
        <div class="grouppage-from">
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
                <strong>{str tag=tags}:</strong>
                {list_tags owner=$view.owner tags=$view.tags}
            </div>
        {/if}
    </li>
{/foreach}
