{include file='header.tpl'}

{if $sitedata}
<div id="site-stats-wrap">
<div class="site-stats-left">
  {include file='admin/stats.tpl' cron=1}
</div>
<div class="site-stats-right">
<div class="tabswrap"><h3 class="rd-tab-title"><a href="#">{str tag=tabs}<span class="rd-tab"></span></a></h3>
<ul class="in-page-tabs">
{foreach from=$subpages item=subpage}
  <li{if $subpage == $type} class="current-tab"{/if}><a {if $subpage == $type}class="current-tab" {/if}href="{$WWWROOT}admin/statistics.php?type={$subpage}">{str tag=$subpage}<span class="accessible-hidden">({str tag=tab}{if $subpage == $type} {str tag=selected}{/if})</span></a></li>
{/foreach}
</ul></div>

<div class="subpage rel"><div id="site-stats-wrap2">
  {if $subpagedata.summary}
    <div class="statistics-subpage-left-column">
      {$subpagedata.summary|safe}
    </div>
  {/if}
  {if $subpagedata.table.csv}
  <div class="fr">
    <span class="bulkaction-title">{str tag=exportstatsascsv section=admin}:</span>
    <a href="{$WWWROOT}download.php" target="_blank">{str tag=Download section=admin} <span class="accessible-hidden">{str tag=downloaddataascsv section=admin args=$type}</span></a>
  </div>
  {/if}
  <div id="statistics_table_container" class="statistics-subpage-{if $subpagedata.summary}right{else}full{/if}-column {if $subpagedata.table.count == 0} hidden{/if}">
    <h3>{$subpagedata.tabletitle}</h3>
    <table id="statistics_table" class="tablerenderer fullwidth">
      <thead>
        <tr>
{foreach from=$subpagedata.tableheadings item=heading}
          <th{if $heading.class} class="{$heading.class}"{/if}>{if $heading.link}<a href="{$heading.link}">{/if}{$heading.name}{if $heading.link}</a>{/if}</th>
{/foreach}
        </tr>
      </thead>
      <tbody>
{$subpagedata.table.tablerows|safe}
      </tbody>
    </table>
{$subpagedata.table.pagination|safe}
  </div>
  <div class="cb"></div>
  </div>
</div>
</div>
  <div class="cb"></div>
</div>
{/if}

<div id="site-stats-clearer"></div>
{include file='footer.tpl'}
