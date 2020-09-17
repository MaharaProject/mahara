{include file="header.tpl"}
<div class="card card-body">
    {$optionform|safe}
</div>
<div class="card">
    <h2 class="card-header">{str tag=addcategories section=admin}</h2>
    <div class="card-body">
        <p class="lead description">{str tag=groupcategoriespagedescription section=admin}</p>
        <div id="editmenus">
            <div id="menuitemlist"></div>
        </div>
    </div>
</div>
{include file="footer.tpl"}
