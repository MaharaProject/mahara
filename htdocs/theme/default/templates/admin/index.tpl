{include file='header.tpl'}

{include file="columnfullstart.tpl"}
<h2>{str tag="administration" section=admin}</h2>

<div id="adminhome">
{if $upgrades}
<h3>{str tag="upgrades" section=admin}</h3>
<div id="runupgrade">
<div class="fr"><span class="upgradeicon"><a href="upgrade.php">{str tag=runupgrade section=admin}</a></span></div>
<h4>{str tag=thefollowingupgradesareready section=admin}</h4>
<table cellspacing="0">
    <tr>
        <th>Plugin</th>
        <th>From</th>
        <th>To</th>
    </tr>
{foreach from=$upgrades key=key item=upgrade}
{if $key != 'disablelogin'}
    <tr>
        <td><strong>{$key|hsc}</strong></td>
        <td>{$upgrade->fromrelease} ({$upgrade->from})</td>
        <td>{$upgrade->torelease} ({$upgrade->to})</td>
    </tr>
{/if}
{/foreach}
</table>
</div>
{/if}<ul>
    <li><h3>{str tag=configsite section=admin}</h3>
    <ul>
        <li><strong><a href="{$WWWROOT}admin/site/options.php">{str tag=siteoptions section=admin}</a></strong> - {str tag=siteoptionsdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/site/pages.php">{str tag=sitepages section=admin}</a></strong> - {str tag=sitepagesdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/site/menu.php">{str tag=sitemenu section=admin}</a></strong> - {str tag=sitemenudescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/site/files.php">{str tag=adminfiles section=admin}</a></strong> - {str tag=adminfilesdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/site/networking.php">{str tag=networking section=admin}</a></strong> - {str tag=networkingdescription section=admin}</li>
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
        <li><strong><a href="{$WWWROOT}admin/users/search.php">{str tag=usersearch section=admin}</a></strong> - {str tag=usersearchdescription section=admin}</li>
    </ul>
    </li>
    <li><h3>{str tag=configextensions section=admin}</h3>
    <ul>
        <li><strong><a href="{$WWWROOT}admin/extensions/plugins.php">{str tag=pluginadmin section=admin}</a></strong> - {str tag=pluginadmindescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/extensions/templates.php">{str tag=templatesadmin section=admin}</a></strong> - {str tag=templatesadmindescription section=admin}</li>
    </ul>
    </li>
</ul></div>


{include file="columnfullend.tpl"}

{include file='footer.tpl'}
