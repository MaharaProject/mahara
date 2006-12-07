{include file="header.tpl"}

<div id="column-right">
</div>

<div id="column-left">

<div class="content">
<div class="box-cnrs"><span class="cnr-tl"><span class="cnr-tr"><span 
class="cnr-bl"><span class="cnr-br">
	<div class="maincontent">
	
<h2>{str tag=createviewstep3}</h2>

{literal}
<style type="text/css">
</style>
{/literal}
<div id="tree">Artefact Tree</div>
<div id="template">
    <form action="" method="post">
    {$template}
        <input type="submit" name="cancel" value="{str tag=cancel}">
        <input type="submit" name="back" value="{str tag=back}">
        <input type="submit" name="submit" value="{str tag=next}">
    </form>
</div>
<script type="text/javascript">
{$rootinfo}

{literal}
function treeItemFormat(data, tree) {
    var item = LI({'id': 'art_' + data.id});
    debugObject(data);

    if (data.container) {
        var toggleLink = SPAN({'id': 'art_' + data.id + '_toggle'}, tree.getExpandLink(item));
        appendChildNodes(item, toggleLink, ' ');
    }

    if (!data.title) {
        data.title = '';
    }

    var title = SPAN({title: data.title}, data.text);

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

        addElementClass(title, 'render_listitem');
        addElementClass(title, 'render_children');
        addElementClass(title, 'render_full');
        addElementClass(title, 'render_meta');
        new Draggable(title, {
            'starteffect': function (element) {
                element.oldParentNode  = element.parentNode;
                element.oldNextSibling = element.nextSibling;
                appendChildNodes('header', element)
            },
            'endeffect': function (element) {
                if(element.oldNextSibling) {
                    insertSiblingNodesBefore(element.oldNextSibling, element)
                }
                else {
                    appendChildNodes(element.oldParentNode, element);
                }
            },
            'reverteffect': function (element, top_offset, left_offset) {
                return new MochiKit.Visual.Move(element, {x: -left_offset, y: -top_offset, duration: 0.0});
            },
            'ghosting': true,
            'revert': true
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

	</div>
</span></span></span></span></div>	
</div>
</div>
{include file="footer.tpl"}
