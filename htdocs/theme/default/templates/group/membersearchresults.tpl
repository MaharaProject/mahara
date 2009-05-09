{if !empty($results.data)}
        {foreach from=$results.cdata item=row}
          <tr class="r{cycle values=0,1}">
            {foreach from=$row item=r}
            <td>
              <div class="fl"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=40&amp;id={$r.id|escape}" alt=""></div>
                <h3><a href="{$WWWROOT}user/view.php?id={$r.id|escape}">{$r.name|escape}</a></h3>
                {if $r.role}
                <h5 class="removeform">
                {$results.roles[$r.role]->display}{if $caneditroles && $r.canchangerole} (<a href="{$WWWROOT}group/changerole.php?group={$group}&amp;user={$r.id}">{str tag=changerole section=group}</a>){/if}
                {$r.removeform}
                </h5>
                <div class="introduction">{$r.introduction|str_shorten:80:true}</div>
                <label>{str tag="Joined" section="group"}:</label> {$r.jointime}
                {elseif $membershiptype == 'request'}
                <div class="requested">{str tag=hasrequestedmembership section=group}.{if $r.reason}
				<label>{str tag=reason}:</label> {$r.reason|format_whitespace}{/if}
				</div>
                {$r.addform}
				
                {elseif $membershiptype == 'invite'}
                <div class="invited">{str tag=hasbeeninvitedtojoin section=group}</div>
                {/if}
            </td>
            {/foreach}
            {if count($row) == 1}<td></td>{/if}
          </tr>
        {/foreach}
{else}
    <div>{str tag="noresultsfound"}</div>
{/if}
