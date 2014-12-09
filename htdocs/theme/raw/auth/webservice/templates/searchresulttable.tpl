{if $results.data}
    <div id="setlimit" class="setlimit fr">
      {str tag=resultsperpage}:
    {foreach from=$limitoptions item=l}
      <a href="?suid={$suid}&token={$token}&limit={$l}"{if $l == $results.limit} class="selected"{/if}>{$l}</a>
    {/foreach}
    </div>
    <h2>{str tag="Results"}</h2>
      <table id="searchresults" class="tablerenderer fullwidth listing">
          <thead>
              <tr>
                  {foreach from=$columns key=f item=c}
                  BLAH
                  <th class="{if $c.sort}search-results-sort-column{if $f == $sortby} {$sortdir}{/if}{/if}{if $c.class} {$c.class}{/if}">
                      {if $c.sort}
                          <a href="{$searchurl}&sortby={$f}&sortdir={if $f == $sortby && $sortdir == 'asc'}desc{else}asc{/if}">
                              {$c.name}
                              <span class="accessible-hidden">({str tag=sortby} {if $f == $sortby && $sortdir == 'asc'}{str tag=descending}{else}{str tag=ascending}{/if})</span>
                          </a>
                      {else}
                          {$c.name}
                      {/if}
                      {if $c.help}
                          {$c.helplink|safe}
                      {/if}
                      {if $c.headhtml}<div style="font-weight: normal;">{$c.headhtml|safe}</div>{/if}
                  </th>
                  {/foreach}
              </tr>
          </thead>
          <tbody>
              {$results|safe}
          </tbody>
      </table>
      {$pagination|safe}
{else}
    <div>{str tag="noresultsfound"}</div>
{/if}
