{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
            
			<div class="fr leftrightlink"><a href="profileicons.php" id="editprofileicons">{str tag="editprofileicons" section="artefact.internal"} &raquo;</a></div>
            <div class="fr cr"><img src="{$WWWROOT}thumb.php?type=profileicon&size=100x100&id={$USER->get('id')}" alt=""></div>
			<h2>{str section="artefact.internal" tag="profile"}</h2>
			{$profileform}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
