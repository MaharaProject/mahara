{include file="header.tpl"}

{if $tags}
    <div class="mytags">
  {foreach from=$tags item=t}
      <a id="tag:{$t->tag}" class="tag{if $t->tag == $tag} selected{/if}" href="{$WWWROOT}tags.php?tag={$t->tag|urlencode}">{$t->tag|escape}&nbsp;<span class="tagfreq">({$t->count})</span></a> 
  {/foreach}
    </div>
{else}
    <div>{str tag=youhavenottaggedanythingyet}</div>
{/if}

           <h6 id="results_heading"{if !$tag} class="hidden"{/if}>{str tag=searchresultsfor}: <a class="tag" href="{$WWWROOT}tags.php?tag={$tag|urlencode}">{$tag|escape}</a></h4>
           <table id="results" class="tablerenderer fullwidth{if !$tag} hidden{/if}">
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

{include file="footer.tpl"}
