{if $record->deleted}
    <div class="groupsdetails">
        <h3 class="title">{$record->name} ({str tag=deleted section=search.elasticsearch})</h3>
    </div>
{else}
    <div class="groupsdetails">
        <h3 class="title"><a href="{$WWWROOT}group/view.php?id={$record->id}">{$record->name}</a></h3>
        <div class="groupadmin">{str tag=groupadmins section=group}:
            {foreach name=admins from=$record->groupadmins item=user}
                <a href="{profile_url($user)}">{$user|display_name}</a>
                {if !$.foreach.admins.last}, {/if}
            {/foreach}
        </div>
        <div class="detail">{$record->description|str_shorten_html:140:true|safe}</div>
    </div>
{/if}