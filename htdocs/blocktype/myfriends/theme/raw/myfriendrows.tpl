    {foreach from=$friends item=row}
        <tr>
        {foreach from=$row item=friend}
            <td class="{cycle values='r0,r1'} friendcell">
                <a href="{$WWWROOT}user/view.php?id={$friend->id}">
                   <img src="{profile_icon_url user=$friend maxwidth=60 maxheight=60}" alt="">
                </a>
                <div><a href="{$WWWROOT}user/view.php?id={$friend->id}">{$friend|display_default_name|escape}</a></div>
            </td>
        {/foreach}
        </tr>
    {/foreach}
