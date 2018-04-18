{include file='header.tpl'}
<div class="btn-group btn-group-top">
    <button id="configbtn" type="button" class="btn btn-secondary" data-toggle="modal-docked" data-target="#modal-configs">
        <span class="icon icon-cog icon-lg" role="presentation" aria-hidden="true"></span>
        {str tag="configurereport" section="admin"}
    </button>
</div>
<div class="reportsettings">{$reportsettings|safe}</div>
<div class="clearfix"></div>
{if $institutiondata || $subpagedata}
    <div>
    {if $institutiondata}
        <div class="subpage card-body row">
        {include file='admin/users/stats.tpl' cron=1}
        </div>
    {/if}
    {if $subpagedata && $subpagedata.notvalid_errorstring}
        <div class="alert alert-info postlist">{$subpagedata.notvalid_errorstring|safe}</div>
    {elseif $subpagedata}
            <div class="subpage card-body row statistics">
                {if $subpagedata.table.count == 0}
                {else}
                    <div id="statistics_table_container" class="col-md-12">
                        <div class="table-responsive">
                            <table id="statistics_table" class="table table-striped fullwidth">
                                <thead>
                                    <tr>
                                        {foreach from=$subpagedata.tableheadings item=heading}
                                            {if $heading.selected}
                                                {$heading.html|safe}
                                            {/if}
                                        {/foreach}
                                    </tr>
                                </thead>
                                <tbody>
                                    {$subpagedata.table.tablerows|safe}
                                </tbody>
                            </table>
                            {$subpagedata.table.pagination|safe}
                        </div>
                        {if $subpagedata.table.csv}
                            <a href="{$WWWROOT}download.php" id="csvdownload" class="csv-button float-right" title="{str tag="exportstatsascsv" section="admin"}">
                            <span class="icon icon-download" role="presentation" aria-hidden="true"></span>
                            <span>{str tag="Download" section="admin"}</span></a>
                        {/if}
                    </div>
                {/if}
                {if $subpagedata.summary}
                    <div class="col-md-12 image-right">
                      {$subpagedata.summary|safe}
                    </div>
                {/if}
            </div>
        </div>
    {/if}
    </div>
{else}
    <div>{str tag="nostatistics" section="admin"}</div>
{/if}
{* The configuration modal form *}
<div class="modal modal-docked modal-docked-right modal-shown closed" id="modal-configs" tabindex="-1" role="dialog" aria-labelledby="#modal-configs-title">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button class="deletebutton close" data-dismiss="modal-docked" aria-label="{str tag=Close}">
                    <span class="times">×</span>
                    <span class="sr-only">{str tag=Close}</span>
                </button>
                <h4 class="modal-title blockinstance-header text-inline modal-configs-title">{str tag="reportconfig" section="statistics"}</h4>
            </div>
            <div class="modal-body">
                <span class="icon icon-spinner icon-pulse" role="presentation" aria-hidden="true"></span>
                <span>{str tag="loading" section="mahara"}</span>
            </div>
        </div>
    </div>
</div>

{include file='footer.tpl'}
