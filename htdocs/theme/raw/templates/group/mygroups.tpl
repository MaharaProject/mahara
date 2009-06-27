{include file="header.tpl"}
            <div class="rbuttons">
                <a href="{$WWWROOT}group/create.php" class="btn">{str tag="creategroup" section="group"}</a>
            </div>
{$form}
{if $groups}
            <table class="fullwidth listing">
                <tbody>
{foreach from=$groups item=group}
                    <tr class="r{cycle values=0,1}">
                        <td><div class="rel">
{include file="group/group.tpl" group=$group returnto='mygroups'}
                        </div></td>
                    </tr>
{/foreach}
                </tbody>
            </table>
{$pagination}
{else}
            <div class="message">{str tag="trysearchingforgroups" section="group" args=$searchingforgroups}</div>
{/if}
{include file="footer.tpl"}
