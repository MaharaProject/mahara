{foreach from=$items item=view}
    <li class="list-group-item text-small text-medium">
        <a href="{$view.fullurl}" class="outer-link">
            <span class="sr-only">{$view.title}</span>
        </a>
        {if $view.template}
        <div class="">
            {$view.form|safe}
        </div>
        {/if}
            {$view.title}
            {if $view.description}
                <small class="detail mts metadata">
                    {$view.description|str_shorten_html:100:true|strip_tags|safe}
                </small>
                {/if}
                
                {if $view.tags}
                <small class="tags mts">
                    <strong>{str tag=tags}:</strong> 
                    {list_tags owner=$view.owner tags=$view.tags}
                </small>
            {/if}
    </li>
{/foreach}
