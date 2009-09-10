{include file="header.tpl"}

{if $tags}
  <div class="rbuttons"><a href="{$WWWROOT}edittags.php">{str tag=edittags}</a></div>
  <ul class="in-page-tabs">
  {foreach from=$tagsortoptions key=tagsortfield item=selectedsort name=tagsortoptions}
    <li><a href="{$WWWROOT}tags.php?ts={$tagsortfield}" class="tag-sort{if $selectedsort} current-tab{/if}">{str tag=sort$tagsortfield}</a></li>
  {/foreach}
  </ul>
  <div class="subpage mytags">
  {foreach from=$tags item=t}
    <a id="tag:{$t->tag}" class="tag{if $t->tag == $tag} selected{/if}" href="{$WWWROOT}tags.php?tag={$t->tag|urlencode}">{$t->tag|str_shorten_text:30|escape}&nbsp;<span class="tagfreq">({$t->count})</span></a> 
  {/foreach}
  </div>
{else}
    <div>{str tag=youhavenottaggedanythingyet}</div>
{/if}

         <div id="results_container" class="tag-results{if !$tag} hidden{/if}">
           <h2 id="results_heading">{str tag=searchresultsfor} <a class="tag" href="{$WWWROOT}tags.php?tag={$tag|urlencode}">{$tag|str_shorten_text:50|escape}</a></h2>
           <div id="results_sort">{str tag=sortresultsby}
{foreach from=$results->sortcols item=sortfield name=sortcols}
           <a href="{$results->baseurl}&type={$results->filter}&sort={$sortfield}"{if $results->sort == $sortfield} class="selected"{/if}>{str tag=$sortfield}</a>{if !$smarty.foreach.sortcols.last} | {/if}
{/foreach}
           </div>
           <div id="results_filter">{str tag=filterresultsby}
{foreach from=$results->filtercols key=filtername item=filterdisplay name=filtercols}
           <a href="{$results->baseurl}&sort={$results->sort}&type={$filtername}"{if $results->filter == $filtername} class="selected"{/if}>{$filterdisplay}</a>{if !$smarty.foreach.filtercols.last} | {/if}
{/foreach}
           </div>
           <table id="results" class="tablerenderer fullwidth">
             <thead>
               <tr><th></th><th></th><th></th></tr>
             </thead>
             <tbody>
{if !empty($results->data)}
              {$results->tablerows}
{/if}
             </tbody>
           </table>
           {$results->pagination}
         </div>

{include file="footer.tpl"}
