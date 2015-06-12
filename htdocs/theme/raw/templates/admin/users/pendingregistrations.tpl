{include file="header.tpl"}
<div class="lead">{str tag="pendingregistrationspagedescription" section="admin"}</div>
<div class="panel panel-default panel-body">
	<div id="pendingonlineistcontainer">
	    <table id="pendinglist" class="fullwidth listing">
	        <tbody>
				{$data|safe}
	        </tbody>
	    </table>
	</div>
</div>
{include file="footer.tpl"}
