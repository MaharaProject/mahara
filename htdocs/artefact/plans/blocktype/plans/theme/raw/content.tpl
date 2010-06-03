{auto_escape off}
<div id="planswrap">
{if $plans}
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
        {foreach from=$plans item=plan}
        {if $plan->completed == -1}
            <tr class="incomplete">
                <td>{$plan->completiondate|escape}</td>
                <td>{$plan->title|escape}<div>{$->description|escape}</div></td>
                <td>&nbsp;</td>
            </tr>
        {else}
            <tr class="{cycle values='r0,r1'}">
                <td>{$plan->completiondate|escape}</td>
                <td>{$plan->title|escape}<div>{$plan->description|escape}</div></td>
                {if $plan->completed == 1}
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
{if $newerplanslink || $olderplanslink}
<div class="myplans-pagination">
{if $olderplanslink}<div class="fr"><a href="{$olderplanslink|escape}">{str tag=olderplans section=blocktype.plans/plans}</a></div>{/if}
{if $newerplanslink}<div><a href="{$newerplanslink|escape}">{str tag=newerplans section=blocktype.plans/plans}</a></div>{/if}
</div>
{/if}
</div>
{/auto_escape}
