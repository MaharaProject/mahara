<div class="message site-stats">
{if $sitedata.weekly}
  <div id="site-stats-graph" class="fr" width="300" height="200"></div>
  <script type="text/javascript">
addLoadEvent(function () {literal}{{/literal}
    EasyPlot('line', {literal}{}{/literal}, $('site-stats-graph'), {$sitedata.dataarray});
{literal}}{/literal});
  </script>
{/if}
  <div class="fl">
    <h3>{$sitedata.name}: {str tag=sitestatistics section=admin}</h3>
    <p><strong>{str tag=siteinstalled section=admin}:</strong> {$sitedata.installdate}</p>
    <p><strong>{str tag=users}:</strong> {$sitedata.users}{if $sitedata.rank.users} ({str tag=Rank section=admin}: $sitedata.rank.users}){/if}</p>
    <p><strong>{str tag=groups}:</strong> {$sitedata.groups}{if $sitedata.rank.groups} ({str tag=Rank section=admin}: $sitedata.rank.groups}){/if}</p>
    <p><strong>{str tag=views}:</strong> {$sitedata.views}{if $sitedata.rank.views} ({str tag=Rank section=admin}: $sitedata.rank.views}){/if}</p>
    <p><strong>{str tag=databasesize section=admin}:</strong> {$sitedata.dbsize|display_size}</p>
    <p><strong>{str tag=diskusage section=admin}:</strong> {$sitedata.diskusage|display_size}</p>
    <p><strong>{str tag=maharaversion section=admin}:</strong> {$sitedata.release}</p>
  </div>
<div class="cb"></div>
</div>
