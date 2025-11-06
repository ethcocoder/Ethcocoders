<?php
/**
 * ETHCO CODERS - Path Test
 * Test if CSS and JS files are accessible
 */

echo "<h2>Dashboard Path Test</h2>";
echo "<style>body { font-family: Arial; padding: 20px; background: #0a192f; color: #ccd6f6; } .success { color: #078930; } .error { color: #da121a; }</style>";

$baseDir = __DIR__;
$cssFile = $baseDir . '/assets/css/dashboard.css';
$jsFile = $baseDir . '/assets/js/dashboard.js';
$chatCSS = $baseDir . '/assets/css/chat.css';
$chatJS = $baseDir . '/assets/js/chat.js';

echo "<h3>File Existence Check:</h3>";
echo "<ul>";
echo "<li class='" . (file_exists($cssFile) ? "success" : "error") . "'>dashboard.css: " . (file_exists($cssFile) ? "✓ Exists" : "✗ Not Found") . "</li>";
echo "<li class='" . (file_exists($jsFile) ? "success" : "error") . "'>dashboard.js: " . (file_exists($jsFile) ? "✓ Exists" : "✗ Not Found") . "</li>";
echo "<li class='" . (file_exists($chatCSS) ? "success" : "error") . "'>chat.css: " . (file_exists($chatCSS) ? "✓ Exists" : "✗ Not Found") . "</li>";
echo "<li class='" . (file_exists($chatJS) ? "success" : "error") . "'>chat.js: " . (file_exists($chatJS) ? "✓ Exists" : "✗ Not Found") . "</li>";
echo "</ul>";

echo "<h3>Path Information:</h3>";
echo "<p><strong>Base Directory:</strong> $baseDir</p>";
echo "<p><strong>Script Name:</strong> " . $_SERVER['SCRIPT_NAME'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";

echo "<h3>Expected URLs:</h3>";
$baseUrl = dirname($_SERVER['SCRIPT_NAME']);
echo "<p><strong>Dashboard CSS:</strong> <a href='$baseUrl/assets/css/dashboard.css' target='_blank'>$baseUrl/assets/css/dashboard.css</a></p>";
echo "<p><strong>Dashboard JS:</strong> <a href='$baseUrl/assets/js/dashboard.js' target='_blank'>$baseUrl/assets/js/dashboard.js</a></p>";

echo "<h3>Test Links:</h3>";
echo "<p>Click these to verify files are accessible:</p>";
echo "<ul>";
echo "<li><a href='assets/css/dashboard.css' target='_blank'>dashboard.css (relative)</a></li>";
echo "<li><a href='assets/js/dashboard.js' target='_blank'>dashboard.js (relative)</a></li>";
echo "</ul>";

