{include file="header.tpl"}
{include file="sidebar.tpl"}

{include file="columnleftstart.tpl"}
            
			{$profileform}
            <script type="text/javascript">
            {literal}
            insertSiblingNodesAfter('profileform', DIV({'id': 'profile-loading'}, IMG({'src': config.theme['images/loading.gif'], 'alt': ''}), ' ', get_string('loading')));
            {/literal}
            </script>

{include file="columnleftend.tpl"}
{include file="footer.tpl"}
