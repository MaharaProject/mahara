{include file="header.tpl"}

<h1>{str tag="noinstitutionsstats" section="admin"}</h1>

<p>{str tag="noinstitutionsstatsdescription1" section="admin" arg1="`$WWWROOT`"}</p>
{if $CANCREATEINST}
<div class="institutioneditbuttons">
<form action="{$WWWROOT}admin/users/institutions.php" method="post">
    <input class="submit btn btn-primary" type="submit" name="add" value="{str tag="addinstitution" section="admin"}" id="admininstitution_add">
</form>
</div>
{/if}

{include file="footer.tpl"}
