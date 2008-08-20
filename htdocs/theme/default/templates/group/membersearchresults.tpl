{if !empty($results.data)}
        {foreach from=$results.cdata item=row}
          <tr class="r1">
            {foreach from=$row item=r}
            <td>
              <div class="fl"><img src="{$WWWROOT}thumb.php?type=profileicon&amp;maxsize=40&amp;id={$r.id|escape}" alt=""></div>
                <h4><a href="{$WWWROOT}user/view.php?id={$r.id|escape}">{$r.name|escape}</a></h4>
                {if $r.role}
                <div class="removeform">
                {$results.roles[$r.role]->display}{if $caneditroles} (<a href="{$WWWROOT}group/changerole.php?group={$group}&amp;user={$r.id}">{str tag=changerole section=group}</a>){/if}
                {$r.removeform}
                </div>
                <p><strong>Joined:</strong> {$r.jointime}</p>
                <p>{$r.introduction|str_shorten:80:true}</p>
                {elseif $membershiptype == 'request'}
                <p>{str tag=hasrequestedmembership section=group}</p>
                <p>{$r.addform}</p>
                {elseif $membershiptype == 'invite'}
                <p>{str tag=hasbeeninvitedtojoin section=group}</p>
                {/if}
            </td>
            {/foreach}
            {if count($row) == 1}<td></td>{/if}
          </tr>
        {/foreach}
{else}
    <div>{str tag="noresultsfound"}</div>
{/if}
