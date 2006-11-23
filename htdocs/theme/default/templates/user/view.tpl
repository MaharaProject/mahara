{include file="header.tpl"}
{include file="searchbox.tpl"}
{include file="adminmenu.tpl"}

<div class="content">

    <h3>{$NAME}</h3>
    <table><tbody>
{foreach from=$USERFIELDS key=key item=item}
    <tr><td>{str section=mahara tag=$key}</td><td>{$item}</td></tr>
{/foreach}
    </tbody></table>

    <table><tbody>
{foreach from=$PROFILE key=key item=item name=profile}
{if $smarty.foreach.profile.first}
    <tr><th>{str section=artefact.internal tag=profile}</th></tr>
{/if}
    <tr><td>{str section=artefact.internal tag=$key}</td><td>{$item}</td></tr>
{/foreach}
    </tbody></table>

</div>

{include file="footer.tpl"}
