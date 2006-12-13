{include file='header.tpl'}

<div id="column-full">
<div class="content">
<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span 
class="cnr-bl"><span class="cnr-br">
	<div class="maincontent">
<h2>{str tag="administration" section=admin}</h2>

<ul>
    <li><h3>{str tag=configsite section=admin}</h3>
    <ul>
        <li><strong><a href="{$WWWROOT}admin/site/options.php">{str tag=siteoptions section=admin}</a></strong> - {str tag=siteoptionsdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/site/pages.php">{str tag=sitepages section=admin}</a></strong> - {str tag=sitepagesdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/site/menu.php">{str tag=sitemenu section=admin}</a></strong> - {str tag=sitemenudescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/site/files.php">{str tag=adminfiles section=admin}</a></strong> - {str tag=adminfilesdescription section=admin}</li>
    </ul>
    </li>
    <li><h3>{str tag=configusers section=admin}</h3>
    <ul>
        <li><strong><a href="{$WWWROOT}admin/users/suspended.php">{str tag=suspendedusers section=admin}</a></strong> - {str tag=suspendedusersdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/users/staff.php">{str tag=staffusers section=admin}</a></strong> - {str tag=staffusersdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/users/admins.php">{str tag=adminusers section=admin}</a></strong> - {str tag=adminusersdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/users/notifications.php">{str tag=adminnotifications section=admin}</a></strong> - {str tag=adminnotificationsdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/users/institutions.php">{str tag=institutions section=admin}</a></strong> - {str tag=institutionsdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/users/uploadcsv.php">{str tag=uploadcsv section=admin}</a></strong> - {str tag=uploadcsvdescription section=admin}</li>
    </ul>
    </li>
    <li><h3>{str tag=configextensions section=admin}</h3>
    <ul>
        <li><strong><a href="{$WWWROOT}admin/extensions/plugins.php">{str tag=pluginadmin section=admin}</a></strong> - {str tag=pluginadmindescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/extensions/templates.php">{str tag=templatesadmin section=admin}</a></strong> - {str tag=templatesadmindescription section=admin}</li>
    </ul>
    </li>
</ul>

{if $upgrades}
<p><a href="upgrade.php">Run upgrade</a></p>
{/if}

			</div>
		</span></span></span></span></div>	
	</div>
</div>

{include file='footer.tpl'}
