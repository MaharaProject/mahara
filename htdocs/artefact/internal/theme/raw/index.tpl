{auto_escape off}
{include file="header.tpl"}
            {$profileform}
            <script type="text/javascript">
{literal}            insertSiblingNodesAfter('profileform', DIV({'id': 'profile-loading'}, IMG({'src': config.theme['images/loading.gif'], 'alt': ''}), ' ', get_string('loading')));{/literal}
            </script>

{include file="footer.tpl"}
{/auto_escape}
