{auto_escape off}
{include file="header.tpl"}
<div id="planswrap">
    <table width="100%" id="deleteplan" class="tablerenderer">
        <thead>
            <tr>
                <th class="plansdate">{str tag='completiondate' section='artefact.plans'}</th>
                <th>{str tag='title' section='artefact.plans'}</th>
                <th>{str tag='description' section='artefact.plans'}</th>
                <th>{str tag='completed' section='artefact.plans'}</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{$todelete->completiondate|escape}</td>
                <td>{$todelete->title|escape}</td>
                <td>{$todelete->description|escape}</td>
                {if $todelete->completed == 1}<td><div class="completed"><img src="{$WWWROOT}theme/raw/static/images/success.gif" alt="" /></div></td>{else}<td>&nbsp;</td>{/if}
            </tr>
        </tbody>
    </table>
    <div class="deleteplan">{$deleteplanform}</div>
</div>
{include file="footer.tpl"}
{/auto_escape}
