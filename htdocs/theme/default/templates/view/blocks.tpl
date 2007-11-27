{include file="header.tpl"}

{include file="columnfullstart.tpl"}

    <form action="{$formurl}" method="post">
        <input type="submit" name="{$action_name}" id="action-dummy" class="hidden">
        <input type="hidden" id="viewid" name="id" value="{$view}">
        <input type="hidden" name="change" value="1">
        <input type="hidden" id="category" name="c" value="{$category}">
        {if $new}<input type="hidden" name="new" value="1">{/if}
        <div id="page">
            <div id="top-pane">
                <div id="category-list">
                    {$category_list}
                </div>
                <div id="blocktype-list">
                    {$blocktype_list}
                </div>
                <div id="blocktype-footer"></div>
            </div>

            <div class="fr" style="font-size: smaller; padding-right: 5px;">
                <strong><a href="view.php?id={$view}">Display my view &raquo;</a></strong>
            </div>

            <a id="layout-link" href="layout.php?id={$view}&c={$category}&new={$new}"{if !$can_change_layout} class="disabled"{/if}>{str tag='changeviewlayout' section='view'}</a>

            <div id="bottom-pane">
                <div id="column-container">
                    {$columns}
                    <div class="cb">
                    </div>
                </div>
            </div>
            <script type="text/javascript">
            {literal}
            insertSiblingNodesAfter('bottom-pane', DIV({'id': 'views-loading'}, IMG({'src': config.theme['images/loading.gif'], 'alt': ''}), ' ', get_string('loading')));
            {/literal}
            </script>
        </div>
    </form>

    <div id="view-wizard-controls" class="center">
{if $new}
        <form action="{$WWWROOT}view/edit.php" method="GET">
            <input type="hidden" name="id" value="{$view}">
            <input type="hidden" name="new" value="1">
            <input type="submit" class="submit" value="{str tag='back'}">
        </form>
        <form action="{$WWWROOT}view/access.php" method="GET">
            <input type="hidden" name="id" value="{$view}">
            <input type="hidden" name="new" value="1">
            <input class="submit" type="submit" value="{str tag='next'}">
        </form>
{else}
        <form action="{$WWWROOT}view/" method="GET">
            <input class="submit" type="submit" value="{str tag='done'}">
        </form>
{/if}
    </div>
{include file="columnfullend.tpl"}

{include file="footer.tpl"}
