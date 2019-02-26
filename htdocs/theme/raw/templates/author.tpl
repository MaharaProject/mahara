{if $realauthor}
    {assign var='indexhash' value=get_random_key()}

    <span>
      <a id="showauthor_{$indexhash}" href="">{$author|safe}</a>
    </span>
    <span class="real_author js-safe-hidden d-none">
        {if $realauthorlink}
          <a href="{$realauthorlink}">{$realauthor|safe}</a>
        {else}
          {$realauthor|safe}
        {/if}
    </span>
    <script>
    jQuery('#showauthor_{$indexhash}').on('click', function(e) {
        e.preventDefault();
        jQuery(this).parent().parent().find('span.real_author').removeClass('d-none js-safe-hidden');
        jQuery(this).parent().addClass('d-none js-safe-hidden');
    });
    </script>

{else}
    {$author|safe}
{/if}
