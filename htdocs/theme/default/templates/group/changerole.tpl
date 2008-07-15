{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
                <h2>{$group->name|escape}</h2>

{include file="group/tabstart.tpl" current="members"}

                <div class="group-info-para"><h3>{$subtitle}</h3></div>
                <div class="group-info-para">{str tag=currentrole section=group}: {$roles[$userrole]->display}
                <form method="post">
                  <input type="hidden" name="userid" value="{$userid|escape}" />
                  <input type="hidden" name="groupid" value="{$groupid|escape}" />
                  {str tag=changeroleto section=group}
                  <select name="role">
                  {foreach from=$roles item=role}
                    <option value="{$role->role}"{if ($role->role == $userrole)} selected{/if}>{$role->display}</option>
                  {/foreach}
                  </select>
                  <input type="submit" value="{str tag=submit}" /><br />
                </form>
                </div>
                <div class="group-info-para"><a href="{$WWWROOT}group/changerole.php?user={$userid}&amp;group={$groupid}&amp;remove=1">{str tag=removefromgroup section=group}</a></div>
                <br />

{include file="group/tabend.tpl"}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}


