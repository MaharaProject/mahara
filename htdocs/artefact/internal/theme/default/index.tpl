{include file="header.tpl"}

<div id="column-right">
{include file="adminmenu.tpl"}
</div>

<div id="column-left">
	<div class="content">
		<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span class="cnr-bl"><span class="cnr-br">
			<div class="maincontent">
            <div class="fr"><a href="profileicons.php">{str tag="editprofileicons" section="artefact.internal"} &raquo;</a></div>
			<h2>{str section="artefact.internal" tag="profile"}</h2>
			{$profileform}

			</div>
		</span></span></span></span></div>	
	</div>
</div>

{include file="footer.tpl"}
