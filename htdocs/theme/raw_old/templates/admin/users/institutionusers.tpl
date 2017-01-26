{include file="header.tpl"}
<p class="lead">{str tag="institutionmemberspagedescription" section="admin"}</p>
<div class="panel panel-default panel-body">
    <p>{$instructions}</p>
    {$usertypeselector|safe}
    <div class="userlistform">
        {$institutionusersform|safe}
    </div>
</div>
{include file="footer.tpl"}
