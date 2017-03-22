{include file="header.tpl"}
<div class="panel panel-default">
    <h2 class="panel-heading">{str tag=groupquotas section=admin args=$groupname}</h2>
    <div class="panel-body">
        <p>
            {str tag=managegroupquotadescription1 section=admin}
        </p>
        {$quotasform|safe}
    </div>
</div>
<div class="panel panel-default">
    <h2 class="panel-heading">{str tag=groupadminsforgroup section=admin args=$groupname}</h2>
    <div class="panel-body userlistform">
        <p class="lead">{str tag=managegroupdescription1 section=admin}</p>
        {$managegroupform|safe}
    </div>
</div>
{include file="footer.tpl"}
