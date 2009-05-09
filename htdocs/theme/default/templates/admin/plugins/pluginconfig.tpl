{include file='header.tpl'}

<div id="column-right">
</div>

{include file="columnleftstart.tpl"}
			<h2>Plugin Administration: {$plugintype}: {$pluginname}{if $type}: {$type}{/if}</h2>
			{$form}
{include file="columnleftend.tpl"}

{include file='footer.tpl'}
