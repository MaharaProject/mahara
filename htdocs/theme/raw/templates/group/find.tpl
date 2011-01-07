{include file="header.tpl"}
{$form|safe}
{if $groups}<table id="findgroups" class="fullwidth listing">
{foreach from=$groups item=group}
            <tr><td class="{cycle values='r0,r1'}">
                <div class="fr">
                     {include file="group/groupuserstatus.tpl" group=$group returnto='find'}
                </div>
                <div class="findgroupsdetails">
                     {include file="group/group.tpl" group=$group returnto='mygroups'}
                </div>
            </td></tr>
{/foreach}
			</table>
{$pagination|safe}
{else}
            <div class="message">{str tag="nogroupsfound" section="group"}</div>
{/if}
{include file="footer.tpl"}
