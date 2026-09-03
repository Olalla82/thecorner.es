<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html>
<head>
    <title>TEST</title>
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-family: Arial;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        h1 { font-size: 4em; text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }
    </style>
</head>
<body>
    <h1>✅ FUNCIONA - <?= date('H:i:s') ?></h1>
</body>
</html>
