{include file="header.tpl"}

{include file="columnfullstart.tpl"}
<div id="edituser">
    <h2>{str tag=addusers section=admin}</h2>
    <p>{str tag=adduserspagedescriptioncsv section=admin}</p>
    <form action="{$WWWROOT}admin/users/uploadcsv.php"><input type="submit" class="submit" value="{str tag=uploadcsv section=admin}" /></form>
    <p>{str tag=adduserspagedescription section=admin}</p>
    <h3>{str tag=adduser section=admin}</h3>
    {$form}
</div>
{include file="columnfullend.tpl"}
{include file="footer.tpl"}

