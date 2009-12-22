<div id="site-stats-graph" class="fr" width="300" height="200">
</div>
{if $sitedata.weekly}
<script type="text/javascript">
addLoadEvent(function () {literal}{{/literal}
    EasyPlot('line', {literal}{}{/literal}, $('site-stats-graph'), {$sitedata.dataarray});
{literal}}{/literal});
</script>
{/if}
<div class="site-stats fl">
<h5>{$sitedata.name}: {str tag=sitestatistics section=admin}</h5>
<p><strong>{str tag=users}:</strong> {$sitedata.users}{if $sitedata.rank.users} ({str tag=Rank section=admin}: $sitedata.rank.users}){/if}</p>
<p><strong>{str tag=groups}:</strong> {$sitedata.groups}{if $sitedata.rank.groups} ({str tag=Rank section=admin}: $sitedata.rank.groups}){/if}</p>
<p><strong>{str tag=views}:</strong> {$sitedata.views}{if $sitedata.rank.views} ({str tag=Rank section=admin}: $sitedata.rank.views}){/if}</p>
</div>
<div class="cb"></div>