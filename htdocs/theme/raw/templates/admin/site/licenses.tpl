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
    <table class="fullwidth">
        <thead>
        <tr>
            <th>{str tag=licenseiconlabel section=admin}</th>
            <th>{str tag=licensedisplaynamelabel section=admin}</th>
            <th>{str tag=licenseshortnamelabel section=admin}</th>
            <th>{str tag=licensenamelabel section=admin}</th>
            <th>&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        {foreach from=$licenses key=i item=l}
            <tr class="{cycle values='r0,r1'}">
                <td>{if $l->icon}<img src="{license_icon_url($l->icon)}">{/if}</td>
                <td><a href="{$l->name}">{$l->displayname}</a></td>
                <td>{$l->shortname}</td>
                <td><a href="{$l->name}">{$l->name}</a></td>
                <td class="btns2">
                <a href="license-edit.php?edit={$l->name|escape:url}"><img src="{$THEME->get_url('images/btn_edit.png')}"></a>
                <input type="image" title="Delete" value="" name="license_delete[{$l->name}]" src="{$THEME->get_url('images/btn_deleteremove.png')}"></td>
            </tr>
        {/foreach}
        </tbody>
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
