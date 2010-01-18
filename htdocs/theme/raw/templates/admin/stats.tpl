{if $sitedata.weekly}
  <div class="fr">
    <div id="site-stats-graph" class="fr" width="300" height="200"></div>
    <script type="text/javascript">
addLoadEvent(function () {literal}{{/literal}
    EasyPlot('line', {literal}{}{/literal}, $('site-stats-graph'), {$sitedata.dataarray});
{literal}}{/literal});
    </script>
  </div>
{/if}
  <div class="fl">
    <p><strong>{str tag=siteinstalled section=admin}:</strong> {$sitedata.installdate}</p>
    {if $sitedata.users}
    <p><strong>{str tag=users}:</strong> {$sitedata.users}{if $sitedata.rank.users} ({str tag=Rank section=admin}: $sitedata.rank.users}){/if}</p>
    <p>&nbsp;{str tag=activeusers section=admin}: {$sitedata.usersloggedin}</p>
    {/if}
    {if $sitedata.groups}
    <p><strong>{str tag=groups}:</strong> {$sitedata.groups}{if $sitedata.rank.groups} ({str tag=Rank section=admin}: $sitedata.rank.groups}){/if}</p>
    <p>&nbsp;{$sitedata.groupmemberaverage}</p>
    {/if}
    {if $sitedata.views}
    <p><strong>{str tag=views}:</strong> {$sitedata.views}{if $sitedata.rank.views} ({str tag=Rank section=admin}: $sitedata.rank.views}){/if}</p>
    <p>&nbsp;{$sitedata.viewsperuser}</p>
    {/if}
    <p><strong>{str tag=databasesize section=admin}:</strong> {$sitedata.dbsize|display_size}</p>
    <p><strong>{str tag=diskusage section=admin}:</strong> {$sitedata.diskusage|display_size}</p>
    <p><strong>{str tag=maharaversion section=admin}:</strong> {$sitedata.release}</p>
    <p><strong>{str tag=Cron section=admin}:</strong> {if $sitedata.cronrunning}{str tag=runningnormally section=admin}{else}{str tag=cronnotrunning section=admin}{/if}</p>
  </div>
<div class="cb"></div>

