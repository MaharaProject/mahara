            {if !empty($results.data)}
                <h3>{str tag="results"}</h3>
                <table id="searchresults" class="tablerenderer">
                    <thead>
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
                {if count($results.data) < $results.count}
                    <tfoot class="search-results-pages">
                      <tr>
                        <td colspan=7>
                          {if $results.page > $results.prev}
                            <span class="search-results-page prev"><a href="?{$params}&amp;sortby={$sortby}&amp;sortdir={$sortdir}&amp;offset={$results.limit*$results.prev}">{str tag=prevpage}</a></span>
                          {/if}
                          {foreach from=$pagenumbers item=i name=pagenumbers}
                            {if !$smarty.foreach.pagenumbers.first && $prevpagenum < $i-1}...{/if}
                            <span class="search-results-page{if $i == $results.page} selected{/if}"><a href="?{$params}&amp;sortby={$sortby}&amp;sortdir={$sortdir}&amp;offset={$i*$results.limit}">{$i+1}</a></span>
                            {assign var='prevpagenum' value=$i}
                          {/foreach}
                          {if $results.page < $results.next}
                            <span class="search-results-page next"><a href="?{$params}&amp;sortby={$sortby}&amp;sortdir={$sortdir}&amp;offset={$results.limit*$results.next}">{str tag=nextpage}</a></span>
                          {/if}
                        </td>
                      </tr>
                    </tfoot>
                {/if}
                </table>
            {else}
                <div>{str tag="noresultsfound"}</div>
            {/if}
