<?php
// Inc/Components/main_footer.php

// Determine the base path for assets/links
$currentPath = $_SERVER['REQUEST_URI'];
// Check if we are in a subfolder (like Pages/ or Src/)
$isInSubfolder = (strpos($currentPath, '/Pages/') !== false || strpos($currentPath, '/Inc/') !== false);
$basePath = $isInSubfolder ? '../' : './';

// Include the footer HTML content
require_once __DIR__ . '/footers.php';

// Include the scripts and closing tags
require_once __DIR__ . '/footer.php';
?>