{auto_escape off}
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
  <div class="statistics-subpage-left-column fl">
  {$subpagedata.summary}
  </div>
  <div id="statistics_table_container" class="statistics-subpage-right-column fr{if $subpagedata.table.count == 0} hidden{/if}">
    <h3>{$subpagedata.tabletitle}</h3>
    <table id="statistics_table" class="fullwidth">
      <thead>
        <tr>
{foreach from=$subpagedata.tableheadings item=heading}
          <th{if $heading.class} class="{$heading.class}"{/if}>{$heading.name|escape}</th>
{/foreach}
        <tr>
      </thead>
      <tbody>
{$subpagedata.table.tablerows}
      </tbody>
    </table>
{$subpagedata.table.pagination}
  </div>
  <div class="cb"></div>
</div>
{/if}

{include file='footer.tpl'}
{/auto_escape}
