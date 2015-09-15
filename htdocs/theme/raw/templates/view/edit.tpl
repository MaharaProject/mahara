{include file="header.tpl"}

{include file="view/editviewtabs.tpl" selected='title' new=$new issiteview=$issiteview}
<div class="row">
    <div class="col-md-9">
    {$editview|safe}
    </div>
</div>
{include file="footer.tpl"}
