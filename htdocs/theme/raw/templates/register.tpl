{include file="header.tpl"}
			{if $register_form}
			<p>{$registerdescription|clean_html|safe}</P>
			
			{$register_form|safe}
			{if $termsandconditions}<br><br><div>{$termsandconditions|clean_html|safe}</div>{/if}
			{elseif $register_profile_form}
			{$register_profile_form|safe}
			{/if}
{include file="footer.tpl"}
