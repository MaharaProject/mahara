{include file="header.tpl"}

{if $tags}
  <div class="rbuttons"><a class="btn" href="{$WWWROOT}tags.php">{str tag=mytags}</a></div>
  <div class="edittags mytags">
  <h3>{str tag=selectatagtoedit}:</h3>
  {foreach from=$tags item=t}
    <a class="tag{if $t->tag == $tag} selected{/if}" href="{$WWWROOT}edittags.php?tag={$t->tag|urlencode|safe}">{$t->tag|str_shorten_text:30}&nbsp;<span class="tagfreq">({$t->count})</span></a> 
  {/foreach}
  </div>
{else}
    <div>{str tag=youhavenottaggedanythingyet}</div>
{/if}

{if $tag}
<div class="edittag">
	<h3>{str tag=edittag arg1=$tagsearchurl arg2=$tag}</h3>
	<div>{str tag=edittagdescription arg1=$tag}</div>
	{$edittagform|safe}
</div>
<div class="deletetag">
	<h3>{str tag=deletetag arg1=$tagsearchurl arg2=$tag}</h3>
	<div>{str tag=deletetagdescription}</div>
	{$deletetagform|safe}
</div>
{/if}

{include file="footer.tpl"}
