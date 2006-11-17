{include file="header.tpl"}

<h2>{str tag=register}</h2>

{if $register_form}
<p>{str tag=registerdescription}</h2>

{$register_form}
{elseif $register_profile_form}
{$register_profile_form}
{/if}

{include file="footer.tpl"}
