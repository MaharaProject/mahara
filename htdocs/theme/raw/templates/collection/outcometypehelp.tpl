<div>
{foreach from=$outcometypes item=type}
  <p><strong>{$type->abbreviation}:</strong>
  {$type->title}</p>
{/foreach}
</div>