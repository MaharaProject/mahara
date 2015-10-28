{foreach from=$items item=collection}
    <li class="list-group-item text-midtone">
        <a href="{$collection.fullurl}" class="outer-link">
            <span class="sr-only">{$collection.name}</span>
        </a>
        <h5 class="text-inline">{$collection.name}</h5>
        {if $collection.sharedby}
        <span class="owner inner-link text-small">
            {str tag=by section=view}
            {if $collection.group}
                <a href="{group_homepage_url($collection.groupdata)}">{$collection.sharedby}</a>
            {elseif $collection.owner}
                <a href="{profile_url($collection.user)}" class="text-small">{$collection.sharedby}</a>
            {else}
                {$collection.sharedby}
            {/if}
        </span>
        {/if}
        {if $collection.description}
        <div class="detail text-small">
            {$collection.description|str_shorten_html:100:true|strip_tags|safe}
        </div>
        {/if}

        {if $collection.tags}
        <div class="text-small">
            <strong>{str tag=tags}:</strong>
            <span class="inner-link">{list_tags owner=$collection.owner tags=$collection.tags}</span>
        </div>
         {/if}

    </li>
{/foreach}
