{auto_escape off}
{include file="header.tpl"}
<div id="planswrap">
    <h3>{str tag=deleting section='artefact.plans'}: {$todelete->title}</h3>
    <table id="deleteplan" class="tablerenderer">
        <colgroup width="25%" span="2"></colgroup>
        <thead>
            <tr>
                <th class="plansdate">{str tag='completiondate' section='artefact.plans'}</th>
                <th>{str tag='title' section='artefact.plans'}</th>
                <th>{str tag='completed' section='artefact.plans'}</th>
            </tr>
        </thead>
        <tbody>
            <tr class="{cycle values='r0,r1'}">
                <td>{$todelete->completiondate|escape}</td>
                <td>{$todelete->title|escape}<div>{$artefact->description|escape}</div></td>
                {if $todelete->completed == 1}<td><div class="completed"><img src="/artefact/plans/theme/raw/static/images/success.gif" alt="" /></div></td>{else}<td>&nbsp;</td>{/if}
            </tr>
        </tbody>
    </table>
    {$deleteplanform}
</div>
{include file="footer.tpl"}
{/auto_escape}
