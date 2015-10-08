{include file="header.tpl"}
<div class="panel panel-default panel-body">
    {$optionform|safe}
</div>
<div class="panel panel-default">
    <h3 class="panel-heading">{str tag=addcategories section=admin}</h3>
    <div class="panel-body">
        <p class="lead text-small description">{str tag=groupcategoriespagedescription section=admin}</p>
        <div id="editmenus">
            <div id="menuitemlist"></div>
        </div>
    </div>
</div>
{include file="footer.tpl"}

