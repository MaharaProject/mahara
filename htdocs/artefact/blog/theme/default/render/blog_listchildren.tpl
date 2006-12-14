{**
 * This smarty template renders a list of a blog's children.
 *}

<script type="text/javascript">
  {$javascript}
</script>

<h2>{$artefact->get('title')|escape}</h2>

<table id="blog_listchildren{$blockid}">
    <thead></thead>
    <tbody></tbody>
</table>
