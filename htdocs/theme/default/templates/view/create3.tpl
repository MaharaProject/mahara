{include file="header.tpl"}

{include file="columnfullstart.tpl"}
	
{if $EDITMODE}
            <h2>{str tag=editview section=view}</h2>
{else}
            <h2>{str tag=createviewstep3 section=view}</h2>
{/if}

{literal}
<style type="text/css">
</style>
{/literal}
<div id="tree">Artefact Tree</div>
<div id="template">
    <form action="" method="post">
    {$template}
{if $EDITMODE}
        <input type="hidden" name="viewid" value="{$viewid}" id="template_viewid">
{/if}
        <input type="submit" class="submit" name="cancel" value="{str tag=cancel}" id="template_cancel">
{if $EDITMODE}
        <input type="submit" class="submit" name="submit" value="{str tag=save}" id="template_submit">
{else}
        <input type="submit" class="submit" name="back" value="{str tag=back section=view}" id="template_back">
        <input type="submit" class="submit" name="submit" value="{str tag=next section=view}" id="template_submit">
{/if}
    </form>
</div>
<script type="text/javascript">
{$rootinfo}

{literal}
function treeItemFormat(data, tree) {
    var item = LI({'id': 'art_' + data.id});

    if (data.container) {
        var toggleLink = DIV({'id': 'art_' + data.id + '_toggle', 'class' : 'toggle_link'}, tree.getExpandLink(item));
        appendChildNodes(item, toggleLink, ' ');
    }

    if (!data.title) {
        data.title = '';
    }

    var title = DIV({title: data.title, 'class': 'move_link'}, data.text);

    appendChildNodes(item, title);

    forEach(tree.statevars, function(j) {
        if (typeof(data[j]) != 'undefined') {
            item.setAttribute(j, data[j]);
        }
    });


    if (data.isartefact) {
        title.artefactid        = data.id;
        title.artefacttype      = data.type;
        title.artefactrendersto = data.rendersto;

        new MoveSource(title, {
            'selectedClass': 'moveselected',
            'acceptData': {
                'type': data.type,
                'rendersto': data.rendersto,
                'plugin': data.pluginname
            }
        });
    }

    return item;
}

// API
var tree = new CollapsableTree(data, '{/literal}{$WWWROOT}{literal}/json/artefacttree.json.php');
tree.setToggleIcons('{/literal}{$plusicon}{literal}', '{/literal}{$minusicon}{literal}');
tree.setFormatCallback(treeItemFormat);
tree.statevars.push('pluginname');
tree.statevars.push('parent');
addLoadEvent(function () {
    appendChildNodes('tree', tree.render());
    expandDownToViewport('tree');
    expandDownToViewport('template');
});

{/literal}
</script>

{include file="columnfullend.tpl"}

{include file="footer.tpl"}
