{include file='header.tpl'}

{if $sitedata}
<div class="site-stats">
  {include file='admin/stats.tpl' cron=1}
</div>

<ul class="in-page-tabs">
{foreach from=$subpages item=subpage}
  <li><a {if $subpage == $type}class="current-tab" {/if}href="{$WWWROOT}admin/statistics.php?type={$subpage}">{str tag=$subpage}</a></li>
{/foreach}
</ul>

<div class="subpage rel">
  <div id="statistics_table_container">
    <table id="statistics_table" class="fullwidth listing">
      <thead>
        <tr>
{foreach from=$subpagedata.tableheadings item=heading}
          <th>{$heading}</th>
{/foreach}
        <tr>
      </thead>
      <tbody>
{$subpagedata.table.tablerows}
      </tbody>
    </table>
  </div>
{$subpagedata.table.pagination}
</div>
{/if}

{include file='footer.tpl'}
