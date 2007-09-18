{include file="header.tpl"}

{include file="columnfullstart.tpl"}

    <form action="" method="post">
        <input type="hidden" id="viewid" name="view" value="1">
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

            <div id="bottom-pane">
                <div id="column-container">
                    {$columns}
                    <div id="clearer">
                        This is footer content
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

{include file="columnfullend.tpl"}

{include file="footer.tpl"}
