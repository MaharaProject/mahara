<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
    <title>{$pagetitle}</title>
</head>
<body>
  <div style="font-family: Arial, sans-serif; font-size: smaller; border-bottom: 1px solid #aaa; margin-bottom: 1em; padding-bottom: .5em; text-align: center;">
    <div style="float: left; margin-right: 1em; height: 2em;"><a href="" onclick="history.go(-1); return false;">&laquo; {str tag="back"}</a></div>
    {$htmlremovedmessage}{if !empty($params.downloadurl)} <a href="{$params.downloadurl}">{str tag="downloadoriginalversion" section="artefact.file"}</a>{/if}</div>
  <div>
    {$content}
  </div>
</body>
</html>
