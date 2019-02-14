{include file='header.tpl'}
<p class="lead">{str tag=htmlfiltersdescription section=admin}</p>

<div class="card card-body">
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
</div>
{include file='footer.tpl'}