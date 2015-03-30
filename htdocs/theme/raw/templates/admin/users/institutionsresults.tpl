{foreach from=$institutions item=institution}
        <tr class="{cycle values='r0,r1'}">
                <td>
                    {if !$institution->site}<a href="{$WWWROOT}institution/index.php?institution={$institution->name}">{/if}
                        {$institution->displayname}
                    {if !$institution->site}</a>{/if}
                </td>
                <td class="center">
                  {if !$institution->site}
                        <a href="{$WWWROOT}admin/users/institutionusers.php?usertype=members&amp;institution={$institution->name}">{$institution->members}</a>
                  {else}
                        <a href="{$WWWROOT}admin/users/search.php?institution=mahara">{$institution->members}</a>
                  {/if}
                </td>
                <td class="center">{if $institution->maxuseraccounts}{$institution->maxuseraccounts}{/if}</td>
                <td class="center">
                    {if !$institution->site}<a href="{$WWWROOT}admin/users/institutionstaff.php?institution={$institution->name}">{/if}
                        {$institution->staff}
                    {if !$institution->site}</a>{/if}</td>
                <td class="center">
                    {if !$institution->site}<a href="{$WWWROOT}admin/users/institutionadmins.php?institution={$institution->name}">{/if}
                        {$institution->admins}
                    {if !$institution->site}</a>{/if}</td>
                <td class="center">{if $institution->suspended}<span class="suspended">{str tag="suspendedinstitution" section=admin}</span>{/if}</td>
                <td class="controls">
                        <form action="" method="post">
                                <input type="hidden" name="i" value="{$institution->name}">
                                <input type="hidden" name="edit" value=1>
                                <input type="image" name="edit" title="{str tag="edit"}" src="{theme_image_url filename="btn_edit"}" alt="{str(tag=editspecific arg1=$institution->displayname)|escape:html|safe}">
                        </form>
                        {if $siteadmin && !$institution->members && $institution->name != 'mahara'}
                        <form action="" method="post">
                                <input type="hidden" name="i" value="{$institution->name}">
                                <input type="hidden" name="delete" value="1">
                                <input type="image" name="delete" title="{str tag="delete"}" src="{theme_image_url filename="btn_deleteremove"}" alt="{str(tag=deletespecific arg1=$institution->displayname)|escape:html|safe}">
                        </form>
                        {/if}
                </td>
        </tr>
{/foreach}