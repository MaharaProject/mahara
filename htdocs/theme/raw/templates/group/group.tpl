<div id="grouplist_{$group->id}" class="list-group-item {if $group->membershiptype == 'invite' || $group->membershiptype == 'request' || $group->requests} list-group-item-warning{/if}">
    <div class="flex-row">
        <div class="flex-title">
            <h2 class="list-group-item-heading text-inline">
                <a href="{$group->homeurl}">{$group->name}</a>
                {if $group->settingsdescription}
                <span class="font-base text-midtone text-regular">
                    - {$group->settingsdescription}
                </span>
                {/if}
            </h2>
        </div>
        <div class="flex-controls">
            {include file="group/groupuseractions.tpl" group=$group}
        </div>
    </div>
    <div class="row">
        <div class="col-md-8">
            {if $group->description}
            <p class="groupdesc">
                {$group->description|str_shorten_html:100:true:true:false|safe}
            </p>
            {/if}
            <div class="groupsdetails text-small">
                {if $group->editwindow}
                <div class="groupeditable">
                    <strong>{str tag=editable section=group}:</strong>
                    {$group->editwindow}
                </div>
                {/if}
                {if $group->admins}
                <div class="groupadmin">
                    <strong>{str tag=groupadmins section=group}:</strong>
                    {strip}
                    {foreach name=admins from=$group->admins item=user}
                        <a href="{profile_url($user)}"> {$user|display_name}</a>
                        {if !$.foreach.admins.last},{/if}
                    {/foreach}
                    {/strip}
                </div>
                {/if}
                {if $group->membercount}
                    <div class="membernumber">
                        <a href="{$WWWROOT}group/members.php?id={$group->id}">
                            <strong>{str tag=Members section=group}:</strong> {$group->membercount}
                        </a>
                    </div>
                {/if}
                {if $group->labels}
                <div class="grouplabels">
                    <strong>{str tag=mygrouplabel section=group}:</strong>
                    {strip}
                    {foreach name=labels from=$group->labels item=label}
                        {assign var=labelselected value=0}
                        {if $activegrouplabels}
                            {foreach from=$activegrouplabels item=grouplabel}
                                {if $grouplabel === $label}
                                    {assign var=labelselected value=1}
                                {/if}
                            {/foreach}
                        {/if}
                        {if $labelselected}
                            &nbsp;{$label}
                        {else}
                            <a href="{$paramsurl}&labelfilter={urlencode($label)}" title='{str tag=addgrouplabelfilter section=group arg1=$label}'> {$label}</a>
                        {/if}
                        {if !$.foreach.labels.last},{/if}
                    {/foreach}
                    {/strip}
                </div>
                {/if}
            </div>
        </div>
        <div class="group-user-status col-md-4">
            {include file="group/groupuserstatus.tpl" group=$group}
        </div>
    </div>
</div>
