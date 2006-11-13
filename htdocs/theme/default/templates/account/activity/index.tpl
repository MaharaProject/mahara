{include file="header.tpl"}
{include file="adminmenu.tpl"}

<h2>{str tag="activity"}</h2>

<div class="content">
<form method="post">
{str section='activity' tag='type'}:
<select name="type" onChange="{$typechange}">
    <option value="all">{str section='activity' tag='alltypes'}</option>
{foreach from=$types item='type'}
    {assign var="tag1" value=$type->name}
    {assign var="tag" value=type$tag1}
    <option value="{$type->name}">{str section='activity' tag=$tag}</option>
{/foreach}
</select>
</form>
<form method="post" onSubmit="{$markread}">
<table id="activitylist">
    <thead>
        <tr>
            <th></th>
            <th>{str section='activity' tag='type'}</th>
            <th>{str section='activity' tag='date'}</th>
            <th>{str section='activity' tag='read'}</th>
            <th>{str section='activity' tag='markasread'} [<a href="" onClick="{$selectall}">{str section='activity' tag='selectall'}</a>]</th>
            <th></th>
        </tr>
    </thead>
    <tbody>

    </tbody>
    <tfoot>
        <tr>
            <td align="right" colspan="4"><div id="messagediv"></div></td>
            <td align="right"><input type="submit" value="{str tag='update'}" /></td>
        </tr>
    </tfoot>
</table>
</form>
</div>

{include file="footer.tpl"}
