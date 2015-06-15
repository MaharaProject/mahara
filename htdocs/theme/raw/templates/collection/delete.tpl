{include file="header.tpl"}
<div class="panel panel-danger mtxl">
    <h2 class="panel-heading">{str tag=delete}</h2>
    <div class="panel-body">
        <p>{$message}</p>
        {$form|safe}
    </div>
</div>
{include file="footer.tpl"}

