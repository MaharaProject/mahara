            {if !empty($results.data)}
                <h3>{str tag="results"}</h3>
                <table id="searchresults" class="tablerenderer">
                    <thead>
                      {if !empty($pagelinks)}
                      <tr class="search-results-pages">
                        <td colspan=7>{$pagelinks}</td>
                      </tr>
                      {/if}

                      <tr>
                        <th></th>
                        {foreach from=$fieldnames item=f}
                        <th class="search-results-sort-column{if $f == $sortby} {$sortdir}{/if}"><a href="?{$params}&sortby={$f}&sortdir={if $f == $sortby && $sortdir == 'asc'}desc{else}asc{/if}">{str tag="$f"}</a></th>
                        {/foreach}
                        <th></th>
                      </tr>

                    </thead>
                    <tbody>
                    {foreach from=$results.data item=r}
                      <tr>
                        <td><img src="{$WWWROOT}thumb.php?type=profileicon&size=40x40&id={$r.id}" alt="{str tag=profileimage}" /></td>
                        <td>{$r.firstname}</td>
                        <td>{$r.lastname}</td>
                        <td><a href="{$WWWROOT}user/view.php?id={$r.id}">{$r.username}</a></td>
                        <td>{$r.email}</td>
                        <td>{$institutions[$r.institution]->displayname}</td>
                        <td><a class="suspend-user-link" href="{$WWWROOT}admin/users/suspend.php?id={$r.id}">{str tag=suspenduser section=admin}</a></td>
                      </tr>
                    {/foreach}
                    </tbody>
                  {if !empty($pagelinks)}
                    <tfoot>
                      <tr class="search-results-pages">
                        <td colspan=7>{$pagelinks}</td>
                      </tr>
                    </tfoot>
                  {/if}
                </table>
            {else}
                <div>{str tag="noresultsfound"}</div>
            {/if}
