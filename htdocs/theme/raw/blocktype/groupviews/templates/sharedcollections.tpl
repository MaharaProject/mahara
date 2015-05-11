{foreach from=$items item=collection}
    <div class="{cycle values='r0,r1'} listrow">
        <h4 class="title"><a href="{$collection.fullurl}">{$collection.name}</a></h4>
        {if $collection.sharedby}
            <span class="owner"> {str tag=by section=view}
                {if $collection.group}
                    <a href="{group_homepage_url($collection.groupdata)}">{$collection.sharedby}</a>
                {elseif $collection.owner}
                    <a href="{profile_url($collection.user)}">{$collection.sharedby}</a>
                {else}
                    {$collection.sharedby}
                {/if}
            </span>
            <span class="postedon">
            - {if $collection.mtime == $collection.ctime}{str tag=Created}{else}{str tag=Updated}{/if}
            {$collection.mtime|strtotime|format_date: 'strftimedate'}
            </span>
        {/if}
        <div class="detail">{$collection.description|str_shorten_html:100:true|strip_tags|safe}</div>
     {if $collection.tags}
        <div class="tags"><strong>{str tag=tags}:</strong> {list_tags owner=$collection.owner tags=$collection.tags}</div>
     {/if}
    </div>
{/foreach}
