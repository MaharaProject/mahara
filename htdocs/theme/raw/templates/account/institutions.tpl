{include file="header.tpl"}
<p>{str tag="institutionmembershipdescription"}</p>
{$memberform}
{$requestedform}
{$invitedform}
{if $joinform}
  <h3>{str tag="requestmembershipofaninstitution"}</h3>
  {$joinform}
{/if}
{include file="footer.tpl"}
