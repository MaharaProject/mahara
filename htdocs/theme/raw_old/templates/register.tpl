{include file="header.tpl"}

{if $register_form}
    <p>{$registerdescription|clean_html|safe}</P>
    {$register_form|safe}
    <div id="termscontainer">{if $termsandconditions}{$termsandconditions|clean_html|safe}{/if}</div>
{elseif $register_profile_form}
    {$register_profile_form|safe}
{/if}

{include file="footer.tpl"}
