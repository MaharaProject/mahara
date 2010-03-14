{if $results.data}
        {foreach from=$results.cdata item=row}
          <tr class="{cycle values='r0,r1'}">
            {foreach from=$row item=r}
            <td>
              <div class="fl"><img src="{profile_icon_url user=$r maxwidth=40 maxheight=40}" alt=""></div>
                <h3><a href="{$WWWROOT}user/view.php?id={$r.id|escape}">{$r.name|escape}</a></h3>
                {if $r.role}
                <div class="rel">
                  <strong>{$results.roles[$r.role]->display}</strong>
                  {if $caneditroles && $r.canchangerole} (<a href="{$WWWROOT}group/changerole.php?group={$group}&amp;user={$r.id}">{str tag=changerole section=group}</a>){/if}
                  <div class="rbuttons btn-del">{$r.removeform}</div>
                  <div>{$r.introduction|str_shorten_html:80:true}</div>
                  <label>{str tag="Joined" section="group"}:</label> {$r.jointime}
                </div>
                {elseif $membershiptype == 'request'}
                <div>{str tag=hasrequestedmembership section=group}.{if $r.reason}
                  <label>{str tag=reason}:</label> {$r.reason|format_whitespace}{/if}
                </div>
                <div class="right btn-add">{$r.addform}</div>
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
