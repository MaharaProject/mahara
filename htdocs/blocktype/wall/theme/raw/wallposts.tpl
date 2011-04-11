{include file="header.tpl"}

    <h2>{$owner->displayname}: {str tag='wall' section='blocktype.wall'}</h2>
    <div class="rbuttons"><a href="{$WWWROOT}/user/view.php?id={$owner->id}" class="btn btn-back">{str tag='backtoprofile' section='blocktype.wall'}</a></div>
    {include file="blocktype:wall:inlineposts.tpl"}


{include file="footer.tpl"}
