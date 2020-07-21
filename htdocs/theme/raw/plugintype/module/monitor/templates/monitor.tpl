{include file="header.tpl"}

    <div class="card double">
        <h2 class="card-header">{$subpagedata.tabletitle}{if $subpagedata.help}{$subpagedata.help|safe}{/if}</h2>
        
        <div class="subpage card-body row" id="site-stats-wrap2">
            <div id="monitor_table_container" class="col-md-12">

                {if $subpagedata.tablesubtitle}<div class="small">{$subpagedata.tablesubtitle}</div>{/if}
            {if $subpagedata.table.count == 0}
                <div class="card-body">
                    <div class="no-results">{str tag="noresultsfound"}</div>
                </div>
            {else}
                <div class="table-responsive">
                    <table id="monitor_table" class="table table-striped fullwidth">
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
                </div>
                {$subpagedata.table.pagination|safe}
            {/if}
            </div>
        </div>

        {if $subpagedata.table.csv}
            <a href="{$WWWROOT}download.php" class="card-footer"><span class="icon icon-table" role="presentation" aria-hidden="true"></span> {str tag=exportresultscsv section=module.monitor}</a>
        {/if}
    </div>


{include file="footer.tpl"}
