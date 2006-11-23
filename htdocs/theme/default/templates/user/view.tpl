{include file="header.tpl"}
{include file="searchbox.tpl"}
{include file="adminmenu.tpl"}

<div class="content">
    <h3>{$NAME}</h3>
    <table><tbody>
{foreach from=$PROFILE item=item}
    <tr><td>{str section=artefact.internal tag=$item.name}</td><td>{$item.value}</td></tr>
{/foreach}
    </tbody></table>
</div>

{include file="footer.tpl"}
