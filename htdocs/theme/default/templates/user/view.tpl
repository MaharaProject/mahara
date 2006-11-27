{include file="header.tpl"}
{include file="searchbox.tpl"}
{include file="adminmenu.tpl"}

<div class="content">

{$INVITEFORM}
{$ADDFORM}

    <h3>{$NAME}</h3>
    <table><tbody>
{foreach from=$USERFIELDS key=key item=item}
    <tr><td>{str section=mahara tag=$key}</td><td>{$item}</td></tr>
{/foreach}
    </tbody></table>

{if $PROFILE}
    <h4>{str section=artefact.internal tag=profile}</h4>
    <table><tbody>
{foreach from=$PROFILE key=key item=item name=profile}
    <tr><td>{str section=artefact.internal tag=$key}</td><td>{$item}</td></tr>
{/foreach}
    </tbody></table>
{/if}

{if $VIEWS}
    <h4>{str section=mahara tag=views}</h4>
    <ul>
{foreach from=$VIEWS key=key item=item name=view}
    <li><a href="{$WWWROOT}view/view.php?id={$key}">{$item}</a></li>
{/foreach}
    </ul>
{/if}

</div>

{include file="footer.tpl"}
