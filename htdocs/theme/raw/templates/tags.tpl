{include file="header.tpl"}

{if $tags}
  <div class="rbuttons"><a class="btn" href="{$WWWROOT}edittags.php">{str tag=edittags}</a></div>
  <ul class="in-page-tabs">
  {foreach from=$tagsortoptions key=tagsortfield item=selectedsort name=tagsortoptions}
    <li><a href="{$WWWROOT}tags.php?ts={$tagsortfield}" class="tag-sort{if $selectedsort} current-tab{/if}">{str tag=sort$tagsortfield}</a></li>
  {/foreach}
  </ul>
  <div class="subpage mytags">
  {foreach from=$tags item=t}
    <a id="tag:{$t->tag|urlencode|safe}" class="tag{if $t->tag == $tag} selected{/if}" href="{$WWWROOT}tags.php?tag={$t->tag|urlencode|safe}">{$t->tag|str_shorten_text:30}&nbsp;<span class="tagfreq">({$t->count})</span></a> 
  {/foreach}
  </div>
{else}
    <div>{str tag=youhavenottaggedanythingyet}</div>
{/if}

         <div id="results_container" class="rel tag-results">
           <h3 id="results_heading">{str tag=searchresultsfor} <a class="tag" href="{$WWWROOT}tags.php{if $tag}?tag={$tag|urlencode|safe}{/if}">{if $tag}{$tag|str_shorten_text:50}{else}{str tag=alltags}{/if}</a></h3>
           <div class="rbuttons"><a class="btn edit-tag{if !$tag} hidden{/if}" href="{$WWWROOT}edittags.php?tag={$tag|urlencode|safe}">{str tag=editthistag}</a></div>
           <div id="results_sort" class="fl">{str tag=sortresultsby}
{foreach from=$results->sortcols item=sortfield name=sortcols}
           <a href="{$results->baseurl}&type={$results->filter}&sort={$sortfield}"{if $results->sort == $sortfield} class="selected"{/if}>{str tag=$sortfield}</a>{if !$.foreach.sortcols.last} <span class="sep">|</span> {/if}
{/foreach}
           </div>
           <div id="results_filter" class="fr">{str tag=filterresultsby}
{foreach from=$results->filtercols key=filtername item=filterdisplay name=filtercols}
           <a href="{$results->baseurl}&sort={$results->sort}&type={$filtername}"{if $results->filter == $filtername} class="selected"{/if}>{$filterdisplay}</a>{if !$.foreach.filtercols.last} <span class="sep">|</span> {/if}
{/foreach}
           </div>
           <div class="cb"></div>
           <table id="results" class="tablerenderer fullwidth">
             <tbody>
{if $results->data}
              {$results->tablerows|safe}
{/if}
             </tbody>
           </table>
           {$results->pagination|safe}
         </div>

{include file="footer.tpl"}
