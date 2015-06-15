{include file="header.tpl"}
<p class="lead">{str tag="institutionmembershipdescription"}</p>

<div class="mtxl">
{if $memberform}
  <h2 class="ptxl">{str tag=Memberships}</h2>
  {$memberform|safe}
{/if}
{if $requestedform}
  <h2 class="ptxl">{str tag=Requests}</h2>
  {$requestedform|safe}
{/if}
{if $invitedform}
  <h2 class="ptxl">{str tag=Invitations}</h2>
  {$invitedform|safe}
{/if}
{if $joinform}
  <h2 class="ptxl">{str tag="requestmembershipofaninstitution"}</h2>
  {$joinform|safe}
{/if}

</div>
{include file="footer.tpl"}

