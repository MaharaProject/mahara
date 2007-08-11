{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
            
            <div style="position: relative;">
            <div style="position: absolute; top: 3.5em; right: 0;"><img src="{$WWWROOT}thumb.php?type=profileicon&size=100x100&id={$USER->get('id')}" alt=""></div>
            </div>
			<h2>{str section="artefact.internal" tag="profile"}</h2>
			{$profileform}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
