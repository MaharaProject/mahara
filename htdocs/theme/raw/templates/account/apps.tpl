{include file="header.tpl"}
<p class="lead">{str tag="acccountappsdescription"}</p>

{if $hasapps}
    <p>{str tag="acccountchooseappsdescription"}</p>
{else}
    <p>{str tag="acccountaddappsdescription"}</p>
{/if}

{include file="footer.tpl"}

