<?php
$memoryLimit = 4000000000;
$chunkSize = 1000000000; 
$report = '';
$filecount = $infected = $dircount = 0;
$CONFIG = Array();
$CONFIG['extensions'] = Array();
$abort = $CONFIG['debug'] = FALSE;
$AVLogDir = $ScanDir;
$AVLogFile = $PHPAVLogFile;

// / -----------------------------------------------------------------------------------
// / Functions
function file_scan($folder, $defs, $debug, $AVLogFile) {
  // Hunts files/folders recursively for scannable items.
  global $report, $memoryLimit;
  $dircount = 0;
  if ($d = @dir($folder)) {
    while (false !== ($entry = $d->read())) {
      $isdir = @is_dir($folder.DIRECTORY_SEPARATOR.$entry);
      if (!$isdir and $entry != '.' and $entry != '..') {      
        virus_check($folder.DIRECTORY_SEPARATOR.$entry, $defs, $debug, $defData); } 
      elseif ($isdir and $entry != '.' and $entry != '..') {
        $txt = 'OP-Act: Scanning folder '.$folder.' ... ';
        $MAKELogFile = file_put_contents($AVLogFile, $txt.PHP_EOL, FILE_APPEND);        
        $dircount++;
        file_scan($folder.DIRECTORY_SEPARATOR.$entry, $defs, $debug, $defData); } }
    $d->close(); } }

function load_defs($file, $debug) {
  // Reads tab-delimited defs file.
  global $AVLogFile;
  $defs = file($file);
  $counter = 0;
  $counttop = sizeof($defs);
  while ($counter < $counttop) {
    $defs[$counter] = explode('  ', $defs[$counter]);
    $counter++; }
  $txt = 'OP-Act: Loaded '.sizeof($defs).' virus definitions.';
  $MAKELogFile = file_put_contents($AVLogFile, $txt.PHP_EOL, FILE_APPEND);
  return $defs; }

function check_defs($file) {
  // Check for >755 perms on virus defs.
  clearstatcache();
  $perms = substr(decoct(fileperms($file)),-2);
  if ($perms > 55) return false;
  else return true; }

function virus_check($file, $defs, $debug, $defData, $AVLogFile) {
  // Hashes and checks files/folders for viruses against static virus defs.
  global $memoryLimit, $chunkSize, $report, $CONFIG;
  $infected = $filecount = 0;
  $filecount++;
  if ($file !== 'virus.def') {
    if (file_exists($file)) { 
      $filesize = filesize($file);
      $data1 = hash_file('md5', $file);
      $data2 = hash_file('sha256', $file);
      $data3 = hash_file('sha1', $file);
      // / Scan files larger than the memory limit by breaking them into chunks.
      if ($filesize >= $memoryLimit && file_exists($file)) { 
        $txt = 'OP-Act: Chunking file ... ';
        $MAKELogFile = file_put_contents($AVLogFile, $txt.PHP_EOL, FILE_APPEND);
        $handle = @fopen($file, "r");
        if ($handle) {
          while (($buffer = fgets($handle, $chunkSize)) !== false) {
            $data = $buffer; 
            if ($debug) { 
              $txt = 'OP-Act: Scanning chunk ... ';
              $MAKELogFile = file_put_contents($AVLogFile, $txt.PHP_EOL, FILE_APPEND); }
            foreach ($defs as $virus) {
              $virus = explode(" \t", $virus[0]);
              $virus[1] = trim($virus[1]);
              if (isset($virus[1])) {
                if (stripos($data, $virus[1]) !== FALSE or stripos($file, $virus[1]) !== FALSE) { 
                  // File matches virus defs.
                  $txt = 'Infected: '.$file.' ('.$virus[0].', Data Match: '.$virus[1].')';
                  $MAKELogFile = file_put_contents($AVLogFile, 'OP-Act: '.$txt.PHP_EOL, FILE_APPEND);
                  $report .= '<p class="r">'.$txt.'</p>';
                  $infected++;
                  $clean = 0; } } } }
          if (!feof($handle)) {
            $txt = 'ERROR!!! PHPAV160, Unable to open '.$file.' on '.$Time.'!';
            $MAKELogFile = file_put_contents($AVLogFile, $txt.PHP_EOL, FILE_APPEND);
            $report .= '<p class="r">'.$txt.'</p>'; }
          fclose($handle); } 
          if (isset($virus[2])) {
            if (strpos(strtolower($data1), strtolower($virus[2])) !== FALSE) {
              // File matches virus defs.
              $txt = 'Infected: '.$file.' ('.$virus[0].', MD5 Hash Match: '.$virus[2].')';
              $MAKELogFile = file_put_contents($AVLogFile, 'OP-Act: '.$txt.PHP_EOL, FILE_APPEND);
              $report .= '<p class="r">'.$txt.'</p>';
              $infected++;
              $clean = 0; } }
            if (isset($virus[3])) {
              if (strpos(strtolower($data2), strtolower($virus[3])) !== FALSE) {
                // File matches virus defs.
                $txt = 'Infected: '.$file.' ('.$virus[0].', SHA256 Hash Match: '.$virus[3].')';
                $MAKELogFile = file_put_contents($AVLogFile, 'OP-Act: '.$txt.PHP_EOL, FILE_APPEND);
                $report .= '<p class="r">'.$txt.'</p>';
                $infected++;
                $clean = 0; } } 
            if (isset($virus[4])) {
              if (strpos(strtolower($data3), strtolower($virus[4])) !== FALSE) {
                // File matches virus defs.
                $txt = 'Infected: '.$file.' ('.$virus[0].', SHA1 Hash Match: '.$virus[4].')';
                $MAKELogFile = file_put_contents($AVLogFile, $txt.PHP_EOL, FILE_APPEND);
                $report .= '<p class="r">'.$txt.'</p>';
                $infected++;
                $clean = 0; } } } } }
      // / Scan files smaller than the memory limit by fitting the entire file into memory.
      if ($filesize < $memoryLimit && file_exists($file)) $data = file_get_contents($file); 
           $clean = 1;
          foreach ($defs as $virus) {
            $virus = explode("\t", $virus[0]);
            $virus[1] = trim($virus[1]);
            if (isset($virus[1])) { 
              if (stripos($data, $virus[1]) or stripos($virus[1], $data)) {
                 // File matches virus defs.
                $txt = 'Infected: '.$file.' ('.$virus[0].', Data Match: '.$virus[1].')';
                $MAKELogFile = file_put_contents($AVLogFile, 'OP-Act: '.$txt.PHP_EOL, FILE_APPEND);
                $report .= '<p class="r">'.$txt.'</p>';
                $infected++;
                $clean = 0; } } 
            if (isset($virus[2])) {
              if (strpos(strtolower($data1), strtolower($virus[2])) !== FALSE) {
                  // File matches virus defs.
                $txt = 'Infected: '.$file.' ('.$virus[0].', MD5 Hash Match: '.$virus[2].')';
                $MAKELogFile = file_put_contents($AVLogFile, 'OP-Act: '.$txt.PHP_EOL, FILE_APPEND);
                $report .= '<p class="r">'.$txt.'</p>';
                $infected++;
                $clean = 0; } }
              if (isset($virus[3])) {
                if (strpos(strtolower($data2), strtolower($virus[3])) !== FALSE) {
                  // File matches virus defs.
                  $txt = 'Infected: '.$file.' ('.$virus[0].', SHA256 Hash Match: '.$virus[3].')';
                  $MAKELogFile = file_put_contents($AVLogFile, 'OP-Act: '.$txt.PHP_EOL, FILE_APPEND);
                  $report .= '<p class="r">'.$txt.'</p>';
                  $infected++;
                  $clean = 0; } } 
              if (isset($virus[4])) {
                if (strpos(strtolower($data3), strtolower($virus[4])) !== FALSE) {
                  // File matches virus defs.
                  $txt = 'Infected: '.$file.' ('.$virus[0].', SHA1 Hash Match: '.$virus[4].')';
                  $MAKELogFile = file_put_contents($AVLogFile, $txt.PHP_EOL, FILE_APPEND);
                  $report .= '<p class="r">'.$txt.'</p>';
                  $infected++;
                  $clean = 0; } } }
           if (($debug) && ($clean)) $report .= '<p class="g">Clean: '.$file.'</p>'; 
  return $infected; }
// / -----------------------------------------------------------------------------------
?>