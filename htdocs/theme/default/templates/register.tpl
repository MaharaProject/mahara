{include file="header.tpl"}
{include file="columnfullstart.tpl"}
			<h2>{str tag=register}</h2>
			
			{if $register_form}
			<p>{str tag=registerstep1description}</P>
			
			{$register_form}
			{elseif $register_profile_form}
			{$register_profile_form}
			{/if}
{include file="columnfullend.tpl"}
{include file="footer.tpl"}
