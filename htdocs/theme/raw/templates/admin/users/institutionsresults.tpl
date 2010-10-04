{foreach from=$institutions item=institution}
        <tr class="{cycle values='r0,r1'}">
                <td>{$institution->displayname}</td>
                <td class="center">
                  {if $institution->name != 'mahara'}
                        <a href="{$WWWROOT}admin/users/institutionusers.php?usertype=members&amp;institution={$institution->name}">{$institution->members}</a>
                  {else}
                        <a href="{$WWWROOT}admin/users/search.php?institution=mahara">{$institution->members}</a>
                  {/if}
                </td>
                <td class="center">{$institution->maxuseraccounts}</td>
                <td class="center"><a href="{$WWWROOT}admin/users/institutionstaff.php?institution={$institution->name}">{$institution->staff}</a></td>
                <td class="center"><a href="{$WWWROOT}admin/users/institutionadmins.php?institution={$institution->name}">{$institution->admins}</a></td>
                <td class="right">
                        <form action="" method="post">
                                <input type="hidden" name="i" value="{$institution->name}">
                                {if $siteadmin && !$institution->members && $institution->name != 'mahara'}<input type="submit" class="btn-del icon s" name="delete" value="{str tag="delete"}">{/if}
                                <input type="submit" class="btn-edit icon s" name="edit" value="{str tag="edit"}">
                        </form>
                </td>
                <td class="center">{if $institution->suspended}<span class="suspended">{str tag="suspendedinstitution" section=admin}</span>{/if}</td>
        </tr>
{/foreach}