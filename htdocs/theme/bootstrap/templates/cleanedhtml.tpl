<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8">
    <title>{$pagetitle}</title>
</head>
<body>
  <div style="font-family: Arial, 'Nimbus Sans L', Helvetica, sans-serif; font-size: 100%; border-bottom: 1px solid #aaa; margin-bottom: 10px; padding-bottom: 5px; text-align: center;">
    <div style="float: left; margin-right: 10px; height: 20px;"><a href="" onclick="history.go(-1); return false;">&laquo; {str tag="back"}</a></div>
    {$htmlremovedmessage|clean_html|safe}{if $params.downloadurl} <a href="{$params.downloadurl}">{str tag="downloadoriginalversion" section="artefact.file"}</a>{/if}</div>
  <div>
    {$content|clean_html|safe}
  </div>
  {mahara_performance_info}
</body>
</html>
