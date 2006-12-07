{include file='header.tpl'}

<div id="column-right">
</div>

<div id="column-left">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
			<h2>{str tag="administration"}</h2>
			
			<p>Screens here:</p>
			
			<ul>
				<li><strong><a href="options/">{str tag="adminsiteoptions" section="admin"}</a></strong><br>
				{str tag="adminsiteoptionsdescription" section="admin"}</li>
				<li>AdminSiteEditor - ???</li>
				<li><a href="options/">Site Options</a></li>
				<li><a href="institutions.php">Institutions</a></li>
				<li><a href="editsitepage.php">Site Pages</a></li>
				<li><a href="editmenu.php">Site Menu</a></li>
				<li><a href="plugins">Administer Plugins</a></li>
			</ul>
			
			{if $upgrades}
			<p><a href="upgrade.php">Run upgrade</a></p>
			{/if}

			</div>
		</span></span></span></span></div>	
	</div>
</div>

{include file='footer.tpl'}
