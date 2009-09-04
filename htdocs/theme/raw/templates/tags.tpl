{include file="header.tpl"}

{if $tags}
  <ul class="in-page-tabs">
  {foreach from=$tagsortoptions key=tagsortfield item=selectedsort name=tagsortoptions}
    <li><a href="{$WWWROOT}tags.php?ts={$tagsortfield}" class="tag-sort{if $selectedsort} current-tab{/if}">{str tag=sort$tagsortfield}</a></li>
  {/foreach}
  </ul>
  <div class="subpage mytags">
  {foreach from=$tags item=t}
    <a id="tag:{$t->tag}" class="tag{if $t->tag == $tag} selected{/if}" href="{$WWWROOT}tags.php?tag={$t->tag|urlencode}">{$t->tag|escape}&nbsp;<span class="tagfreq">({$t->count})</span></a> 
  {/foreach}
  </div>
{else}
    <div>{str tag=youhavenottaggedanythingyet}</div>
{/if}

         <div id="results_container" class="tag-results{if !$tag} hidden{/if}">
           <h2 id="results_heading">{str tag=searchresultsfor} <a class="tag" href="{$WWWROOT}tags.php?tag={$tag|urlencode}">{$tag|escape}</a></h4>
           <div id="results_sort">{str tag=sortresultsby}
{foreach from=$results->sortcols item=sortfield name=sortcols}
           <a href="{$results->baseurl}&sort={$sortfield}"{if $results->sort == $sortfield} class="selected"{/if}>{str tag=$sortfield}</a>{if !$smarty.foreach.sortcols.last} | {/if}
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
