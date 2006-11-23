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

{foreach from=$PROFILE key=key item=item name=profile}
{if $smarty.foreach.profile.first}
    <h4>{str section=artefact.internal tag=profile}</h4>
    <table><tbody>
{/if}
    <tr><td>{str section=artefact.internal tag=$key}</td><td>{$item}</td></tr>
{if $smarty.foreach.profile.last}
    </tbody></table>
{/if}
{/foreach}

</div>

{include file="footer.tpl"}
