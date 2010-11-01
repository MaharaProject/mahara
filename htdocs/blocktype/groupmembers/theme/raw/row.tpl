    {foreach from=$groupmembers item=row}
        <tr>
        {foreach from=$row item=groupmember}
            <td class="{cycle values='r0,r1'} friendcell">
                <a href="{$WWWROOT}user/view.php?id={$groupmember['id']}">
                   <img src="{profile_icon_url user=$groupmember maxwidth=60 maxheight=60}" alt="" title="{$groupmember|display_default_name|escape}">
                </a>
                <div><a href="{$WWWROOT}user/view.php?id={$groupmember['id']}">{$groupmember|display_default_name|escape}</a></div>
            </td>
        {/foreach}
        </tr>
    {/foreach}
