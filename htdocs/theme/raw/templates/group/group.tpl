<div id="grouplist_{$group->id}" class="list-group-item {if $group->membershiptype == 'invite' || $group->membershiptype == 'request' || $group->requests} list-group-item-warning{/if}">
    <div class="row">
        <div class="col-md-8">
            <h2 class="list-group-item-heading text-inline">
                <a href="{$group->homeurl}">{$group->name}</a>
            </h2>
            {if $group->settingsdescription}
            <span class="text-midtone">
                - {$group->settingsdescription}
            </span>
            {/if}

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
                        <a href="{profile_url($user)}" class="inner-link"> {$user|display_name}</a>
                        {if !$.foreach.admins.last},{/if}
                    {/foreach}
                    {/strip}
                </div>
                {/if}
                {if $group->membercount}
                    <div class="membernumber">
                        <a href="{$WWWROOT}group/members.php?id={$group->id}" class="inner-link">
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
                            <a href="{$paramsurl}&labelfilter={urlencode($label)}" title='{str tag=addgrouplabelfilter section=group arg1=$label}' class="inner-link"> {$label}</a>
                        {/if}
                        {if !$.foreach.labels.last},{/if}
                    {/foreach}
                    {/strip}
                </div>
                {/if}
            </div>
        </div>

        <div class="col-md-4">
            {include file="group/groupuserstatus.tpl" group=$group}
        </div>
    </div>
</div>
