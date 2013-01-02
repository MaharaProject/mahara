<h2>{$title}</h2>
<p>{$description|clean_html|safe}</p>
{if $license}
  <div class="artefactlicense">
    {$license|safe}
  </div>
{/if}
