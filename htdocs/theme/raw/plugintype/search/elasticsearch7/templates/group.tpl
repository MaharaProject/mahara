{if $record->deleted}
    <h2 class="list-group-item-heading">
        <span class="icon icon-people-group left text-midtone" role="presentation" aria-hidden="true"></span>
        {$record->name} ({str tag=deleted section=search.elasticsearch7})
    </h2>
{else}
    <div class="groupsdetails">
        <h2 class="list-group-item-heading">
            <span class="icon icon-people-group left" role="presentation" aria-hidden="true"></span>
            <a href="{$WWWROOT}group/view.php?id={$record->id}">
                {$record->name}
            </a>
        </h2>
        <div class="groupdesc">
            {if $record->highlight}
                {$record->highlight|safe}
            {else}
                {$record->description|str_shorten_html:140:true|safe}
           {/if}
        </div>
        {if $record->groupadmins}
        <div class="groupadmin text-small">
            {str tag=groupadmins section=group}:
            {foreach name=admins from=$record->groupadmins item=user}
                <a href="{profile_url($user)}">{$user|display_name}</a>
                {if !$.foreach.admins.last}, {/if}
            {/foreach}
        </div>
        {/if}
    </div>
{/if}
