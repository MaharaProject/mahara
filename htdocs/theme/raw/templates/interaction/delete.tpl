{include file="header.tpl"}
<div class="panel panel-danger view-container">
    <h2 class="panel-heading">{$subheading}</h2>
    <div class="panel-body">
        {if $landingpagenote}<p class="lead">{$landingpagenote}</p>{/if}
        <p>{$message}</p>
        {$form|safe}
    </div>
</div>
{include file="footer.tpl"}
