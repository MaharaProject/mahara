{auto_escape off}
{include file="header.tpl"}

{if $GROUP->description}
	<div class="groupdescription">{$GROUP->description}</div>
{/if}

<div class="grouphomepage">
{$viewcontent}
</div>

{include file="footer.tpl"}
{/auto_escape}
