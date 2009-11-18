{include file="header.tpl"}

            <div class="rbuttons">
                <a href="{$WWWROOT}user/view.php">{str tag=viewmyprofilepage}</a>&nbsp;
                <a class="btn-edit" href="{$WWWROOT}view/blocks.php?profile=1">{str tag=editmyprofilepage}</a>
            </div>
            
            {$profileform}
            <script type="text/javascript">
{literal}            insertSiblingNodesAfter('profileform', DIV({'id': 'profile-loading'}, IMG({'src': config.theme['images/loading.gif'], 'alt': ''}), ' ', get_string('loading')));{/literal}
            </script>

{include file="footer.tpl"}
