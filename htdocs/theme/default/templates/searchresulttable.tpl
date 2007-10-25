{if !empty($results.data)}
    <h3>{str tag="Results"}</h3>
    <table id="searchresults" class="tablerenderer">
        <thead>
          {mahara_pagelinks offset=$results.offset limit=$results.limit count=$results.count url=$pagebaseurl assign=pagelinks}
          {if (!empty($pagelinks))}
          <tr class="search-results-pages">
            <td colspan={$ncols}>
            {$pagelinks}
            </td>
          </tr>
          {/if}
          <tr>
          {foreach from=$cols key=f item=c}
          {if empty($c.name)}
            <th></th>
          {else}
            <th class="search-results-sort-column{if $f == $sortby} {$sortdir}{/if}">
              <a href="{$searchurl}&sortby={$f}&sortdir={if $f == $sortby && $sortdir == 'asc'}desc{else}asc{/if}">{$c.name}</a>
            </th>
          {/if}
          {/foreach}
          </tr>
        </thead>
        <tbody>
        {foreach from=$results.data item=r}
          <tr>
          {foreach from=$cols key=f item=c}
            {if empty($c.template)}
            <td>{$r[$f]}</td>
            {else}
            <td>{eval var=$c.template}</td>
            {/if}
          {/foreach}
          </tr>
        {/foreach}
        </tbody>
          {if (!empty($pagelinks))}
        <tfoot>
          <tr class="search-results-pages">
            <td colspan={$ncols}>
            {$pagelinks}
            </td>
          </tr>
        </tfoot>
          {/if}
    </table>
{else}
    <div>{str tag="noresultsfound"}</div>
{/if}
