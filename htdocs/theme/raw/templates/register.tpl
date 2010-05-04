{auto_escape off}
{include file="header.tpl"}
			{if $register_form}
			<p>{str tag=registerstep1description}</P>
			
			{$register_form}
			{elseif $register_profile_form}
			{$register_profile_form}
			{/if}
{include file="footer.tpl"}
{/auto_escape}
