{if !empty($results.data)}
    <h2>{str tag="Results"}</h2>
    <table id="searchresults" class="tablerenderer fullwidth listing">
        <thead>
          {mahara_pagelinks offset=$results.offset limit=$results.limit count=$results.count url=$pagebaseurl assign=pagelinks}
          {if (!empty($pagelinks))}
          <tr class="search-results-pages">
            <td colspan="{$ncols}">
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
          <tr class="{cycle values="r0,r1"}">
          {foreach from=$cols key=f item=c}
            <td{if (!empty($c.class))} class="{$c.class}"{/if}>{if empty($c.template)}{$r[$f]|escape}{else}{eval var=$c.template}{/if}</td> 
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
