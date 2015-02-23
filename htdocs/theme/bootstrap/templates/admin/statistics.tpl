{include file='header.tpl'}

{if $sitedata}
<div id="site-stats-wrap" class="panel-items js-masonry" data-masonry-options='{ "itemSelector": ".panel" }'>
	<div class="panel panel-info">
		<h3 class="panel-heading">{$sitedata.name}: {str tag=siteinformation section=admin} <span class="fa fa-info pls pull-right"></span></h3>
		{include file='admin/stats.tpl' cron=1}
	</div>
	<div class="panel panel-default double">
		<div class="panel-heading">
			<ul class="nav nav-pills">
				{foreach from=$subpages item=subpage}
					<li{if $subpage == $type} class="active"{/if}>
						<a {if $subpage == $type}class="current-tab" {/if}href="{$WWWROOT}admin/statistics.php?type={$subpage}">{str tag=$subpage}<span class="accessible-hidden sr-only">({str tag=tab}{if $subpage == $type} {str tag=selected}{/if})</span></a>
					</li>
				{/foreach}
			</ul>
		</div>

		<div class="subpage panel-body row" id="site-stats-wrap2">
			{if $subpagedata.table.count == 0}{else}
				<div id="statistics_table_container" class="{if $subpagedata.summary}col-md-7{else}col-md-12{/if}">
					<h3>{$subpagedata.tabletitle}</h3>
					<table id="statistics_table" class="table table-striped fullwidth">
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
			{/if}
			{if $subpagedata.summary}
				<div class="{if $subpagedata.table.count == 0}col-md-12{else}col-md-5{/if}">
					{$subpagedata.summary|safe}
				</div>
			{/if}
		</div>
		
		{if $subpagedata.table.csv}
			<a href="{$WWWROOT}download.php" class="panel-footer" target="_blank"><span class="fa fa-table"></span> {str tag=exportstatsascsv section=admin}</a>
		{/if}
	</div>

</div>
{/if}


{include file='footer.tpl'}
