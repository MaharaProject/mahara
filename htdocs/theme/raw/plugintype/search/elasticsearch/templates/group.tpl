{if $record->deleted}
    <h2 class="title list-group-item-heading">
        <span class="icon icon-users left text-midtone" role="presentation" aria-hidden="true"></span>
        {$record->name} ({str tag=deleted section=search.elasticsearch})
    </h2>
{else}
    <div class="groupsdetails">
        <h2 class="title list-group-item-heading">
            <span class="icon icon-users left" role="presentation" aria-hidden="true"></span>
            <a href="{$WWWROOT}group/view.php?id={$record->id}">
                {$record->name}
            </a>
        </h2>
        <div class="groupdesc text-small">
            {if $record->highlight}
                {$record->highlight|safe}
            {else}
                {$record->description|str_shorten_html:140:true|safe}
           {/if}
        </div>
        {if $record->groupadmins}
        <div class="groupadmin text-small">
            <strong>{str tag=groupadmins section=group}:</strong>
            {foreach name=admins from=$record->groupadmins item=user}
                <a href="{profile_url($user)}">{$user|display_name}</a>
                {if !$.foreach.admins.last}, {/if}
            {/foreach}
        </div>
        {/if}
    </div>
{/if}
