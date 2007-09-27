{include file="header.tpl"}

{include file="columnfullstart.tpl"}

    <form action="" method="post">
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

            <a id="layout-link" href="layout.php?id=1"{if !$can_change_layout} style="display: none;"{/if}>Change View Layout</a>

            <div id="bottom-pane">
                <div id="column-container">
                    {$columns}
                    <div id="clearer">
                    </div>
                </div>
            </div>
            <script type="text/javascript">
            {literal}
            insertSiblingNodesAfter('bottom-pane', DIV({'id': 'views-loading'}, 'Loading...'));
            {/literal}
            </script>
        </div>
    </form>

{* TODO theme me *}
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
{include file="columnfullend.tpl"}

{include file="footer.tpl"}
