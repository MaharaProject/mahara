{auto_escape off}
{include file="header.tpl"}
<p>{str tag="institutionmembershipdescription"}</p>
{if $memberform}
  <h3>{str tag=Memberships}</h3>
  {$memberform}
{/if}
{if $requestedform}
  <h3>{str tag=Requests}</h3>
  {$requestedform}
{/if}
{if $invitedform}
  <h3>{str tag=Invitations}</h3>
  {$invitedform}
{/if}
{if $joinform}
  <h3>{str tag="requestmembershipofaninstitution"}</h3>
  {$joinform}
{/if}
{include file="footer.tpl"}
{/auto_escape}
