{include file="header.tpl"}

    <h2>{$displayname}: {str tag='wall' section='blocktype.wall'}</h2>
    <div class="rbuttons"><a href="{profile_url($owner)}" class="btn">{str tag='backtoprofile' section='blocktype.wall'}</a></div>
    {include file="blocktype:wall:inlineposts.tpl"}


{include file="footer.tpl"}
