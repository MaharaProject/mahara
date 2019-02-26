{foreach from=$institutions item=institution}
        <tr class="{cycle values='r0,r1'}">
                <td>
                    {if !$institution->site}<a href="{$WWWROOT}institution/index.php?institution={$institution->name}">{/if}
                        {$institution->displayname}
                    {if !$institution->site}</a>{/if}
                </td>
                <td class="center">
                    {$institution->name}
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
                        <form action="" method="post"  class="btn-group float-right">
                          <input type="hidden" name="i" value="{$institution->name}">
                          {if $webserviceconnections}
                              <a class="btn-secondary btn-sm button btn btn-group-first" href="{$WWWROOT}webservice/admin/connections.php?i={$institution->name}">
                                  <span class="icon icon-plug icon-lg text-default" role="presentation" aria-hidden="true"></span>
                                  <span class="sr-only">
                                      {str(tag=connectspecific arg1=$institution->displayname)|escape:html|safe}
                                  </span>
                              </a>
                          {/if}
                            <button type="submit" name="edit" value="1" class="btn-secondary btn-sm button btn
                            {if !($siteadmin && !$institution->members && $institution->name != 'mahara')} no-delete-btn btn-group-last{/if}
                            {if !$webserviceconnections} btn-group-first {/if}"
                            alt="{str(tag=editspecific arg1=$institution->displayname)|escape:html|safe}">
                                 <span class="icon icon-cog icon-lg text-default" role="presentation" aria-hidden="true"></span>
                                 <span class="sr-only">
                                     {str tag="edit"}
                                 </span>
                             </button>
                         {if $siteadmin && !$institution->members && $institution->name != 'mahara'}
                            <button type="submit" name="delete" value="1" class="btn-secondary btn-sm button btn btn-group-last" alt="{str(tag=deletespecific arg1=$institution->displayname)|escape:html|safe}">

                                <span class="icon icon-trash text-danger icon-lg" role="presentation" aria-hidden="true"></span>
                                <span class="sr-only">
                                    {str tag="delete"}
                                </span>
                            </button>
                        {/if}
                        </form>
                </td>
        </tr>
{/foreach}
