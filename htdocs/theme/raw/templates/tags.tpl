{include file="header.tpl"}

{if $tags}
    <div class="mytags">
  {foreach from=$tags item=t}
      <a class="tag{if $t->tag == $tag} selected{/if}" style="font-size: {$t->size}em;" href="{$WWWROOT}tags.php?tag={$t->tag|urlencode}">{$t->tag|escape}</a>
  {/foreach}
    </div>
{else}
    <div>{str tag=youhavenottaggedanythingyet}</div>
{/if}

{if !empty($results->data)}
           <table id="results" class="tablerenderer fullwidth">
             <thead>
               <tr><th></th><th></th><th></th></tr>
             </thead>
             <tbody>
              {$results->tablerows}
             </tbody>
           </table>
           {$results->pagination}
{/if}

{include file="footer.tpl"}
