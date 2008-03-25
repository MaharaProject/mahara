{include file="header.tpl"}

{include file="columnfullstart.tpl"}
<div id="wall">
    <h3>{$owner->displayname}: {str tag='wall' section='blocktype.wall'}</h3>
    ( <a href="{$WWWROOT}/user/view.php?id={$owner->id}">{str tag='backtoprofile' section='blocktype.wall'}</a> )
    {include file="blocktype:wall:inlineposts.tpl}
</div>
				
{include file="columnfullend.tpl"}

{include file="footer.tpl"}
