{include file="header.tpl"}
<p class="lead">{str tag="institutionmembershipdescription"}</p>


{if $memberform}
<div class="view-container">
    <h2>{str tag=Memberships}</h2>
    {$memberform|safe}
</div>
{/if}

{if $requestedform}
<div class="view-container">
    <h2>{str tag=Requests}</h2>
    {$requestedform|safe}
</div>
{/if}

{if $invitedform}
<div class="view-container">
    <h2>{str tag=Invitations}</h2>
    {$invitedform|safe}
</div>
{/if}
    
{if $joinform}
<div class="view-container">
    <h2>{str tag="requestmembershipofaninstitution"}</h2>
    {$joinform|safe}
</div>
{/if}

{include file="footer.tpl"}

