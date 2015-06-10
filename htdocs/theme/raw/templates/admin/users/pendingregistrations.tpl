{include file="header.tpl"}
<div class="panel panel-body">
	<p>{str tag="pendingregistrationspagedescription" section="admin"}</p>
	{$institutionselector|safe}
	<div id="pendingonlineistcontainer">
	    <table id="pendinglist" class="fullwidth listing">
	        <tbody>
				{$data|safe}
	        </tbody>
	    </table>
	</div>
</div>
{include file="footer.tpl"}
