{include file="header.tpl"}

{include file="adminmenu.tpl"}

<h2>{str tag="watchlist"}</h2>

<div class="content">
<form method="post">
{str tag='filter'}:
<select name="type" onChange="{$typechange}">
    <option value="views">{str section='activity' tag='viewsandartefacts'}</option>
    <option value="communities">{str section='activity' tag='communities'}</option>
</select>
</form>
<p><b><div id="typeheader">{str section='activity' tag='monitored'} {$typestr}</div></b></p>
<form method="post" onSubmit="{$stopmonitoring}">
<table id="watchlist">
    <thead>
        <tr>
            <th></th>
            <th></th>
            <th>{str section='activity' tag='stopmonitoring'} [<a href="" onClick="{$selectall}">{str section='activity' tag='selectall'}</a>]</th>
        </tr>
    </thead>
    <tbody>

    </tbody>
    <tfoot>
        <tr>
            <td align="right" colspan="4"><div id="messagediv"></div></td>
            <td align="right"><input type="submit" value="{str tag='stopmonitoring' section='activity'}" /></td>
        </tr>
    </tfoot>
</table>
</form>
</div>

{include file="footer.tpl"}
