{include file="header.tpl"}
<div class="card card-default card-body">
    {$optionform|safe}
</div>
<div class="card card-default">
    <h3 class="card-heading">{str tag=addcategories section=admin}</h3>
    <div class="card-body">
        <p class="lead text-small description">{str tag=groupcategoriespagedescription section=admin}</p>
        <div id="editmenus">
            <div id="menuitemlist"></div>
        </div>
    </div>
</div>
{include file="footer.tpl"}

