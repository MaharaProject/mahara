{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
			<div id="userview">
			<h3>{$NAME}</h3>
            <div></div>
            <div>
			<table id="userview_profile"><tbody>
		{foreach from=$USERFIELDS name=userfields key=key item=item}
			<tr>{if $smarty.foreach.userfields.first}
                <td style="width: 100px;" rowspan="{$smarty.foreach.userfields.total+1}">
                    <img src="{$WWWROOT}thumb.php?type=profileicon&maxsize=100&id={$USERID}" alt="">
                </td>{/if}
                <th>{str section=artefact.internal tag=$key}</th><td>{$item}</td>
            </tr>
		{/foreach}
			</tbody></table>
            </div>
		{if $VIEWS}
			<table id="userview_views"><thead><tr><th>
			{str section=mahara tag=views}
			</td></tr></thead>
			<tbody><tr><td><ul>
		{foreach from=$VIEWS key=key item=item name=view}
			<li><a href="{$WWWROOT}view/view.php?view={$key}">{$item}</a></li>
		{/foreach}
			</ul></td></tr></tbody></table>
		{/if}
		{if $USERGROUPS}
			<table id="userview_groups"><thead><tr><th colspan=2>
			{str section=mahara tag=groups}
			</td></tr></thead>
			<tbody>
		{foreach from=$USERGROUPS item=item}
                        <tr><td><a href="{$WWWROOT}contacts/groups/view.php?id={$item->id}">{$item->name}</a></td><td>{$item->type}</td></tr>
		{/foreach}
			</tbody></table>
		{/if}

                <br>
		{$INVITEFORM}
		{$ADDFORM}
		{$FRIENDFORM}
        {$MESSAGEFORM}
		</div>
{include file="columnleftend.tpl"}

{include file="footer.tpl"}
