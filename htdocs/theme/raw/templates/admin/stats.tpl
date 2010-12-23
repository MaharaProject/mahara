{if $sitedata.weekly}
    <div id="site-stats-graph">
      <img src="{$sitedata.weekly}" alt="" />
    </div>
{/if}
  <div class="site-stats-table">
    <div><strong>{str tag=siteinstalled section=admin}:</strong> {$sitedata.installdate}</div>
    {if $sitedata.users}
    <div><strong>{str tag=users}:</strong> {$sitedata.users}<br />
    {str tag=activeusers section=admin}: {$sitedata.usersloggedin}</div>
    {/if}
    {if $sitedata.groups}
    <div><strong>{str tag=groups}:</strong> {$sitedata.groups}</br />
    {$sitedata.strgroupmemberaverage}</div>
    {/if}
    {if $sitedata.views}
    <div><strong>{str tag=Views section=view}:</strong> {$sitedata.views}<br />
    {$sitedata.strviewsperuser}</div>
    {/if}
    <div><strong>{str tag=databasesize section=admin}:</strong> {$sitedata.dbsize|display_size}</div>
    {if $sitedata.diskusage}
    <div><strong>{str tag=diskusage section=admin}:</strong> {$sitedata.diskusage|display_size}</div>
    {/if}
    <div><strong>{str tag=maharaversion section=admin}:</strong> {$sitedata.release}{if $sitedata.strlatestversion} ({$sitedata.strlatestversion|clean_html|safe}){/if}</div>
    <div><strong>{str tag=Cron section=admin}:</strong> {if $sitedata.cronrunning}{str tag=runningnormally section=admin}{else}{str tag=cronnotrunning section=admin}{/if}</div>
  </div>
