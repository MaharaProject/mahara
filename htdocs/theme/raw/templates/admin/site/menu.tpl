{include file="header.tpl"}
<div class="row">
    <div class="col-md-9" id="editmenus">
        <div class="panel panel-default">
            <h3 class="panel-heading">{str tag=linksandresourcesmenu section=admin }</h3>
            <div class="panel-body">
                <p class="lead">{str tag=linksandresourcesmenupagedescription section=admin args=$descriptionstrargs}</p>
                <div class="dropdown form-group">
                    <label for="menuselect">{str tag=edit}:</label>
                    <span class="picker">
                        <select class="form-control select" id="menuselect" name="menuselect">
                        {foreach from=$MENUS item=menu}
                            <option value={$menu.value}>{$menu.name}</option>
                        {/foreach}
                        </select>
                    </span>
                    {contextualhelp plugintype='core' pluginname='admin' section='adminmenuselect'}
                </div>
                <div id="menuitemlist"></div>
            </div>
        </div>
        <div class="panel panel-default">
            <h3 class="panel-heading">{str tag=footermenu section=admin}</h3>
            <div class="panel-body">
                <p class="lead">
                    {str tag=footermenudescription section=admin}
                </p>
                {$footerform|safe}
            </div>
        </div>
    </div>
</div>

{include file="footer.tpl"}
