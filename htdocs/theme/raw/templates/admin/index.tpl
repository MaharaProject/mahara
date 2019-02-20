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
<div class="card bg-warning" id="">
    <h3 class="card-header">{str tag="upgrades" section=admin}</h3>
    <div class="card-body">
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
        <a class="btn btn-secondary" href="upgrade.php">{str tag=runupgrade section=admin}</a>
    </div>
</div>
{/if}

{if $upgrades['settings']['newinstallcount']}
<div class="card bg-warning" id="runinstall">
    <h3 class="card-header">{str tag="newplugins" section=admin}</h3>
    <div class="card-body">
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
        <a class="btn btn-secondary" href="extensions/plugins.php">
            {str tag=gotoinstallpage section=admin}
            <span class="icon icon-arrow-right right" role="presentation" aria-hidden="true"></span>
        </a>
    </div>
</div>
{/if}
<div class="card-items js-masonry" data-masonry-options='{ "itemSelector": ".card" }'>
    {if $register}

        <div class="card bg-success register-site">
            <h3 class="card-header">{str tag=registermaharasite section=admin} <span class="icon icon-star float-right" role="presentation" aria-hidden="true"></span></h3>
            <div class="card-body">
                {if $newregisterpolicy}
                    <strong>{str tag=newregistrationpolicyinfo section=admin}</strong>
                {/if}
            {str tag=registeryoursitesummary section=admin args=$WWWROOT}
            {if $firstregistered}
                <p>{str tag=siteisregisteredsince section=admin args=$firstregistered}</p>
            {/if}
            {if $sendweeklyupdates}
                <p>{str tag=sendingweeklyupdates1 section=admin}</p>
            {else}
                <p>{str tag=notsendingweeklyupdates section=admin}</p>
            {/if}
            </div>
            <a class="card-footer" href="{$WWWROOT}admin/registersite.php">{str tag=Registration section=admin} <span class="icon icon-arrow-circle-right float-right" role="presentation" aria-hidden="true"></span></a>
        </div>

    {/if}

    {if $sitedata}

        <div class="card bg-info site-stats">
            <h3 class="card-header">{$sitedata.displayname}: {str tag=siteinformation section=admin} <span class="icon icon-area-chart float-right" role="presentation" aria-hidden="true"></span></h3>
            {include file='admin/users/stats.tpl' institutiondata=$sitedata showall='_all' fromindex='1'}
            <a class="card-footer text-small" href="{$WWWROOT}admin/users/statistics.php?type=information&subtype=information">{str tag=viewfullsitestatistics section=admin} <span class="icon icon-arrow-circle-right float-right" role="presentation" aria-hidden="true"></span></a>
        </div>

    {/if}

    <div class="card close-site {if $closed}bg-success {else}bg-danger {/if}">
        {if $closed}
            <h3 class="card-header">{str tag=reopensite section=admin} <span class="icon icon-lock float-right" role="presentation" aria-hidden="true"></span></h3>
            <div class="card-body">
                <p>{str tag=reopensitedetail section=admin}</p>
                {$closeform|safe}
            </div>
        {else}
            <h3 class="card-header">{str tag=closesite section=admin} <span class="icon icon-unlock-alt float-right" role="presentation" aria-hidden="true"></span></h3>
            <div class="card-body">
                <p>{str tag=closesitedetail section=admin}</p>
                {$closeform|safe}
            </div>
        {/if}
    </div>

    <div class="card">
        <h3 class="card-header">{str tag=clearcachesheading section=admin} <span class="icon icon-refresh float-right" role="presentation" aria-hidden="true"></span></h3>
        <div class="card-body">
            <p>{str tag=cliclearcachesdescription section=admin}</p>
            {$clearcachesform|safe}
        </div>
    </div>

    <div class="card">
        <h3 class="card-header">{str tag=configsite section=admin} <span class="icon icon-cogs float-right" role="presentation" aria-hidden="true"></span></h3>
        <ul class="list-group">
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/options.php">{str tag=siteoptions section=admin}</a>
                <small> {str tag=siteoptionsdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/pages.php">{str tag=staticpages section=admin}</a>
                <small> {str tag=staticpagesdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/privacy.php">{str tag=legal section=admin}</a>
                <small> {str tag=privacytermsdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/menu.php">{str tag=menus section=admin}</a>
                <small> {str tag=menusdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/networking.php">{str tag=networking section=admin}</a>
                <small> {str tag=networkingdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/licenses.php">{str tag=sitelicenses section=admin}</a>
                <small> {str tag=sitelicensesdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/views.php">{str tag=Viewscollections section=view}</a>
                <small> {str tag=siteviewsdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}artefact/blog/index.php?institution=mahara">{str tag=Blogs section=artefact.blog}</a>
                <small> {str tag=siteblogsdesc section=artefact.blog}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/shareviews.php">{str tag=share section=mahara}</a>
                <small> {str tag=sharesitefilesdesc section=admin}</small>
            </li>
            {ifconfig key=skins}
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/skins.php">{str tag=siteskinmenu section=skin}</a>
                <small> {str tag=siteskinsdesc section=admin}</small>
            </li>{/ifconfig}
            {ifconfig key=skins}
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/fonts.php">{str tag=sitefonts section=admin}</a>
                <small> {str tag=sitefontsdesc section=admin}</small>
            </li>{/ifconfig}
            <li class="list-group-item">
                <a href="{$WWWROOT}artefact/file/sitefiles.php">{str tag=Files section=group}</a>
                <small> {str tag=sitefilesdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/site/cookieconsent.php">{str tag=cookieconsent section=admin}</a>
                <small>{str tag=cookieconsentdesc section=admin}</small>
            </li>
        </ul>
    </div>
    <div class="card">
        <h3 class="card-header">{str tag=configusers section=admin} <span class="icon icon-user float-right" role="presentation" aria-hidden="true"></span></h3>
        <ul class="list-group">
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/search.php">{str tag=usersearch section=admin}</a>
                <small>{str tag=usersearchdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/suspended.php">{str tag=suspendeduserstitle section=admin}</a>
                <small>{str tag=suspendedusersdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/staff.php">{str tag=sitestaff section=admin}</a>
                <small>{str tag=staffusersdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/admins.php">{str tag=siteadmins section=admin}</a>
                <small>{str tag=adminusersdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/exportqueue.php">{str tag=exportqueue section=admin}</a>
                <small>{str tag=exportqueuedesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/add.php">{str tag=adduser section=admin}</a>
                <small>{str tag=adduserdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/uploadcsv.php">{str tag=uploadcsv section=admin}</a>
                <small>{str tag=uploadcsvdesc section=admin}</small>
            </li>
        </ul>
    </div>



    <div class="card">
        <h3 class="card-header">{str tag=managegroups section=admin} <span class="icon icon-users float-right" role="presentation" aria-hidden="true"></span></h3>
        <ul class="list-group">
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/groups/groups.php">{str tag=administergroups section=admin}</a>
                <small>{str tag=administergroupsdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/groups/groupcategories.php">{str tag=groupcategories section=admin}</a>
                <small>{str tag=groupcategoriesdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/groups/archives.php">{str tag=archivedsubmissions section=admin}</a>
                <small>{str tag=archivedsubmissionsdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/groups/uploadcsv.php">{str tag=uploadgroupcsv section=admin}</a>
                <small>{str tag=uploadgroupcsvdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/groups/uploadmemberscsv.php">{str tag=uploadgroupmemberscsv section=admin}</a>
                <small>{str tag=uploadgroupmemberscsvdescription section=admin}</small>
            </li>
        </ul>
    </div>


    <div class="card">
        <h3 class="card-header">{str tag=manageinstitutions section=admin} <span class="icon icon-university float-right" role="presentation" aria-hidden="true"></span></h3>
        <ul class="list-group">
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/institutions.php">{str tag=settings section=mahara}</a>
                <small>{str tag=institutionsettingsdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/institutionpages.php">{str tag=staticpages section=admin}</a>
                <small> {str tag=staticpagesinstdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/institutionprivacy.php">{str tag=legal section=admin}</a>
                <small> {str tag=institutionprivacytermsdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/institutionusers.php">{str tag=members section=mahara}</a>
                <small>{str tag=institutionmembersdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/institutionstaff.php">{str tag=staff section=statistics}</a>
                <small>{str tag=institutionstaffdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/institutionadmins.php">{str tag=Admins section=admin}</a>
                <small>{str tag=institutionadminsdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/notifications.php">{str tag=adminnotifications section=admin}</a>
                <small>{str tag=adminnotificationsdescription section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/progressbar.php">{str tag=profilecompleteness section=mahara}</a>
                <small>{str tag=profilecompletiondesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}view/institutionviews.php">{str tag=Viewscollections section=view}</a>
                <small>{str tag=institutionviewsdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}artefact/blog/index.php?institution=1">{str tag=Blogs section=artefact.blog}</a>
                <small>{str tag=institutionblogsdesc section=artefact.blog}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}view/institutionshare.php">{str tag=share section=mahara}</a>
                <small>{str tag=shareinstitutionfilesdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}artefact/file/institutionfiles.php">{str tag=Files section=group}</a>
                <small>{str tag=institutionfilesdescription section=admin}</small>
            </li>
            {if $institutiontags}
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/institutiontags.php">{str tag=tags section=mahara}</a>
                <small>{str tag=institutiontagsdesc section=admin}</small>
            </li>
            {/if}
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/pendingregistrations.php">{str tag=pendingregistrations section=admin}</a>
                <small>{str tag=pendingregistrationdesc section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/pendingdeletions.php">{str tag=pendingdeletions section=admin}</a>
                <small>{str tag=pendingdeletiondesc section=admin}</small>
            </li>
        </ul>
    </div>

    <div class="card">
        <h3 class="card-header">{str tag=configextensions section=admin} <span class="icon icon-puzzle-piece float-right" role="presentation" aria-hidden="true"></span></h3>
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
                <small>{str tag=iframesitesdescriptionshort section=admin}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/extensions/cleanurls.php">{str tag=cleanurls section=admin}</a>
                <small>{str tag=cleanurlsdescriptionshort section=admin}</small>
            </li>
            {if $framework}
            <li class="list-group-item">
                <a href="{$WWWROOT}module/framework/frameworks.php">{str tag=smartevidence section=collection}</a>
                <small>{str tag=smartevidencedesc section=collection}</small>
            </li>
            {/if}
        </ul>
    </div>

    <div class="card">
        <h3 class="card-header">{str tag=webservice section=auth.webservice} <span class="icon icon-puzzle-piece float-right" role="presentation" aria-hidden="true"></span></h3>
        <ul class="list-group">
            <li class="list-group-item">
                <a href="{$WWWROOT}webservice/admin/index.php">{str tag=config section=mahara}</a>
                <small>{str tag=webservicesconfigdescshort section=auth.webservice}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}webservice/apptokens.php">{str tag=apptokens section=auth.webservice}</a>
                <small>{str tag=apptokensdesc section=auth.webservice}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}webservice/admin/connections.php">{str tag=connections section=auth.webservice}</a>
                <small>{str tag=connectionsdesc section=auth.webservice}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}webservice/admin/oauthv1sregister.php">{str tag=externalapps section=auth.webservice}</a>
                <small>{str tag=externalappsdesc section=auth.webservice}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}webservice/admin/webservicelogs.php">{str tag=webservicelogsnav section=auth.webservice}</a>
                <small>{str tag=webservicelogsdesc section=auth.webservice}</small>
            </li>
            <li class="list-group-item">
                <a href="{$WWWROOT}webservice/testclient.php">{str tag=testclientnav section=auth.webservice}</a>
                <small>{str tag=testclientdescshort section=auth.webservice}</small>
            </li>
        </ul>
    </div>

    <div class="card">
        <h3 class="card-header">{str tag=reports section=statistics} <span class="icon icon-area-chart float-right" role="presentation" aria-hidden="true"></span></h3>
        <ul class="list-group">
            <li class="list-group-item">
                <a href="{$WWWROOT}admin/users/statistics.php">{str tag=reports section=statistics}</a>
                <small>{str tag=reportsdesc section=statistics}</small>
            </li>
        </ul>
    </div>

</div>
{include file='footer.tpl'}
