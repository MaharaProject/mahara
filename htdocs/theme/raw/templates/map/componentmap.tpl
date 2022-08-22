{include file="header.tpl"}
<span id="top"></span>

<div id="pluginmap_container" class="pieform">
  <div class="form-group collapsible-group">
    <fieldset class="pieform-fieldset collapsible last">
        <legend>
            <a href="#third-party" aria-expanded="true" data-bs-toggle="collapse">{str tag=thirdpartyplugins section=admin}<span class="icon icon-chevron-down float-end collapse-indicator" role="presentation"
                    aria-hidden="true"></span></a>
        </legend>

        <div id="third-party" class="fieldset-body show">

            <table class="table table-striped">
                <thead>
                    <th>{str tag=Plugin section=admin}</th>
                    <th>{str tag=Path section=admin}</th>
                    <th>{str tag=version section=admin}</th>
                    <th>{str tag=url section=admin}</th>
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
</div>


{include file="footer.tpl"}
