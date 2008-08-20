{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
                <h2>{$group->name|escape}</h2>

{include file="group/tabstart.tpl" current="members"}

                <div class="group-info-para"><h3>{$subtitle}</h3><div>
                <div class="group-info-para">
                    {$changeform}
                </div>
                <br />


{include file="group/tabend.tpl"}

{include file="columnleftend.tpl"}
{include file="footer.tpl"}


