<?php
// Serve chat.js through PHP to bypass host static file restrictions
header('Content-Type: application/javascript; charset=UTF-8');
header('Cache-Control: max-age=300, public');
readfile(__DIR__ . '/message.js');
exit;


