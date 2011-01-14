    <div class="sidebar-header"><h3>{$sbdata.group->name}</h3></div>
    <div class="sidebar-content">
    <ul>
    {foreach from=$sbdata.menu item=item}
        {if $item.path != 'groups'}
        <li><a href="{$WWWROOT}{$item.url}">{$item.title}</a>
            {if $item.path == 'groups/forums' && $sbdata.forums}
            <ul>
                {foreach from=$sbdata.forums item=forum}
                <li><a href="{$WWWROOT}interaction/forum/view.php?id={$forum->id}">{$forum->title}</a></li>
                {/foreach}
            </ul>
            {/if}
        </li>
        {/if}
    {/foreach}
    </ul>
</div>
