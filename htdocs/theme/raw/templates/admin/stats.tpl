{auto_escape off}
{if $sitedata.weekly}
  <div class="fr">
    <div id="site-stats-graph" class="fr">
      <img src="{$sitedata.weekly}" alt="" />
    </div>
  </div>
{/if}
  <div class="fl">
    <p><strong>{str tag=siteinstalled section=admin}:</strong> {$sitedata.installdate}</p>
    {if $sitedata.users}
    <p><strong>{str tag=users}:</strong> {$sitedata.users}{if $sitedata.rank.users} ({str tag=Rank section=admin}: {$sitedata.rank.users}*){/if}</p>
    <p>&nbsp;{str tag=activeusers section=admin}: {$sitedata.usersloggedin}</p>
    {/if}
    {if $sitedata.groups}
    <p><strong>{str tag=groups}:</strong> {$sitedata.groups}{if $sitedata.rank.groups} ({str tag=Rank section=admin}: {$sitedata.rank.groups}*){/if}</p>
    <p>&nbsp;{$sitedata.strgroupmemberaverage}</p>
    {/if}
    {if $sitedata.views}
    <p><strong>{str tag=views}:</strong> {$sitedata.views}{if $sitedata.rank.views} ({str tag=Rank section=admin}: {$sitedata.rank.views}*){/if}</p>
    <p>&nbsp;{$sitedata.strviewsperuser}</p>
    {/if}
    {if $sitedata.rank.users}
    <p>{$sitedata.strrankingsupdated}</p>
    {/if}
    <p><strong>{str tag=databasesize section=admin}:</strong> {$sitedata.dbsize|display_size}</p>
    {if $sitedata.diskusage}
    <p><strong>{str tag=diskusage section=admin}:</strong> {$sitedata.diskusage|display_size}</p>
    {/if}
    <p><strong>{str tag=maharaversion section=admin}:</strong> {$sitedata.release}{if $sitedata.strlatestversion} ({$sitedata.strlatestversion}){/if}</p>
    <p><strong>{str tag=Cron section=admin}:</strong> {if $sitedata.cronrunning}{str tag=runningnormally section=admin}{else}{str tag=cronnotrunning section=admin}{/if}</p>
    {if $sitedata.rank.users}<p class="s dull fr">* {str tag=registrationrankdescription section=admin}</p>{/if}
  </div>
<div class="cb"></div>

{/auto_escape}
