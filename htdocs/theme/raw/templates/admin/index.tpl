{include file='header.tpl'}

{if $warnings}
<div class="admin-warning alert alert-warning">
<h3>{str tag="warnings" section=admin}</h3>
<ul>
{foreach from=$warnings key=key item=warning}
    <li>{$warning|safe}</li>
{/foreach}
</ul>
</div>
{/if}

{if $upgrades['settings']['toupgradecount']}
<div class="panel panel-warning" id="">
    <h3 class="panel-heading">{str tag="upgrades" section=admin}</h3>
    <div class="panel-body">
        <p>{str tag=thefollowingupgradesareready section=admin}</p>
        <table id="upgrades-table" class="table">
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
                    <td><strong>{if $upgrade->displayname}{$upgrade->displayname}{else}{$key}{/if}</strong></td>
                    <td>{$upgrade->fromrelease} ({$upgrade->from})</td>
                    <td>{$upgrade->torelease} ({$upgrade->to})</td>
                </tr>
                {/if}
            {/foreach}
            </tbody>
        </table>
        <a class="btn btn-default" href="upgrade.php">{str tag=runupgrade section=admin}</a>
    </div>
</div>
{/if}

{if $upgrades['settings']['newinstallcount']}
<div class="panel panel-warning" id="runinstall">
    <h3 class="panel-heading">{str tag="newplugins" section=admin}</h3>
    <div class="panel-body">
        <p>{str tag=thefollowingpluginsareready section=admin}</p>
        <table id="upgradestable" class="table">
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
                    <td><strong>{if $upgrade->displayname}{$upgrade->displayname}{else}{$key}{/if}</strong></td>
                    <td>{$upgrade->fromrelease}</td>
                    <td>{$upgrade->torelease} ({$upgrade->to})</td>
                </tr>
                {/foreach}
            </tbody>
        </table>
        <a class="btn btn-default" href="extensions/plugins.php">
            {str tag=gotoinstallpage section=admin}
            <span class="icon icon-arrow-right right" role="presentation" aria-hidden="true"></span>
        </a>
    </div>
</div>
{/if}
<div class="panel-items js-masonry" data-masonry-options='{ "itemSelector": ".panel" }'>
    {if $register}

        <div class="panel panel-success register-site">
            <h3 class="panel-heading">{str tag=registeryourmaharasite section=admin} <span class="icon icon-star pull-right" role="presentation" aria-hidden="true"></span></h3>
            <div class="panel-body">
                {str tag=registeryourmaharasitesummary section=admin args=$WWWROOT}
            </div>
            <a class="panel-footer" href="{$WWWROOT}admin/registersite.php">{str tag=Register section=admin} <span class="icon icon-arrow-circle-right pull-right" role="presentation" aria-hidden="true"></span></a>
        </div>

    {/if}

    {if $sitedata}

        <div class="panel panel-info site-stats">
            <h3 class="panel-heading">{$sitedata.name}: {str tag=siteinformation section=admin} <span class="icon icon-area-chart pull-right" role="presentation" aria-hidden="true"></span></h3>
            {include file='admin/stats.tpl' full=0}
            <a class="panel-footer text-small" href="{$WWWROOT}admin/statistics.php">{str tag=viewfullsitestatistics section=admin} <span class="icon icon-arrow-circle-right pull-right" role="presentation" aria-hidden="true"></span></a>
        </div>

    {/if}

    <div class="panel close-site {if $closed}panel-success{else}panel-danger{/if}">
        {if $closed}
            <h3 class="panel-heading">{str tag=reopensite section=admin} <span class="icon icon-lock pull-right" role="presentation" aria-hidden="true"></span></h3>
            <div class="panel-body">
                <p>{str tag=reopensitedetail section=admin}</p>
                {$closeform|safe}
            </div>
        {else}
            <h3 class="panel-heading">{str tag=closesite section=admin} <span class="icon icon-unlock-alt pull-right" role="presentation" aria-hidden="true"></span></h3>
            <div class="panel-body">
                <p>{str tag=closesitedetail section=admin}</p>
                {$closeform|safe}
            </div>
        {/if}
    </div>

    <div class="panel panel-default">
        <h3 class="panel-heading">{str tag=clearcachesheading section=admin} <span class="icon icon-refresh pull-right" role="presentation" aria-hidden="true"></span></h3>
        <div class="panel-body">
            <p>{str tag=cliclearcachesdescription section=admin}</p>
            {$clearcachesform|safe}
        </div>
    </div>

    <div class="panel panel-default">
        <h3 class="panel-heading">{str tag=configsite section=admin} <span class="icon icon-cogs pull-right" role="presentation" aria-hidden="true"></span></h3>
        <ul class="list-group">
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/options.php">{str tag=siteoptions section=admin}</a>
                <small> {str tag=siteoptionsdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/pages.php">{str tag=staticpages section=admin}</a>
                <small> {str tag=staticpagesdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/licenses.php">{str tag=sitelicenses section=admin}</a>
                <small> {str tag=sitelicensesdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/menu.php">{str tag=menus section=admin}</a>
                <small> {str tag=menusdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/networking.php">{str tag=networking section=admin}</a>
                <small> {str tag=networkingdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/views.php">{str tag=siteviews section=admin}</a>
                <small> {str tag=siteviewsdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}artefact/file/sitefiles.php">{str tag=sitefiles section=admin}</a>
                <small> {str tag=sitefilesdescription section=admin}</small>
            </li>
            {ifconfig key=skins}
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/skins.php">{str tag=siteskins section=admin}</a>
                <small> {str tag=siteskinsdescription section=admin}</small>
            </li>{/ifconfig}
            {ifconfig key=skins}
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/fonts.php">{str tag=sitefonts section=admin}</a>
                <small> {str tag=sitefontsdescription section=admin}</small>
            </li>{/ifconfig}
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/cookieconsent.php">{str tag=cookieconsent section=admin}</a>
                <small>{str tag=cookieconsentdescription section=admin}</small>
            </li>
        </ul>
    </div>
    <div class="panel panel-default">
        <h3 class="panel-heading">{str tag=configusers section=admin} <span class="icon icon-user pull-right" role="presentation" aria-hidden="true"></span></h3>
        <ul class="list-group">
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/search.php">{str tag=usersearch section=admin}</a>
                <small>{str tag=usersearchdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/suspended.php">{str tag=suspendedusers section=admin}</a>
                <small>{str tag=suspendedusersdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/staff.php">{str tag=sitestaff section=admin}</a>
                <small>{str tag=staffusersdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/admins.php">{str tag=siteadmins section=admin}</a>
                <small>{str tag=adminusersdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/notifications.php">{str tag=adminnotifications section=admin}</a>
                <small>{str tag=adminnotificationsdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/add.php">{str tag=adduser section=admin}</a>
                <small>{str tag=adduserdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/uploadcsv.php">{str tag=uploadcsv section=admin}</a>
                <small>{str tag=uploadcsvdescription section=admin}</small>
            </li>
        </ul>
    </div>



    <div class="panel panel-default">
        <h3 class="panel-heading">{str tag=managegroups section=admin} <span class="icon icon-users pull-right" role="presentation" aria-hidden="true"></span></h3>
        <ul class="list-group">
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/groups/groups.php">{str tag=administergroups section=admin}</a>
                <small>{str tag=administergroupsdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/groups/groupcategories.php">{str tag=groupcategories section=admin}</a>
                <small>{str tag=groupcategoriesdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/groups/uploadcsv.php">{str tag=uploadgroupcsv section=admin}</a>
                <small>{str tag=uploadgroupcsvdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/groups/uploadmemberscsv.php">{str tag=uploadgroupmemberscsv section=admin}</a>
                <small>{str tag=uploadgroupmemberscsvdescription section=admin}</small>
            </li>
        </ul>
    </div>


    <div class="panel panel-default">
        <h3 class="panel-heading">{str tag=manageinstitutions section=admin} <span class="icon icon-university pull-right" role="presentation" aria-hidden="true"></span></h3>
        <ul class="list-group">
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/institutions.php">{str tag=Institutions section=admin}</a>
                <small>{str tag=institutionsdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/institutionusers.php">{str tag=institutionmembers section=admin}</a>
                <small>{str tag=institutionmembersdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/institutionstaff.php">{str tag=institutionstaff section=admin}</a>
                <small>{str tag=institutionstaffdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/institutionadmins.php">{str tag=institutionadmins section=admin}</a>
                <small>{str tag=institutionadminsdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}view/institutionviews.php">{str tag=institutionviews section=admin}</a>
                <small>{str tag=institutionviewsdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}artefact/file/institutionfiles.php">{str tag=institutionfiles section=admin}</a>
                <small>{str tag=institutionfilesdescription section=admin}</small>
            </li>
        </ul>
    </div>

    <div class="panel panel-default">
        <h3 class="panel-heading">{str tag=configextensions section=admin} <span class="icon icon-puzzle-piece pull-right" role="presentation" aria-hidden="true"></span></h3>
        <ul class="list-group">
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/extensions/plugins.php">{str tag=pluginadmin section=admin}</a>
                <small>{str tag=pluginadmindescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/extensions/filter.php">{str tag=htmlfilters section=admin}</a>
                <small>{str tag=htmlfiltersdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/extensions/iframesites.php">{str tag=allowediframesites section=admin}</a>
                <small>{str tag=allowediframesitesdescriptionshort section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/extensions/cleanurls.php">{str tag=cleanurls section=admin}</a>
                <small>{str tag=cleanurlsdescriptionshort section=admin}</small>
            </li>
        </ul>
    </div>

</div>
{include file='footer.tpl'}
