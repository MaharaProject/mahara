{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
<p>{str tag="institutionmembershipdescription"}</p>
{$memberform}
{$requestedform}
{$invitedform}
{if !empty($joinform)}
  <h3>{str tag="requestmembershipofaninstitution"}</h3>
  {$joinform}
{/if}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
