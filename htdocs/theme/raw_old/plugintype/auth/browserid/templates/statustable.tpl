<table class="table fullwidth table-padded">
    <thead>
        <tr>
            <th>{str tag='institutioncolumn' section='auth.browserid'}</th>
            <th>{str tag='numuserscolumn' section='auth.browserid'}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$instances item=item}
        <tr>
            <td>
                <h3 class="title">
                    <a href="{$WWWROOT}admin/users/institutions.php?i={$item->name}">{$item->displayname}</a>
                </h3>
            </td>
            <td>
                {if $item->numusers}
                    <a href="{$WWWROOT}admin/users/search.php?institution={$item->name}&authname=browserid">{str tag='nusers' section='mahara' arg1=$item->numusers}</a>
                {else}
                    {str tag='nusers' section='mahara' arg1=0}
                {/if}
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>
