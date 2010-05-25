{auto_escape off}
<div id="planswrap">
{if $rows}
<table id="planslist" class="tablerenderer">
    <colgroup width="50%" span="2"></colgroup>
    <thead>
        <tr>
            <th class="plansdate">{str tag='completiondate' section='artefact.plans'}</th>
            <th>{str tag='title' section='artefact.plans'}</th>
            <th>{str tag='completed' section='artefact.plans'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        {if $row->completed == -1}
            <tr class="incomplete">
                <td>{$row->completiondate|escape}</td>
                <td>{$row->title|escape}<div>{$row->description|escape}</div></td>
                <td>&nbsp;</td>
            </tr>
        {else}
            <tr class="{cycle values='r0,r1'}">
                <td>{$row->completiondate|escape}</td>
                <td>{$row->title|escape}<div>{$row->description|escape}</div></td>
                {if $row->completed == 1}
                    <td><div class="completed"><img src="{$WWWROOT}theme/raw/static/images/success.gif" alt="" /></div></td>
                {else}
                    <td>&nbsp;</td>
                {/if}
            </tr>
        {/if}
        {/foreach}
    </tbody>
</table>
{/if}
</div>
{/auto_escape}
