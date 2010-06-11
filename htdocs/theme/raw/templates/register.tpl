{auto_escape off}
{include file="header.tpl"}
			{if $register_form}
			<p>{$registerdescription}</P>
			
			{$register_form}
			{if $termsandconditions}<br><br><div>{$termsandconditions}</div>{/if}
			{elseif $register_profile_form}
			{$register_profile_form}
			{/if}
{include file="footer.tpl"}
{/auto_escape}
