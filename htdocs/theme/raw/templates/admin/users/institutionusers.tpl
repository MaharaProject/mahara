{include file="header.tpl"}
<p>{str tag="institutionmemberspagedescription" section="admin"}</p>
<p>{$instructions}</p>
{$usertypeselector|safe}
<div class="userlistform">
{$institutionusersform|safe}
</div>
{include file="footer.tpl"}
