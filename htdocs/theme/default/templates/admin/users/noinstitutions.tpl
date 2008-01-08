{include file="header.tpl"}

{include file="columnfullstart.tpl"}
	
<h2>{str tag="noinstitutions" section="admin"}</h2>
<p>{str tag="noinstitutionsdescription" section="admin"}</p>
<div class="institutioneditbuttons">
<form action="{$WWWROOT}admin/users/institutions.php" method="post">
    <input type="submit" class="submit" name="add" value="{str tag="addinstitution" section="admin"}" id="admininstitution_add">
</form>
</div>

{include file="columnfullend.tpl"}

{include file="footer.tpl"}
