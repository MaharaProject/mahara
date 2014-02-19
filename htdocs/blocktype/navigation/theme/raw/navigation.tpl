{if $views}
    <nav id="collection-nav" class="fullwidth">
        <ul>
        {foreach from=$views item=item name=view}
            <li class="{cycle name=rows values='r0,r1'}">
                {if $currentview == $item->view}
                    <h3 class="title">{$item->title}</h3>
                {else}
                    <h3 class="title cpage"><a href="{$item->fullurl}">{$item->title}</a></h3>
                {/if}
            </li>
        {/foreach}
        </ul>
    </nav>
{else}
    {str tag='noviewstosee' section='group'}
{/if}
