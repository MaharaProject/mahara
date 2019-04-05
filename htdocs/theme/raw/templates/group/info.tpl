
<div class="block-container">
    <p>
        <span class="icon icon-birthday-cake left" role="presentation" aria-hidden="true"></span>
        <span> {str tag=Created section=group}: {$group->ctime}</span>
    </p>

    <p>
        <span class="icon icon-shield left" role="presentation" aria-hidden="true"></span>
        <span class="">{$group->settingsdescription}</span>
    </p>

    {if $group->categorytitle}
    <p>
        <span class="icon icon-tag left" role="presentation" aria-hidden="true"></span>
        <span>{str tag=groupcategory section=group}:</span>
        {$group->categorytitle}
    </p>
    {/if}

    {if $editwindow}
    <p>
        <span class="icon icon-calendar left" role="presentation" aria-hidden="true"></span>
        <span>{str tag=editable section=group}:</span>
        {$editwindow}
    </p>
    {/if}
    <ul class="list-unstyled list-inline ">
        <li class="list-inline-item">
            <span class="icon icon-area-chart" role="presentation" aria-hidden="true"></span>
        </li>
        {if $group->membercount}
        <li class="list-inline-item">
            {$group->membercount}&nbsp;{str tag=Members section=group}
        </li>
        {/if}
        <li class="list-inline-item">
            {$group->viewcount}&nbsp;{str tag=Views section=view}
        </li>
        <li class="list-inline-item">
            {$group->filecounts->files}&nbsp;{str tag=Files section=artefact.file}
        </li>
        <li class="list-inline-item">
            {$group->filecounts->folders}&nbsp;{str tag=Folders section=artefact.file}
        </li>
        <li class="list-inline-item">
            {$group->forumcounts}&nbsp;{str tag=nameplural section=interaction.forum}
        </li>
        <li class="list-inline-item">
            {$group->topiccounts}&nbsp;{str tag=Topics section=interaction.forum}
        </li>
        <li class="list-inline-item">
            {$group->postcounts}&nbsp;{str tag=Posts section=interaction.forum}
        </li>
    </ul>
    <p>
        <span class="icon icon-user left" role="presentation" aria-hidden="true"></span>
        <span>{str tag=groupadmins section=group}:</span>
          {foreach name=admins from=$group->admins item=user}
        <a href="{profile_url($user)}">
            <span class="user-icon user-icon-20 user-icon-inline"><img src="{profile_icon_url user=$user maxwidth=20 maxheight=20}" alt="{str tag=profileimagetext arg1=$user|display_default_name}"></span>
            {$user|display_name}
        </a>
    {/foreach}
    </p>
</div>
