{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
                <h2>{$group->name|escape}</h2>
                <div class="message">
                <h3>{$heading|escape}</h3>
                <p>{$message}</p>
                {$form}
                </div>
{include file="columnleftend.tpl"}
{include file="footer.tpl"}

