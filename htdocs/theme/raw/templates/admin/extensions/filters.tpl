{include file='header.tpl'}

{include file="columnfullstart.tpl"}

<h2>{$heading}</h2>

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
<div>{$reloadform}</div>

{include file="columnfullend.tpl"}

{include file='footer.tpl'}
