{include file="header.tpl"}

<div class="card card bg-danger text-white view-container">
    <h2 class="card-heading">{$subheading}</h2>
    <div class="card-body">
        <p>{$message}</p>
        {$form|safe}
    </div>
</div>

{include file="footer.tpl"}

