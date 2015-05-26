
<div class="panel-body text-small">

    <p class="mbm">
        <span class="prs fa fa-birthday-cake"></span>
        <span class=""> {str tag=Created section=group}: {$group->ctime}</span>
    </p>

    <p class="mbm">
        <span class="prs fa fa-shield"></span>
        <span class="">{$group->settingsdescription}</span>
    </p>

    {if $group->categorytitle}
    <p class="mbm">
        <span class="prs fa fa-tag"></span>
        <span class="">{str tag=groupcategory section=group}:</span>
        {$group->categorytitle}
    </p>
    {/if}
    
    {if $editwindow}
    <p class="mbm">
        <span class="prs fa fa-calendar"></span>
        <span class="">{str tag=editable section=group}:</span>
        {$editwindow}
    </p>
    {/if}
    <p class="mbm">
        <span class="prs fa fa-area-chart"></span>
        {if $group->membercount}
        <span class="mrs">
            {$group->membercount}&nbsp;{str tag=Members section=group}
            
        </span>
        {/if}
        <span class="mrs">
            {$group->viewcount}&nbsp;{str tag=Views section=view}
        </span>
        <span class="mrs">
            {$group->filecounts->files}&nbsp;{str tag=Files section=artefact.file}
            
        </span>
        <span class="mrs">
            {$group->filecounts->folders}&nbsp;{str tag=Folders section=artefact.file}
            
        </span>
        <span class="mrs">
            {$group->forumcounts}&nbsp;{str tag=nameplural section=interaction.forum}
            
        </span>
        <span class="mrs">
            {$group->topiccounts}&nbsp;{str tag=Topics section=interaction.forum}
            
        </span>
        <span class="mrs">
            {$group->postcounts}&nbsp;{str tag=Posts section=interaction.forum}
            
        </span>
    </p>
    <p class="mbm">
        <span class="prs fa fa-user"></span>
        <span class="">{str tag=groupadmins section=group}:</span>
    </p>
    <p class="mbm">
    {foreach name=admins from=$group->admins item=user}
        <a href="{profile_url($user)}" class="label label-default mbs">
            <img src="{profile_icon_url user=$user maxwidth=20 maxheight=20}" alt="{str tag=profileimagetext arg1=$user|display_default_name}" class="user-icon-alt">
            {$user|display_name}
        </a>
    {/foreach}
    </p>
</div>
