{foreach from=$items item=collection}
    <li class="list-group-item text-midtone">
        <a href="{$collection.fullurl}" class="outer-link">
         <span class="sr-only">{$collection.name}</span>
        </a>

        {$collection.name}

        {if $collection.sharedby}
        <span class="owner metadata inner-link">
            {str tag=by section=view}

            {if $collection.group}
                <a href="{group_homepage_url($collection.groupdata)}" class="text-success">{$collection.sharedby}</a>
            {elseif $collection.owner}
                <a href="{profile_url($collection.user)}" class="text-success text-small">{$collection.sharedby}</a>
            {else}
                {$collection.sharedby}
            {/if}
        </span>
        {/if}
        {if $collection.description}
        <small class="detail mts">
            {$collection.description|str_shorten_html:100:true|strip_tags|safe}
        </small>
        {/if}

        {if $collection.tags}
        <small class="">
            <strong>{str tag=tags}:</strong>
            <span class="inner-link">{list_tags owner=$collection.owner tags=$collection.tags}</span>
        </small>
         {/if}

    </li>
{/foreach}
