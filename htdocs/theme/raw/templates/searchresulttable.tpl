{if $results.data}
    <div id="setlimit" class="setlimit fr">
      {str tag=resultsperpage}:
    {foreach from=$limitoptions item=l}
      <a href="?limit={$l}"{if $l == $results.limit} class="selected"{/if}>{$l}</a>
    {/foreach}
    </div>
    <h2>{str tag="Results"}</h2>
    <table id="searchresults" class="tablerenderer fullwidth listing">
        <thead>
          <tr class="search-results-pages">
            <td colspan="{$ncols}">
            {$results.pagination.html|safe}
            </td>
          </tr>
          <tr>
          {foreach from=$cols key=f item=c}
            <th class="{if $c.sort}search-results-sort-column{if $f == $sortby} {$sortdir}{/if}{/if}{if $c.class} {$c.class}{/if}">
          {if $c.sort}
              <a href="{$searchurl}&sortby={$f}&sortdir={if $f == $sortby && $sortdir == 'asc'}desc{else}asc{/if}">{$c.name}</a>
          {else}
              {$c.name}
          {/if}
          {if $c.headhtml}<div style="font-weight: normal;">{$c.headhtml|safe}</div>{/if}
            </th>
          {/foreach}
          </tr>
        </thead>
        <tbody>
        {foreach from=$results.data item=r}
          <tr class="{cycle values='r0,r1'}">
          {foreach from=$cols key=f item=c}{strip}
            <td{if $c.class} class="{$c.class}"{/if}>
            {if !$c.template}
              {$r[$f]}
            {else}
              {include file=$c.template r=$r}
            {/if}
            </td>{/strip}
          {/foreach}
          </tr>
        {/foreach}
        </tbody>
        <tfoot>
          <tr class="search-results-pages">
            <td colspan={$ncols}>
            {$results.pagination.html|safe}
            </td>
          </tr>
        </tfoot>
    </table>
{else}
    <div>{str tag="noresultsfound"}</div>
{/if}
