{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
            
            <div class="fr"><span class="viewicon"><a href="{$WWWROOT}user/view.php?id={$USER->get('id')}">{str tag="viewmyprofile" section="artefact.internal"}</a></span></div>
            <div style="position: relative;">
            <div style="position: absolute; top: 3.5em; right: 0;"><a href="{$WWWROOT}artefact/internal/profileicons.php"><img src="{$WWWROOT}thumb.php?type=profileicon&maxsize=100&id={$USER->get('id')}" alt=""></a></div>
            </div>

			{$profileform}
{include file="columnleftend.tpl"}
{include file="footer.tpl"}
