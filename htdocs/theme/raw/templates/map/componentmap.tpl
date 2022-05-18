{include file="header.tpl"}
<span id="top"></span>

<div id="pluginmap_container">
    <fieldset class="pieform-fieldset collapsible">
        <legend>
            <h3 class="form-group collapsible-group">
                <a href="#third-party" aria-expanded="true" data-bs-toggle="collapse">{str tag=thirdpartyplugins section=admin}<span class="icon icon-chevron-down float-end collapse-indicator" role="presentation"
                        aria-hidden="true"></span></a></h3>
        </legend>

        <div id="third-party" class="show">

            <table class="table table-striped">
                <thead>
                    <td>{str tag=Plugin section=admin}</td>
                    <td>{str tag=Path section=admin}</td>
                    <td>{str tag=version section=admin}</td>
                    <td>{str tag=url section=admin}</td>
                </thead>
                {foreach from=$plugins item=item}
                <tr>
                    <td>{$item->name}</td>
                    <td>{$item->path}</td>
                    <td>
                        {foreach from=$item->versions item=sub}
                        {$sub}<br>{/foreach}
                    </td>
                    <td>
                        {foreach from=$item->websites item=sub}
                        <a href={$sub}>{$sub}</a><br>{/foreach}
                    </td>
                </tr>
                {/foreach}
            </table>
            <a href="{$WWWROOT}download.php" class="card-footer"><span class="icon icon-table" role="presentation" aria-hidden="true"></span> {str tag=exportthirdpartycsv section=admin}</a>
        </div>
    </fieldset>
</div>


{include file="footer.tpl"}