{include file='header.tpl'}

{if $sitedata}
<div class="site-stats-left fl">
  {include file='admin/stats.tpl' cron=1}
</div>
<div class="site-stats-right fr">
<ul class="in-page-tabs">
{foreach from=$subpages item=subpage}
  <li><a {if $subpage == $type}class="current-tab" {/if}href="{$WWWROOT}admin/statistics.php?type={$subpage}">{str tag=$subpage}</a></li>
{/foreach}
</ul>

<div class="subpage rel">
  <div class="statistics-subpage-left-column fl">
  {$subpagedata.summary|safe}
  </div>
  <div id="statistics_table_container" class="statistics-subpage-right-column fr{if $subpagedata.table.count == 0} hidden{/if}">
    <h3>{$subpagedata.tabletitle}</h3>
    <table id="statistics_table" class="fullwidth">
      <thead>
        <tr>
{foreach from=$subpagedata.tableheadings item=heading}
          <th{if $heading.class} class="{$heading.class}"{/if}>{$heading.name}</th>
{/foreach}
        <tr>
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
  <div class="cb"></div>
{/if}

{include file='footer.tpl'}
