<?php
// HEADERS ANTI-CACHE AGRESIVOS
header("Cache-Control: no-cache, no-store, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

// Timestamp para cache busting
$timestamp = time();
$version = "20251031";
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  
  <link rel="icon" href="./favicon.ico"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <meta name="theme-color" content="#000000"/>
  <meta name="description" content="Liga de Padel de Bogotá - Sistema de Gestión"/>
  <link rel="apple-touch-icon" href="./logo192.png"/>
  <link rel="manifest" href="./manifest.json"/>
  <title>Liga de Padel de Bogotá</title>
  
  <link href="./static/css/main.f855e6bc.css?v=<?php echo $version; ?>&t=<?php echo $timestamp; ?>" rel="stylesheet">
</head>
<body>
  <noscript>You need to enable JavaScript to run this app.</noscript>
  <div id="root"></div>
  
  <script defer="defer" src="./static/js/main.374d2336.js?v=<?php echo $version; ?>&t=<?php echo $timestamp; ?>"></script>
  
  <script>
    console.log("🚫 PHP NO-CACHE:", "<?php echo date('Y-m-d H:i:s'); ?>");
    console.log("🚫 Timestamp:", "<?php echo $timestamp; ?>");
  </script>
</body>
</html>