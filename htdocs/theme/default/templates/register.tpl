{include file="header.tpl"}
{include file="columnfullstart.tpl"}
			{if $register_form}
			<p>{str tag=registerstep1description}</P>
			
			{$register_form}
			{elseif $register_profile_form}
			{$register_profile_form}
			{/if}
{include file="columnfullend.tpl"}
{include file="footer.tpl"}
