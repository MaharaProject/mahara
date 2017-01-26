{if $results.data}
{foreach from=$results.cdata item=row}
{foreach from=$row item=r}
<div class="list-group-item {if $membershiptype == 'invite' || $membershiptype == 'request'} list-group-item-warning{/if}">
    <a href="{profile_url($r)}" class="outer-link"><span class="sr-only">{$r.name}</span></a>
     <div class="row">
        <div class="col-md-8">
            <div class="usericon-heading">
                <div class="user-icon pull-left">
                    <img src="{profile_icon_url user=$r maxwidth=40 maxheight=40}" alt="{str tag=profileimagetext arg1=$r|display_default_name}">
                </div>
                <h4 class="list-group-item-heading">
                    {$r.name}
                    {if $r.role}
                    <span class="grouprole text-small text-midtone"> -
                        {$results.roles[$r.role]->display}
                        {if $caneditroles && $r.canchangerole}
                        <a href="{$WWWROOT}group/changerole.php?group={$group}&amp;user={$r.id}" class="inner-link text-link">
                            [{str tag=changerole section=group}]
                        </a>
                        {/if}
                    </span>
                    {/if}
                </h4>
            </div>
            {if $r.role}
            <div class="introduction detail text-small">
                <span>{$r.introduction|str_shorten_html:80:true:true:false|safe}
                </span>

                <div class="jointime">
                    <strong>
                        {str tag="Joined" section="group"}:
                    </strong>
                    {$r.jointime}
                </div>
            </div>
            {elseif $membershiptype == 'request'}
            <div class="requestedmembership detail text-small">
                {str tag=hasrequestedmembership section=group}.
                {if $r.reason}
                <div>
                    <strong>{str tag=reason}:</strong>
                    {$r.reason|format_whitespace|safe}
                </div>
                {/if}
            </div>
            {elseif $membershiptype == 'invite'}
            <div class="invited detail text-small">
                {str tag=hasbeeninvitedtojoin section=group}
            </div>
            {/if}
        </div>
        <div class="col-md-4">
            <div class="inner-link btn-action-list">
                <div class="btn-top-right btn-group btn-group-top">
                    {if $r.role}
                        {$r.removeform|safe}
                    {elseif $membershiptype == 'request'}
                        {$r.addform|safe}
                        {$r.denyform|safe}
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>
{/foreach}
{/foreach}
{else}
<div class="panel-body">
    <p class="no-results">{str tag="noresultsfound"}</p>
</div>
{/if}
