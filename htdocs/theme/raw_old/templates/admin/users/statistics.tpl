{include file='header.tpl'}

{if $institutiondata}
    <div id="panel panel-info" class="panel-items js-masonry">
        <div class="panel panel-info">
            <h3 class="panel-heading">{str tag=information section=admin}<span class="icon icon-info pull-right" role="presentation" aria-hidden="true"></span></h3>
          {include file='admin/users/stats.tpl' cron=1}
        </div>

        <div class="panel panel-default double">
            <div class="panel-heading">
                <ul class="nav nav-pills">
                    {foreach from=$subpages item=subpage}
                        <li{if $subpage == $type} class="active"{/if}>
                            <a {if $subpage == $type}class="current-tab" {/if}href="{$WWWROOT}admin/users/statistics.php?institution={$institutiondata.name}&type={$subpage}">{str tag=$subpage}
                            <span class="accessible-hidden sr-only">({str tag=tab}{if $subpage == $type} {str tag=selected}{/if})</span></a>
                        </li>
                    {/foreach}
                </ul>
            </div>

            <div class="subpage panel-body row">
                {if $subpagedata.table.count == 0}{else}
                    <div id="statistics_table_container" class="col-md-12">
                        <h3>{$subpagedata.tabletitle}</h3>
                        <table id="statistics_table" class="table table-striped fullwidth">
                            <thead>
                                <tr>
                                    {foreach from=$subpagedata.tableheadings item=heading}
                                        <th{if $heading.class} class="{$heading.class}"{/if}>{$heading.name}</th>
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
                    <div class="col-md-12 image-right">
                      {$subpagedata.summary|safe}
                    </div>
                {/if}
            </div>

            {if $subpagedata.table.csv}
                <a href="{$WWWROOT}download.php" class="panel-footer">
                <span class="icon icon-table" role="presentation" aria-hidden="true"></span> {str tag=exportstatsascsv section=admin}</span></a>
            {/if}
        </div>

    </div>
{/if}

{include file='footer.tpl'}
