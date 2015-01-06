{include file='header.tpl'}

<p>{str tag=htmlfiltersdescription section=admin}</p>

{if $filters}
<h3>{str tag=installed section=admin}:</h3>
<ul>
  {foreach from=$filters item=filter}
  <li>{$filter->site}</li>
  {/foreach}
</ul>
{else}
<p>{str tag=nofiltersinstalled section=admin}</p>
{/if}

<p>{$newfiltersdescription}</p>
<div>{$reloadform|safe}</div>

{include file='footer.tpl'}

