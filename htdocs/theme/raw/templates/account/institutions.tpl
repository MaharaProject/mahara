{include file="header.tpl"}
<p class="lead">{str tag="institutionmembershipdescription"}</p>

{if $memberform}
<div class="view-container">
    <h2>{str tag=Memberships}</h2>
    {$memberform|safe}
</div>
{/if}
{if $migrateallowed}
<div class="view-container">
    <h2>{str tag=selfmigrate}</h2>
    {str tag=migrateinstitutionpagelink section=mahara arg1=$migrateurl arg2=$sitename}
</div>
{/if}
{if $requestedform}
<div class="view-container">
    <h2>{str tag=Requests}</h2>
    <p class="lead">{str tag="institutionmembershiprequestsdescription"}</p>
    {$requestedform|safe}
</div>
{/if}

{if $invitedform}
<div class="view-container">
    <h2>{str tag=Invitations}</h2>
    <p class="lead">{str tag="institutionmembershipinvitedescription"}</p>
    {$invitedform|safe}
</div>
{/if}
    
{if $joinform}
<div class="view-container">
    <h2>{str tag="requestmembershipofaninstitution"}</h2>
    <p class="lead">{str tag="institutionmembershipjoindescription"}</p>
    {$joinform|safe}
</div>
{/if}

{include file="footer.tpl"}

