{include file="header.tpl"}

{if $tags}
  <div class="rbuttons"><a class="btn" href="{$WWWROOT}tags.php">{str tag=mytags}</a></div>
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
<h2>{str tag=edittag arg1=$tagsearchurl arg2=$tag|escape}</h2>
<p>{str tag=edittagdescription arg1=$tag|escape}</p>
{$edittagform}
<h2>{str tag=deletetag arg1=$tagsearchurl arg2=$tag|escape}</h2>
<p>{str tag=deletetagdescription}</p>
{$deletetagform}
{/if}

{include file="footer.tpl"}
