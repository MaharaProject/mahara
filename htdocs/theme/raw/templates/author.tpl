{if $realauthor}
    <span id="hidden_author_{$author_link_index}">
      <a id="show_real_author_{$author_link_index}" class="show_real_author" href="">{$author|safe}</a>
    </span>
    <span id="real_author_{$author_link_index}" class="js-safe-hidden hidden">
        {if $realauthorlink}
          <a href="{$realauthorlink}">{$realauthor|safe}</a>
        {else}
          {$realauthor|safe}
        {/if}
    </span>
{else}
    {$author|safe}
{/if}
