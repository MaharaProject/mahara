<html>
<head><title></title></head>
<body>
  <hr />
  <div>{str tag=htmlremovedmessage}</div>
  {if !empty($params.downloadurl)}
  <div>
     <a href="{$params.downloadurl}">{str tag=downloadoriginalversion}</a>
  </div>
  {/if}
  <hr />
  <div>
    {$content}
  </div>
</body>
</html>
