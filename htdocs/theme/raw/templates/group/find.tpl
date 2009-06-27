{include file="header.tpl"}
{$form}

{if $groups}
            <table id="findgroups" class="fullwidth listing">
{foreach from=$groups item=group name=groups}
                <tbody>
                    <tr class="r{cycle values=0,1}">
                        <td><div class="rel">
{include file="group/group.tpl" group=$group returnto='find'}
                        </div></td>
                    </tr>
{/foreach}
                </tbody>
            </table>

{$pagination}
{else}
            <div class="message">{str tag="nogroupsfound" section="group"}</div>
{/if}
{include file="footer.tpl"}
