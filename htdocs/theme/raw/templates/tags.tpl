{include file="header.tpl"}

{if $tags}
    <div class="mytags">
  {foreach from=$tags item=t}
      <a class="tag{if $t->tag == $tag} selected{/if}" style="font-size: {$t->size}em;" href="{$WWWROOT}tags.php?tag={$t->tag|urlencode}" title="{str tag=numitems arg1=$t->count}">{$t->tag|escape}</a>
  {/foreach}
    </div>
{else}
    <div>{str tag=youhavenottaggedanythingyet}</div>
{/if}

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

{include file="footer.tpl"}
