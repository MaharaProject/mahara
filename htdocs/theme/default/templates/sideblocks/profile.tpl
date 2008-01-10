    <h3><a style="color:white" href="{$WWWROOT}user/view.php?id={$data.id}">{$data.id|display_name|escape}</a></h3>
    <a href="{$WWWROOT}user/view.php?id={$data.id}"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=50&amp;id={$data.id}" alt=""></a>
    <a href="{$WWWROOT}?logout">{str tag="logout"}</a>
    <ul style="color:black">
        {if $data.unreadnotifications}<li><a href="{$WWWROOT}account/activity/">{$data.unreadnotifications}</a></li>{/if}
        {if $data.invitedgroups}<li><a href="{$WWWROOT}group/mygroups.php?filter=invited">{$data.invitedgroups}</a></li>{/if}
        {if $data.pendingfriends}<li><a href="{$WWWROOT}user/?filter=2">{$data.pendingfriends}</a></li>{/if}
        {if $data.groups}
        <li>
            <a href="{$WWWROOT}group/mygroups.php?filter=owned">{str tag="groupsiown"}:</a>
            <ul>
            {foreach from=$data.groups item=group}
                <li><a href="{$WWWROOT}group/view.php?id={$group->id}">{$group->name|escape}</a></li>
            {/foreach}
            </ul>
        </li>
        {/if}
        {if $data.views}
        <li>
            <a href="{$WWWROOT}view/">{str tag="myviews"}:</a>
            <ul>
            {foreach from=$data.views item=view}
                <li><a href="{$WWWROOT}view/view.php?id={$view->id}">{$view->title|escape}</a></li>
            {/foreach}
            </ul>
        </li>
        {/if}
    </ul>