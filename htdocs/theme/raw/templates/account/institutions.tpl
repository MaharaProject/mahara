{include file="header.tpl"}
<p class="intro">{str tag="institutionmembershipdescription"}</p>
{if $memberform}
  <h2>{str tag=Memberships}</h2>
  {$memberform|safe}
{/if}
{if $requestedform}
  <h2>{str tag=Requests}</h2>
  {$requestedform|safe}
{/if}
{if $invitedform}
  <h2>{str tag=Invitations}</h2>
  {$invitedform|safe}
{/if}
{if $joinform}
  <h2>{str tag="requestmembershipofaninstitution"}</h2>
  {$joinform|safe}
{/if}
{include file="footer.tpl"}

