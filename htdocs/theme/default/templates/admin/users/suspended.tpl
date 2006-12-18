{include file="header.tpl"}

<div id="column-full">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
			<h2>{str tag=suspendedusers section=admin}</h2>

            {$buttonformopen}
            {$buttonform}
            <table id="suspendedlist">
                <thead>
                    <tr>
                        <th>{str tag=fullname}</th>
                        <th>{str tag=studentid}</th>
                        <th>{str tag=institution}</th>
                        <th>{str tag=suspendingadmin section=admin}</th>
                        <th>{str tag=suspensionreason section=admin}</th>
                        <th>{str tag=select}</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
            </form>

			</div>
		</span></span></span></span></div>	
	</div>
</div>

{include file="footer.tpl"}
