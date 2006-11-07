{include file='header.tpl'}

<h2>Administration</h2>

<p>Screens here:</p>

<ul>
    <li><a href="options/">AdminSiteOptions</a>
    <ul>
        <li><a href="options/authentication.php">Admin Authentication</a>
        <ul>
            <li>AdminAuthenticationOptions</li>
        </ul></li>
    </ul>
    <li><a href="editsitepage.php">EditSitePages</a></li>
</ul>

{if $upgrades}
<p><a href="upgrade.php">Run upgrade</a></p>
{/if}
<p><a href="..">parent</a></p>

{include file='footer.tpl'}
