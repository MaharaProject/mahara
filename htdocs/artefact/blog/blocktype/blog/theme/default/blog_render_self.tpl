{if !$options.hidetitle}<h2>{$artefacttitle}</h2>{/if}

<table id="blog_renderfull{$blockid}">
    <thead></thead>
    <tbody></tbody>
</table>

<script type="text/javascript">
var blog_renderfull{$blockid} = new TableRenderer(
    'blog_renderfull{$blockid}',
    config['wwwroot'] + 'artefact/blog/blog_render_self.json.php',
    [
        {literal}function(r) {
            var td = TD();
            if (r.content.html) {
                td.innerHTML = r.content.html;
            }
            else {
                td.innerHTML = r.content;
            }
            return td;
        }{/literal}
    ]
);

blog_renderfull{$blockid}.statevars.push('id');
blog_renderfull{$blockid}.id = {$enc_id};
blog_renderfull{$blockid}.limit = {$limit};
blog_renderfull{$blockid}.statevars.push('options');
blog_renderfull{$blockid}.options = {$enc_options};

blog_renderfull{$blockid}.updateOnLoad();
</script>
