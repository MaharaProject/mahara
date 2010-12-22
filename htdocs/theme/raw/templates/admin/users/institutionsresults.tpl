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
                <td class="controls right">
                    <div class="left">
                        <form action="" method="post">
                                <input type="hidden" name="i" value="{$institution->name}">
                                <input type="hidden" name="edit" value=1>
                                <input type="image" name="edit" title="{str tag="edit"}" src="{theme_url filename="images/edit.gif"}">
                        </form>
                        {if $siteadmin && !$institution->members && $institution->name != 'mahara'}
                        <form action="" method="post">
                                <input type="hidden" name="i" value="{$institution->name}">
                                <input type="hidden" name="delete" value="1">
                                <input type="image" name="delete" title="{str tag="delete"}" src="{theme_url filename="images/icon_close.gif"}">
                        </form>
                        {/if}
                    </div>
                </td>
                <td class="center">{if $institution->suspended}<span class="suspended">{str tag="suspendedinstitution" section=admin}</span>{/if}</td>
        </tr>
{/foreach}