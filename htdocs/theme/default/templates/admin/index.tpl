{include file='header.tpl'}

<h2>{str tag="administration"}</h2>

<ul>
    <li><strong><a href="options/">{str tag="adminsiteoptions" section="admin"}</a></strong><br>
    {str tag="adminsiteoptionsdescription" section="admin"}</li>
    <li>AdminSiteEditor - ???</li>
    <li><a href="institutions.php">Institutions</a></li>
    <li><a href="editsitepage.php">Site Pages</a></li>
    <li><a href="editmenu.php">Site Menu</a></li>
    <li><a href="plugins">Administer Plugins</a></li>
</ul>

{if $upgrades}
<p><a href="upgrade.php">Run upgrade</a></p>
{/if}

{include file='footer.tpl'}
