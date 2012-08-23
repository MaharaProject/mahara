{if $results.data}
        {foreach from=$results.cdata item=row}
          <tr class="{cycle values='r0,r1'}">
            {foreach from=$row item=r}
            <td>
              <div class="fl membericon"><img src="{profile_icon_url user=$r maxwidth=40 maxheight=40}" alt=""></div>
                <h4><a href="{profile_url($r)}">{$r.name}</a></h4>
                {if $r.role}
                <div class="rel">
                  <div class="removemember">{$r.removeform|safe}</div>
                  <strong>{$results.roles[$r.role]->display}</strong>
                  {if $caneditroles && $r.canchangerole} (<a href="{$WWWROOT}group/changerole.php?group={$group}&amp;user={$r.id}">{str tag=changerole section=group}</a>){/if}
                  <div>{$r.introduction|str_shorten_html:80:true|safe}</div>
                  <label>{str tag="Joined" section="group"}:</label> {$r.jointime}
                </div>
                {elseif $membershiptype == 'request'}
                <div>{str tag=hasrequestedmembership section=group}.{if $r.reason}
                  <label>{str tag=reason}:</label> {$r.reason|format_whitespace|safe}{/if}
                </div>
                <div class="s fl">{$r.addform|safe}</div>
                <div class="s fl">{$r.denyform|safe}</div>
                {elseif $membershiptype == 'invite'}
                <div>{str tag=hasbeeninvitedtojoin section=group}</div>
                {/if}
            </td>
            {/foreach}
            {if count($row) == 1}<td></td>{/if}
          </tr>
        {/foreach}
{else}
    <div>{str tag="noresultsfound"}</div>
{/if}
