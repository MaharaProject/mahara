{include file="header.tpl"}
<div class="card card-default">
    <h2 class="card-heading">{str tag=associatewithaninstitution section=group args=$groupname}</h2>
    <div class="card-body">
        {$institutionform|safe}
    </div>
</div>
<div class="card card-default">
    <h2 class="card-heading">{str tag=groupquotas section=admin args=$groupname}</h2>
    <div class="card-body">
        <p>
            {str tag=managegroupquotadescription1 section=admin}
        </p>
        {$quotasform|safe}
    </div>
</div>
<div class="card card-default">
    <h2 class="card-heading">{str tag=groupadminsforgroup section=admin args=$groupname}</h2>
    <div class="card-body userlistform">
        <p class="lead">{str tag=managegroupdescription1 section=admin}</p>
        {$managegroupform|safe}
    </div>
</div>
{include file="footer.tpl"}
