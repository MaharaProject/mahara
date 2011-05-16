    {foreach from=$groupmembers item=groupmember}
        <div class="{cycle values='d0,d1'} friendcell center">
            <a href="{$WWWROOT}user/view.php?id={$groupmember['id']}">
               <img src="{profile_icon_url user=$groupmember maxwidth=60 maxheight=60}" alt="" title="{$groupmember|display_default_name|escape}">
            </a>
            <div class="membername"><a href="{$WWWROOT}user/view.php?id={$groupmember['id']}">{$groupmember|display_default_name|escape}</a></div>
        </div>
    {/foreach}
