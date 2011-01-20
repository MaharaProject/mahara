{include file="header.tpl"}
<p class="intro">{str tag="institutionmembershipdescription"}</p>
{if $memberform}
  <h3>{str tag=Memberships}</h3>
  {$memberform|safe}
{/if}
{if $requestedform}
  <h3>{str tag=Requests}</h3>
  {$requestedform|safe}
{/if}
{if $invitedform}
  <h3>{str tag=Invitations}</h3>
  {$invitedform|safe}
{/if}
{if $joinform}
  <h3>{str tag="requestmembershipofaninstitution"}</h3>
  {$joinform|safe}
{/if}
{include file="footer.tpl"}

