{include file="header.tpl"}
{if $allowextralicenses}
<div class="btn-top-right btn-group btn-group-top">
    <a href="license-edit.php?add=add" class="btn btn-secondary">
        <span class="icon icon-plus left" role="presentation" aria-hidden="true"></span>
        {str tag=addsitelicense section=admin}
    </a>
</div>
{/if}
<div class="row">
    <div class="col-md-12">
        <p class='lead'>{str tag=sitelicensesdesc section=admin}</p>
        {if !$enabled}
        <p class="alert alert-warning">{str tag=sitelicensesdisablednote section=admin args=$WWWROOT}</p>
        {/if}
        <div class="card">
            <div class="card-body">
                <form id="sitelicenses" action="" method="post" name="sitelicenses">
                {if $errors}
                    {foreach from=$errors item=e}
                        <div class="errmsg">{$e}</div>
                    {/foreach}
                {/if}
                <table class="fullwidth table table-striped">
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
                        <tr>
                            <td>{if $l->icon}<img src="{license_icon_url($l->icon)}" alt="{$l->displayname}">{/if}</td>
                            <td><a href="{$l->name}">{$l->displayname}</a></td>
                            <td>{$l->shortname}</td>
                            <td><a href="{$l->name}">{$l->name}</a></td>
                            <td class="control-buttons">
                                <div class="btn-group">
                                    <a href="license-edit.php?edit={$l->name|escape:url}" title="{str tag=edit}" class="btn btn-secondary btn-sm">
                                        <span class="icon icon-pencil icon-lg" role="presentation" aria-hidden="true"></span>
                                        <span class="sr-only">{str(tag=editspecific arg1=$l->shortname)|escape:html|safe}</span>
                                    </a>
                                    <button class="btn btn-secondary btn-sm" type="submit" title="{str tag=delete}" name="license_delete[{$l->name}]" alt="{str(tag=deletespecific arg1=$l->shortname)|escape:html|safe}">
                                        <span class="icon icon-trash text-danger icon-lg" role="presentation" aria-hidden="true"></span>
                                        <span class="sr-only">{str tag=delete}</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                {if $extralicenses}
                    <p>{str tag=extralicensesdescription section=admin}</p>
                    <ul>
                    {foreach from=$extralicenses item=l}
                        <li>{$l}</li>
                    {/foreach}
                    </ul>
                {/if}
                </form>
            </div>
        </div>
    </div>
</div>
{include file="footer.tpl"}
