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
          {mahara_pagelinks offset=$results.offset limit=$results.limit count=$results.count url=$pagebaseurl assign=pagelinks}
          {if ($pagelinks)}
          <tr class="search-results-pages">
            <td colspan="{$ncols}">
            {$pagelinks|safe}
            </td>
          </tr>
          {/if}
          <tr>
          {foreach from=$cols key=f item=c}
          {if !$c.name}
            <th></th>
          {elseif $c.nosort}
            <th>{$c.name}</th>
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
          <tr class="{cycle values='r0,r1'}">
          {foreach from=$cols key=f item=c}{strip}
            <td{if $c.class} class="{$c.class}"{/if}>
            {if !$c.template}
              {$r[$f]}
            {else}
              {auto_escape off}
              {* auto_escape off seems to be required to eval these templates without errors;
                 somehow the variables output inside them are getting escaped anyway. *}
              {eval var=$c.template}
              {/auto_escape}
            {/if}
            </td>{/strip}
          {/foreach}
          </tr>
        {/foreach}
        </tbody>
          {if $pagelinks}
        <tfoot>
          <tr class="search-results-pages">
            <td colspan={$ncols}>
            {$pagelinks|safe}
            </td>
          </tr>
        </tfoot>
          {/if}
    </table>
{else}
    <div>{str tag="noresultsfound"}</div>
{/if}
