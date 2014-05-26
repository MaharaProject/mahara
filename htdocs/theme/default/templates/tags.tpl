{include file="header.tpl"}

{if $tags}
  <div class="rbuttons"><a class="btn" href="{$WWWROOT}edittags.php">{str tag=edittags}</a></div>
  <div class="tabswrap"><h3 class="rd-tab-title"><a href="#">{str tag=tabs}<span class="rd-tab"></span></a></h3><ul class="in-page-tabs">
  {foreach from=$tagsortoptions key=tagsortfield item=selectedsort name=tagsortoptions}
    <li{if $selectedsort} class="current-tab"{/if}><a href="{$WWWROOT}tags.php?ts={$tagsortfield}"{if $selectedsort} class="current-tab"{/if}>{str tag=sort$tagsortfield} <span class="accessible-hidden">({str tag=tab}{if $selectedsort} {str tag=selected}{/if})</span></a></li>
  {/foreach}
  </ul></div>
  <div class="subpage mytags">
  {foreach from=$tags item=t}
    <a id="tag:{$t->tag|urlencode|safe}" class="tag{if $t->tag == $tag} selected{/if}" href="{$WWWROOT}tags.php?tag={$t->tag|urlencode|safe}">{$t->tag|str_shorten_text:30}&nbsp;<span class="tagfreq">({$t->count})</span></a> 
  {/foreach}
  </div>
{else}
    <div>{str tag=youhavenottaggedanythingyet}</div>
{/if}

         <div id="results_container" class="rel tag-results">
             <h2 id="results_heading">{str tag=searchresultsfor} <a class="tag" href="{$WWWROOT}tags.php{if $tag}{$results->queryprefix}tag={$tag|urlencode|safe}{/if}">{if $tag}{$tag|str_shorten_text:50}{else}{str tag=alltags}{/if}</a></h2>
           <div class="rbuttons"><a class="btn edit-tag{if !$tag} hidden{/if}" href="{$WWWROOT}edittags.php?tag={$tag|urlencode|safe}">{str tag=editthistag}</a></div>

           <div id="results_sort"><strong>{str tag=sortresultsby}</strong>
{foreach from=$results->sortcols item=sortfield name=sortcols}
           <a href="{$results->baseurl}{$results->queryprefix}type={$results->filter}&sort={$sortfield}"{if $results->sort == $sortfield} class="selected"{/if}>{str tag=$sortfield}</a>{if !$.foreach.sortcols.last} <span class="sep">|</span>{/if}
{/foreach}
           </div>
           <div id="resultswrap">
           <div id="results_filter"><div class="filtertitle">{str tag=filterresultsby}</div>
{foreach from=$results->filtercols key=filtername item=filterdisplay name=filtercols}
           <div class="filtername"><a href="{$results->baseurl}{$results->queryprefix}sort={$results->sort}&type={$filtername}"{if $results->filter == $filtername} class="selected"{/if}>{$filterdisplay}</a></div>{if !$.foreach.filtercols.last}{/if}
{/foreach}
           </div>
           <div id="results" class="tablerenderer fullwidth listing">
{if $results->data}
              {$results->tablerows|safe}
{/if}
           </div>
           {$results->pagination|safe}
            <div class="cb"></div>
         </div>
         </div>

{include file="footer.tpl"}
