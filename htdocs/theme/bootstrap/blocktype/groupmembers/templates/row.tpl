    {foreach from=$groupmembers item=groupmember}
        <div class="{cycle values='d0,d1'} friendcell center">
            <a href="{profile_url($groupmember)}">
               <img src="{profile_icon_url user=$groupmember maxwidth=60 maxheight=60}" alt="{str tag=profileimagetext arg1=$groupmember|display_default_name}" title="{$groupmember|display_default_name|escape}">
            </a>
            <div class="membername"><a href="{profile_url($groupmember)}">{$groupmember|display_default_name|escape}</a></div>
        </div>
    {/foreach}
