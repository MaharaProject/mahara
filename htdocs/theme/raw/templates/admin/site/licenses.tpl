{include file="header.tpl"}
    <p>{str tag=sitelicensesdescription section=admin}</p>
    {if !$enabled}
    <p>{str tag=sitelicensesdisablednote section=admin args=$WWWROOT}</p>
    {/if}
    <form id="sitelicenses" action="" method="post" name="sitelicenses">
    {if $errors}
        {foreach from=$errors item=e}
            <div class="errmsg">{$e}</div>
        {/foreach}
    {/if}
    <table>
        <tr class="{cycle values='r0,r1'}">
            <th>{str tag=licenseiconlabel section=admin}</td>
            <th>{str tag=licensedisplaynamelabel section=admin}</td>
            <th>{str tag=licenseshortnamelabel section=admin}</td>
            <th>{str tag=licensenamelabel section=admin}</td>
            <th>&nbsp;</td>
        </tr>
	    {foreach from=$licenses key=i item=l}
            <tr class="{cycle values='r0,r1'}">
                <td>{if $l->icon}<img src="{license_icon_url($l->icon)}">{/if}</td>
                <td><a href="{$l->name}">{$l->displayname}</a></td>
                <td>{$l->shortname}</td>
                <td><a href="{$l->name}">{$l->name}</a></td>
                <td>
                <a href="license-edit.php?edit={$l->name|escape:url}"><img src="{$THEME->get_url('images/edit.gif')}"></a>
                <input type="image" title="Delete" value="" name="license_delete[{$l->name}]" src="{$THEME->get_url('images/icon_close.gif')}"></td>
            </tr>
        {/foreach}
    </table>
    <a href="license-edit.php?add=add" class="btn">{str tag=addsitelicense section=admin}</a>
    {if $extralicenses}
        <p>{str tag=extralicensesdescription section=admin}</p>
        <ul>
        {foreach from=$extralicenses item=l}
            <li>{$l}</li>
        {/foreach}
        </ul>
    {/if}
    </form>
{include file="footer.tpl"}
