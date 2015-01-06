{include file="header.tpl"}

{if $GROUP->description}
	<div class="groupdescription">{$GROUP->description|clean_html|safe}</div>
{/if}

<div class="grouphomepage">
{$viewcontent|safe}
</div>
<div class="cb"></div>

{include file="footer.tpl"}
