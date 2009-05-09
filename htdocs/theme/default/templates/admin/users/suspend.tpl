{include file="header.tpl"}

{include file="columnfullstart.tpl"}

    <h2>{str tag="suspenduser" section="admin"}</h2>
    <h3><a href="{$WWWROOT}user/view.php?id={$user->id}">{$user->firstname} {$user->lastname} ({$user->username})</a></h3>
    {$form}
{include file="columnfullend.tpl"}
{include file="footer.tpl"}

