{if $results.data}
        {foreach from=$results.cdata item=row}
            {foreach from=$row item=r}
            <div class="{cycle values='r0,r1'} listrow">
              <div class="fl membericon"><img src="{profile_icon_url user=$r maxwidth=40 maxheight=40}" alt="{str tag=profileimagetext arg1=$r|display_default_name}"></div>
              <div class="memberdetail">
                <h3 class="title"><a href="{profile_url($r)}">{$r.name}</a>{if $r.role}<span class="grouprole"> - {$results.roles[$r.role]->display}
                  {if $caneditroles && $r.canchangerole} (<a href="{$WWWROOT}group/changerole.php?group={$group}&amp;user={$r.id}">{str tag=changerole section=group}</a>){/if}</span>{/if}</h3>
                {if $r.role}
                <div class="rel">
                  <div class="fr removemember">{$r.removeform|safe}</div>
                  <div class="detail">{$r.introduction|str_shorten_html:80:true|safe}</div>
                  <div class="jointime"><strong>{str tag="Joined" section="group"}:</strong> {$r.jointime}</div>
                </div>
                {elseif $membershiptype == 'request'}
                <div class="hasrequestedmembership">{str tag=hasrequestedmembership section=group}.{if $r.reason}
                  <strong>{str tag=reason}:</strong> {$r.reason|format_whitespace|safe}{/if}
                </div>
                <div class="fl">{$r.addform|safe}</div>
                <div class="fl">{$r.denyform|safe}</div>
                {elseif $membershiptype == 'invite'}
                <div class="detail">{str tag=hasbeeninvitedtojoin section=group}</div>
                {/if}
              </div>
              <div class="cb"></div>
            </div>
            {/foreach}
        {/foreach}
{else}
    <div>{str tag="noresultsfound"}</div>
{/if}
