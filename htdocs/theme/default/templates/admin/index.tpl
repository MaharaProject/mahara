{include file='header.tpl'}

<h2>Administration</h2>

<p>Screens here:</p>

<ul>
    <li><a href="options/">Site Options</a>
    <li><a href="institutions.php">Institutions</a></li>
    <li><a href="editsitepage.php">Site Pages</a></li>
    <li><a href="editmenu.php">Site Menu</a></li>
    <li><a href="plugins">Administer Plugins</a></li>
</ul>

{if $upgrades}
<p><a href="upgrade.php">Run upgrade</a></p>
{/if}

{include file='footer.tpl'}
