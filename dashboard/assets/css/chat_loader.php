<?php
// Serve chat.css through PHP to bypass host static file restrictions
header('Content-Type: text/css; charset=UTF-8');
header('Cache-Control: max-age=300, public');
readfile(__DIR__ . '/message.css');
exit;


