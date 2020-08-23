<div class="mediaplayer-container text-center">
  <div id="vid_{$blockid}" class="mediaplayer" style="margin: 0 auto;">
    {$html|clean_html|safe}
    {if $jsurl}
        <script>
        var blockinstance_{$blockid}_loaded = false;
        $j('#blockinstance_{$blockid} .js-heading a[data-toggle="collapse"]').on("click", function() {
            if (blockinstance_{$blockid}_loaded === false) {
                {if $jsflashvars}
                var embedobj = $j('<object></object>').attr('width', '{$width}')
                                               .attr('height', '{$height}');
                $j('<param></param>').attr('name', 'movie')
                               .attr('value', '{$jsurl}')
                               .appendTo(embedobj);
                $j('<param></param>').attr('name', 'allowfullscreen')
                               .attr('value', 'true')
                               .appendTo(embedobj);
                $j('<param></param>').attr('name', 'allowscriptaccess')
                               .attr('value', 'always')
                               .appendTo(embedobj);
                $j('<param></param>').attr('name', 'wmode')
                               .attr('value', 'transparent')
                               .appendTo(embedobj);
                $j('<param></param>').attr('name', 'flashvars')
                               .attr('value', '{$jsflashvars}')
                               .appendTo(embedobj);
                $j('<embed></embed>').attr('src', '{$jsurl}')
                               .attr('allow', 'fullscreen')
                               .attr('wmode', 'transparent')
                               .attr('allowscriptaccess', 'always')
                               .attr('width', '{$width}')
                               .attr('height', '{$height}')
                               .attr('flashvars', '{$jsflashvars}')
                               .appendTo(embedobj);
                embedobj.appendTo($j('#vid_{$blockid}'));
                blockinstance_{$blockid}_loaded = true;
                $j('#user_block_{$blockid}_waiting').css('display','none');
                {else}
                var jsurl = '{$jsurl}'.replace(/&amp;/g,'&');
                $j('<iframe></iframe>').attr('src', jsurl)
                                .attr('width', '{$width}')
                                .attr('height', '{$height}')
                                .attr('allowfullscreen', '')
                                .appendTo($j('#vid_{$blockid}'))
                                .on("load", function() {
                                    blockinstance_{$blockid}_loaded = true;
                                    $j('#user_block_{$blockid}_waiting').css('display','none');
                                });
                {/if}
            }
        });
        </script>
    {/if}
  </div>
</div>
