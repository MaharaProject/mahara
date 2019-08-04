<div id="placeholderlist">
{$typeslist|safe}
{if $pagination.html}
    {$pagination.html|safe}
{/if}
</div>
{if $pagination.javascript}
    <script>
    {$pagination.javascript|safe}
    </script>
{/if}
