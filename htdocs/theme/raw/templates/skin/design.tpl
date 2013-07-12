{include file="header.tpl"}

            {$designskinform|safe}
            <script type="text/javascript">
{literal}            insertSiblingNodesAfter('designskinform', DIV({'id': 'viewskin-loading'}, IMG({'src': config.theme['images/loading.gif'], 'alt': ''}), ' ', get_string('loading')));{/literal}
            </script>

{include file="footer.tpl"}
