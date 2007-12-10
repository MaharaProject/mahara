{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
            
            <div style="position: relative;">
            <div style="position: absolute; top: 3.5em; right: 0;"><a href="{$WWWROOT}artefact/internal/profileicons.php"><img src="{$WWWROOT}thumb.php?type=profileicon&maxsize=100&id={$USER->get('id')}" alt=""></a></div>
            </div>
			<h2>{str section="artefact.internal" tag="profile"}</h2>
			{$profileform}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
