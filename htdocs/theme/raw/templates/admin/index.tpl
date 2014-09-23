{include file='header.tpl'}

{if $warnings}
<div class="admin-warning-box">
<h3>{str tag="warnings" section=admin}</h3>
<ul>
{foreach from=$warnings key=key item=warning}
    <li>{$warning|safe}</li>
{/foreach}
</ul>
</div>
{/if}

{if $upgrades['settings']['toupgradecount']}
<div class="message" id="runupgrade">
<h3>{str tag="upgrades" section=admin}</h3>
<div class="fr"><span class="upgrade"><a class="btn" href="upgrade.php">{str tag=runupgrade section=admin}</a></span></div>
<h4>{str tag=thefollowingupgradesareready section=admin}</h4>
<table id="upgradestable" class="fullwidth">
    <thead>
    <tr>
        <th>{str tag=Plugin section=admin}</th>
        <th>{str tag=From}</th>
        <th>{str tag=To}</th>
    </tr>
    </thead>
    <tbody>
{foreach from=$upgrades key=key item=upgrade}
{if $key != 'settings' && $upgrade->upgrade}
    <tr>
        <td><strong>{$key}</strong></td>
        <td>{$upgrade->fromrelease} ({$upgrade->from})</td>
        <td>{$upgrade->torelease} ({$upgrade->to})</td>
    </tr>
{/if}
{/foreach}
    </tbody>
</table>
</div>
{/if}

{if $upgrades['settings']['newinstallcount']}
<div class="warning" id="runinstall">
<h3>{str tag="newplugins" section=admin}</h3>
<div class="fr"><span class="upgrade"><a class="btn" href="extensions/plugins.php">{str tag=gotoinstallpage section=admin}</a></span></div>
<h4>{str tag=thefollowingpluginsareready section=admin}</h4>
<table id="upgradestable" class="fullwidth">
    <thead>
    <tr>
        <th>{str tag=Plugin section=admin}</th>
        <th>{str tag=From}</th>
        <th>{str tag=To}</th>
    </tr>
    </thead>
    <tbody>
{foreach from=$upgrades['settings']['newinstalls'] key=key item=upgrade}
<tr>
    <td><strong>{$key}</strong></td>
    <td>{$upgrade->fromrelease}</td>
    <td>{$upgrade->torelease} ({$upgrade->to})</td>
</tr>
{/foreach}
    </tbody>
</table>
</div>
{/if}

<div id="adminhome">
{if $register}
<div class="message" id="register-site">
    <h3>{str tag=registeryourmaharasite section=admin}</h3>
    {str tag=registeryourmaharasitesummary section=admin args=$WWWROOT}
</div>
{/if}

{if $sitedata}
<div class="message" id="site-stats">
  <div><h3>{$sitedata.name}: {str tag=siteinformation section=admin}</h3></div>
  <div><a class="icon-sitestats" href="{$WWWROOT}admin/statistics.php">{str tag=viewfullsitestatistics section=admin}</a></div>
  <div class="cb"></div>
  {include file='admin/stats.tpl' full=0}
</div>
{/if}

<div class="message" id="close-site">
{if $closed}
    <h3>{str tag=reopensite section=admin}</h3>
    {str tag=reopensitedetail section=admin}
{else}
    <h3>{str tag=closesite section=admin}</h3>
    {str tag=closesitedetail section=admin}
{/if}
    {$closeform|safe}
</div>

</div>


<div class="cb"></div>

<div class="admin-home-column fl">

<h3>{str tag=configsite section=admin}</h3>
    <ul>
        <li><strong><a href="{$WWWROOT}admin/site/options.php">{str tag=siteoptions section=admin}</a></strong> - {str tag=siteoptionsdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/site/pages.php">{str tag=staticpages section=admin}</a></strong> - {str tag=staticpagesdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/site/licenses.php">{str tag=sitelicenses section=admin}</a></strong> - {str tag=sitelicensesdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/site/menu.php">{str tag=menus section=admin}</a></strong> - {str tag=menusdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/site/networking.php">{str tag=networking section=admin}</a></strong> - {str tag=networkingdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/site/views.php">{str tag=siteviews section=admin}</a></strong> - {str tag=siteviewsdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}artefact/file/sitefiles.php">{str tag=sitefiles section=admin}</a></strong> - {str tag=sitefilesdescription section=admin}</li>
        {ifconfig key=skins}<li><strong><a href="{$WWWROOT}admin/site/skins.php">{str tag=siteskins section=admin}</a></strong> - {str tag=siteskinsdescription section=admin}</li>{/ifconfig}
        {ifconfig key=skins}<li><strong><a href="{$WWWROOT}admin/site/fonts.php">{str tag=sitefonts section=admin}</a></strong> - {str tag=sitefontsdescription section=admin}</li>{/ifconfig}
        <li><strong><a href="{$WWWROOT}admin/site/cookieconsent.php">{str tag=cookieconsent section=admin}</a></strong> - {str tag=cookieconsentdescription section=admin}</li>
    </ul>

<h3>{str tag=configusers section=admin}</h3>
    <ul>
        <li><strong><a href="{$WWWROOT}admin/users/search.php">{str tag=usersearch section=admin}</a></strong> - {str tag=usersearchdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/users/suspended.php">{str tag=suspendedusers section=admin}</a></strong> - {str tag=suspendedusersdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/users/staff.php">{str tag=sitestaff section=admin}</a></strong> - {str tag=staffusersdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/users/admins.php">{str tag=siteadmins section=admin}</a></strong> - {str tag=adminusersdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/users/notifications.php">{str tag=adminnotifications section=admin}</a></strong> - {str tag=adminnotificationsdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/users/add.php">{str tag=adduser section=admin}</a></strong> - {str tag=adduserdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/users/uploadcsv.php">{str tag=uploadcsv section=admin}</a></strong> - {str tag=uploadcsvdescription section=admin}</li>
    </ul>

</div>

<div class="admin-home-column fr">

<h3>{str tag=managegroups section=admin}</h3>
    <ul>
        <li><strong><a href="{$WWWROOT}admin/groups/groups.php">{str tag=administergroups section=admin}</a></strong> - {str tag=administergroupsdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/groups/groupcategories.php">{str tag=groupcategories section=admin}</a></strong> - {str tag=groupcategoriesdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/groups/uploadcsv.php">{str tag=uploadgroupcsv section=admin}</a></strong> - {str tag=uploadgroupcsvdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/groups/uploadmemberscsv.php">{str tag=uploadgroupmemberscsv section=admin}</a></strong> - {str tag=uploadgroupmemberscsvdescription section=admin}</li>
    </ul>

<h3>{str tag=manageinstitutions section=admin}</h3>
    <ul>
        <li><strong><a href="{$WWWROOT}admin/users/institutions.php">{str tag=Institutions section=admin}</a></strong> - {str tag=institutionsdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/users/institutionusers.php">{str tag=institutionmembers section=admin}</a></strong> - {str tag=institutionmembersdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/users/institutionstaff.php">{str tag=institutionstaff section=admin}</a></strong> - {str tag=institutionstaffdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/users/institutionadmins.php">{str tag=institutionadmins section=admin}</a></strong> - {str tag=institutionadminsdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}view/institutionviews.php">{str tag=institutionviews section=admin}</a></strong> - {str tag=institutionviewsdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}artefact/file/institutionfiles.php">{str tag=institutionfiles section=admin}</a></strong> - {str tag=institutionfilesdescription section=admin}</li>
    </ul>

<h3>{str tag=configextensions section=admin}</h3>
    <ul>
        <li><strong><a href="{$WWWROOT}admin/extensions/plugins.php">{str tag=pluginadmin section=admin}</a></strong> - {str tag=pluginadmindescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/extensions/filter.php">{str tag=htmlfilters section=admin}</a></strong> - {str tag=htmlfiltersdescription section=admin}</li>
        <li><strong><a href="{$WWWROOT}admin/extensions/iframesites.php">{str tag=allowediframesites section=admin}</a></strong> - {str tag=allowediframesitesdescriptionshort section=admin}</li>
       <li><strong><a href="{$WWWROOT}admin/extensions/cleanurls.php">{str tag=cleanurls section=admin}</a></strong> - {str tag=cleanurlsdescriptionshort section=admin}</li>
    </ul>
</div>

<div class="cb"></div>
{include file='footer.tpl'}

