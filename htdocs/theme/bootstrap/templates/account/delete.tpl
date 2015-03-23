{include file="header.tpl"}
<div id="deleteaccount">
    <h1>
        {str tag=deleteaccount section=account}
    </h1>
    <p class="lead">
        {str tag=deleteaccountdescription section=account}
    </p>
    {$form|safe}
</div>
{include file="footer.tpl"}

