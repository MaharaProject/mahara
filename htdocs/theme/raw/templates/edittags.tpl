{include file="header.tpl"}

{if $tags}
  <div class="edittags mytags">
  <div>{str tag=selectatagtoedit}:</div>
  {foreach from=$tags item=t}
    <a class="tag{if $t->tag == $tag} selected{/if}" href="{$WWWROOT}edittags.php?tag={$t->tag|urlencode}">{$t->tag|str_shorten_text:30|escape}&nbsp;<span class="tagfreq">({$t->count})</span></a> 
  {/foreach}
  </div>
{else}
    <div>{str tag=youhavenottaggedanythingyet}</div>
{/if}

{if $tag}
<h3>{str tag=edittag arg1=$tag|escape}</h3>
<p>{str tag=edittagdescription arg1=$tag|escape}</p>
{$edittagform}
<h3>{str tag=deletetag arg1=$tag|escape}</h3>
<p>{str tag=deletetagdescription}</p>
{$deletetagform}
{/if}

{include file="footer.tpl"}
