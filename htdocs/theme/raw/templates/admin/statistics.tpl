{include file='header.tpl'}

{if $sitedata}
<div class="site-stats">
  {include file='admin/stats.tpl' cron=1}
</div>
{/if}

{include file='footer.tpl'}
