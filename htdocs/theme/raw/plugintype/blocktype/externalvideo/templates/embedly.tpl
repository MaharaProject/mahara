<div class="mediaplayer-container text-center">

{if $type == 'link'}
    <a class="embedly-card" href="{if $url}{$url}{else}{$src}{/if}">{if $title}{$title}{/if}</a>
{/if}

{if $type == 'card'}
    <blockquote class="embedly-card" {$data}><h4><a href="{$url}">{if $title}{$title}{/if}</a></h4>{if $desc}<p>{$desc}</p>{/if}</blockquote>
{/if}

{if $type == 'div'}
    <div class="embedly-responsive" style="{$style1}"><iframe class="embedly-embed" allowfullscreen="1" src="{$src}" width="{$width}" height="{$height}" style="{$style2}"></iframe></div>
{/if}

{if $type == 'iframe'}
    <iframe class="embedly-embed" allowfullscreen="1" src="{$src}" width="{$width}" height="{$height}"></iframe>
{/if}

</div>

<script>

function embedLoaded() {

  //Embed was loaded.
  console.log('...loaded');
  var rows = jQuery('.js-col-row'),
               i, j,
               height,
               cols;
  for(i = 0; i < rows.length ; i = i + 1) {
    height = 0;
    cols = jQuery(rows[i]).find('.column .column-content');
    cols.height('auto');
    for(j = 0; j < cols.length ; j = j + 1) {
        height = jQuery(cols[j]).height() > height ? jQuery(cols[j]).height() : height;
    }
    cols.height(height);
  }

}

function checkembedLoaded() {
    console.log('loading...');

    var expected = jQuery('.mediaplayer-container').length;
    var count = 0;

    jQuery('.mediaplayer-container').each(function() {
        count += (jQuery(this).first().find('iframe').length) ? 1 : 0;
    });

    if (count == expected) {
        clearInterval(twt{$key});
        embedLoaded();
    }
}
var twt{$key} = setInterval(checkembedLoaded, 1000);
</script>