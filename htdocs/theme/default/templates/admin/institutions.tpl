{include file="header.tpl"}

<h2>AdminInstitutions</h2>

{if $institution_form}
{if $add}
<h3>Add Institution</h3>
{/if}
{$institution_form}
{else}
<p>Here is a list of all installed institutions.</p>

<table>
    <tr>
        <th>Institution</th>
        <th>Authentication Method</th>
        <th>Registration Allowed?</th>
        <th></th>
    </tr>
    {foreach from=$institutions item=institution}
    <tr>
        <td>{$institution->displayname|escape}</td>
        <td>{$institution->authplugin}</td>
        <td>{if $institution->authplugin == 'internal'}{if $institution->registerallowed}{str tag="yes"}{else}{str tag="no"}{/if}{else}-{/if}</td>
        <td>
            <form action="" method="post">
                <input type="hidden" name="i" value="{$institution->name}">
                <input type="submit" name="edit" value="Edit">
                {if $institution->candelete}<input type="submit" name="delete" value="Delete">{/if}
            </form>
        </td>
    </tr>
    {/foreach}
    <tr>
        <td colspan="4">
            <form action="" method="post">
                <input type="submit" name="add" value="Add Institution">
            </form>
        </td>
    </tr>
</table>
{/if}

{include file="footer.tpl"}
