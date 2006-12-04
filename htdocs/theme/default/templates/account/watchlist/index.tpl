{include file="header.tpl"}

{include file="adminmenu.tpl"}


<div class="content">
<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span 
class="cnr-bl"><span class="cnr-br">
	<div class="maincontent">
	
<h2>{str tag="watchlist"}</h2>
<form method="post">
{str tag='filter'}:
<select name="type" onChange="{$typechange}">
    <option value="views">{str section='activity' tag='views'}</option>
    <option value="artefacts">{str section='activity' tag='artefacts'}</option>
    <option value="communities">{str section='activity' tag='communities'}</option>
</select>
</form>
<p><b><div id="typeheader">{str section='activity' tag='monitored'} {$typestr}</div></b></p>
<form method="post" onSubmit="{$stopmonitoring}">
<table id="watchlist">
    <thead>
        <tr>
            <th></th>
            <th>[<a href="" onClick="{$selectall}">{str section='activity' tag='selectall'}</a>]</th>
            <th id="recurseheader">{$recursestr}</th>
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
</span></span></span></span></div>	
</div>

{include file="footer.tpl"}
