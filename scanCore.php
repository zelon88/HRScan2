<!DOCTYPE HTML>
<?php
// / -----------------------------------------------------------------------------------
// / APPLICATION INFORMATION ...
// / HRScan2, Copyright on 2/21/2016 by Justin Grimes, www.github.com/zelon88
// / 
// / LICENSE INFORMATION ...
// / This project is protected by the GNU GPLv3 Open-Source license.
// / 
// / APPLICATION DESCRIPTION ...
// / This application is designed to provide a web-interface for scanning files 
// / for viruses on a server for users of any web browser without authentication. 
// / 
// / HARDWARE REQUIREMENTS ... 
// / This application requires at least a Raspberry Pi Model B+ or greater.
// / This application will run on just about any x86 or x64 computer.
// / 
// / DEPENDENCY REQUIREMENTS ... 
// / This application requires Debian Linux (w/3rd Party audio license), 
// / Apache 2.4, PHP 7.0+, JScript, WordPress & mySql (optional) & ClamAV.
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The following code will load required HRScan2 files.
if (!file_exists('config.php')) die ('ERROR!!! HRScan226, Cannot process the HRScan2 Configuration file (config.php)!'.PHP_EOL); 
else require_once ('config.php');
if (!file_exists('sanitizeCore.php')) die ('ERROR!!! HRScan233, Cannot process the HRScan2 Sanitize Core file (sanitizeCore.php)!'.PHP_EOL); 
else require_once ('sanitizeCore.php'); 
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The folloiwing code attempts to detect the users IP so it can be used as a unique identifier for the session.
  // / If it is not unique we will adjust it later.
if (!empty($_SERVER['HTTP_CLIENT_IP'])) $IP = htmlentities(str_replace(str_split('~#[](){};:$!#^&%@>*<"\''), '', $_SERVER['HTTP_CLIENT_IP']), ENT_QUOTES, 'UTF-8'); 
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) $IP = htmlentities(str_replace(str_split('~#[](){};:$!#^&%@>*<"\''), '', $_SERVER['HTTP_X_FORWARDED_FOR']), ENT_QUOTES, 'UTF-8'); 
else $IP = htmlentities(str_replace(str_split('~#[](){};:$!#^&%@>*<"\''), '', $_SERVER['REMOTE_ADDR']), ENT_QUOTES, 'UTF-8'); 
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The following code sets an echo variable that adjusts printed URL's to https when SSL is enabled.
if (!empty($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] == 443) $URLEcho = 's'; 
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The following code sets or validates a Token so it can be used as a unique identifier for the session.
if (!isset($Token1) or strlen($Token1) < 19) $Token1 = hash('ripemd160', rand(0, 1000000000).rand(0, 1000000000)); 
if (isset($Token2)) if ($Token2 !== hash('ripemd160', $Token1.$Salts1.$Salts2.$Salts3.$Salts4.$Salts5.$Salts6)) die('ERROR!!! HRScan263, Authentication error!!!'); 
if (!isset($Token2)) $Token2 = hash('ripemd160', $Token1.$Salts1.$Salts2.$Salts3.$Salts4.$Salts5.$Salts6); 
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The following code sets the global variables for the session.
$HRScanVersion = 'v1.3';
$versions = 'PHP-AV App v3.8 | Virus Definition v4.7, 8/19/2018';
$Date = date("m_d_y");
$Time = date("F j, Y, g:i a"); 
$JanitorDeleteIndex = FALSE;
$Current_URL = "http$URLEcho://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$SesHash = substr(hash('ripemd160', $Date.$Salts1.$Salts2.$Salts3.$Salts4.$Salts5.$Salts6), -12);
$SesHash2 = substr(hash('ripemd160', $SesHash.$Token1.$Date.$IP.$Salts1.$Salts2.$Salts3.$Salts4.$Salts5.$Salts6), -12);
$SesHash3 = $SesHash.'/'.$SesHash2;
$SesHash4 = hash('ripemd160', $Salts6.$Salts5.$Salts4.$Salts3.$Salts2.$Salts1);
$ScanDir0 = $ScanLoc.'/'.$SesHash;
$ScanDir = $ScanDir0.'/'.$SesHash2;
$ScanTemp = $InstLoc.'/DATA';
$ScanTempDir0 = $ScanTemp.'/'.$SesHash;
$ScanTempDir = $ScanTempDir0.'/'.$SesHash2;
$LogInc = '0';
$ScanGuiCounter1 = $ConsolidateLogs = 0;
$LogFile = $LogDir.'/HRScan2_'.$LogInc.'_'.$Date.'_'.substr($SesHash4, -7).'_'.substr($SesHash, -7).'.txt';
$ClamLogFileName = 'ClamScan_'.$Date.'_'.substr($SesHash4, -7).'_'.substr($SesHash, -7).'.txt';
$ClamLogFile = $ScanDir.'/'.$ClamLogFileName;
$ClamLogTempFile = $ScanTempDir.'/'.$ClamLogFileName;
$PHPAVLogFileName = 'PHPAVScan_'.$Date.'_'.substr($SesHash4, -7).'_'.substr($SesHash, -7).'.txt';
$PHPAVLogFile = $ScanDir.'/'.$PHPAVLogFileName;
$PHPAVLogTempFile = $ScanTempDir.'/'.$PHPAVLogFileName;
$ConsolidatedLogFileName = 'ScanAll_'.$Date.'_'.substr($SesHash4, -7).'_'.substr($SesHash, -7).'.txt';
$ConsolidatedLogFile = $ScanDir.'/'.$ConsolidatedLogFileName;
$ConsolidatedLogTempFile = $ScanTempDir.'/'.$ConsolidatedLogFileName;
$defaultLogDir = $InstLoc.'/Logs';
$defaultLogSize = '1048576';
$defaultApps = array('index.html', '.', '..', '..');
$RequiredDirs = array($LogDir, $defaultLogDir, $ScanDir0, $ScanDir, $ScanTemp, $ScanTempDir0, $ScanTempDir);
$RequiredIndexes = array($LogDir, $defaultLogDir, $ScanTemp, $ScanTempDir0, $ScanTempDir);
$ReservedFiles = array('index.php', 'index.html');
$fileArray1 = array();
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / GUI specific resources.
function getExtension($pathToFile) {
  return pathinfo($pathToFile, PATHINFO_EXTENSION); }
function getFilesize($File) {
  $Size = filesize($File);
  if ($Size < 1024) $Size=$Size." Bytes"; 
  elseif (($Size < 1048576) && ($Size > 1023)) $Size = round($Size / 1024, 1)." KB";
  elseif (($Size < 1073741824) && ($Size > 1048575)) $Size = round($Size / 1048576, 1)." MB";
  else ($Size = round($Size/1073741824, 1)." GB");
  return ($Size); }
function symlinkmtime($symlinkPath) {
  $stat = lstat($symlinkPath);
  return isset($stat['mtime']) ? $stat['mtime'] : null; }
function fileTime($filePath) {
  if (file_exists($filePath)) {
    $stat = filemtime($filePath);
    return ($stat); } }
function is_dir_empty($dir) { 
  if (is_dir($dir)) { 
    $contents = scandir($dir);
    foreach ($contents as $content) { 
      if ($content == '.' or $content == '..') return FALSE; } }
  return TRUE; }
function cleanFiles($path) { 
  global $ScanLoc, $ScanTemp, $InstLoc, $defaultApps;
  if (is_dir($path)) { 
    $i = scandir($path);
    foreach($i as $f) { 
      if (is_file($path.'/'.$f) && !in_array(basename($path.'/'.$f), $defaultApps)) @unlink($path.'/'.$f);  
      if (is_dir($path.'/'.$f) && !in_array(basename($path.'/'.$f), $defaultApps) && is_dir_empty($path)) @rmdir($path.'/'.$f);
      if (is_dir($path.'/'.$f) && !in_array(basename($path.'/'.$f), $defaultApps) && !is_dir_empty($path)) cleanFiles($path.'/'.$f); } 
    if ($path !== $ScanLoc && $path !== $ScanTemp) @rmdir($path); } }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The following code creates a logfile if one does not exist.
if (!is_numeric($MaxLogSize)) $MaxLogSize = $defaultLogSize;
if (!is_dir($LogDir)) mkdir($LogDir);
if (!is_dir($LogDir)) $LogDir = $defaultLogDir;
if (!is_dir($LogDir)) die('ERROR!!! HRScan278, The specified $LogDir does not exist at '.$LogDir.' on '.$Time.'.');
if (!file_exists($LogDir.'/index.html')) copy('index.html', $LogDir.'/index.html');
while (file_exists($LogFile) && round((filesize($LogFile) / $MaxLogSize), 2) > $MaxLogSize) { 
  $LogInc++; 
  $LogFile = $LogDir.'/HRScan2_'.$LogInc.'.txt.'; 
  $MAKELogFile = file_put_contents($LogFile, 'OP-Act: Logfile created on '.$Time.'.'.PHP_EOL, FILE_APPEND); }
if (!file_exists($LogFile)) $MAKELogFile = file_put_contents($LogFile, 'OP-Act: Logfile created on '.$Time.'.'.PHP_EOL, FILE_APPEND);
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The following code creates required data directoreis if they do not exist.
if (!is_dir($ScanLoc)) {
  $txt = ('ERROR!!! HRScan278, The specified ScanLoc does not exist at '.$ScanLoc.' on '.$Time.'.');
  $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND); }
foreach ($RequiredDirs as $RequiredDir) { 
  if (!is_dir($RequiredDir)) { 
    mkdir($RequiredDir); 
    $txt = ('OP-Act: Created a directory at '.$RequiredDir.' on '.$Time.'.');
    $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND); } }
foreach ($RequiredIndexes as $RequiredIndex) { 
  copy ('index.html', $RequiredIndex.'/index.html'); }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The following code will clean up old files.
if (file_exists($ScanDir)) {
  $DFiles = scandir($ScanDir);
  $now = time();
  foreach ($DFiles as $DFile) {
    if ($DFile == 'index.html' or in_array($DFile, $defaultApps)) continue;
    if (($now - fileTime($ScanDir.'/'.$DFile)) > ($Delete_Threshold * 60)) { // Time to keep files.
      if (is_file($DFile)) {
        chmod ($DFile, 0755);
        if (file_exists($ScanDir.'/'.$DFile)) unlink($ScanDir.'/'.$DFile); 
        $txt = ('OP-Act: Cleaned '.$ScanDir.'/'.$DFile.' on '.$Time.'.');
        $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND); }
      if (is_dir($DFile)) {
        $CleanDir = $ScanDir.'/'.$DFile;
        chmod ($CleanDir, 0755);
        cleanFiles($CleanDir); } 
      cleanFiles($ScanDir); } } }
if (file_exists($ScanTemp)) {
  $DFiles = scandir($ScanTemp);
  $now = time();
  foreach ($DFiles as $DFile) {
    if ($DFile == 'index.html' or in_array($DFile, $defaultApps)) continue;
    if (($now - fileTime($ScanTemp.'/'.$DFile)) > ($Delete_Threshold * 60)) { // Time to keep files.
      if (is_file($DFile)) {
        chmod ($DFile, 0755);
        if (file_exists($ScanTemp.'/'.$DFile)) unlink($ScanTemp.'/'.$DFile); 
        $txt = ('OP-Act: Cleaned '.$ScanTemp.'/'.$DFile.' on '.$Time.'.');
        $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND); }
      if (is_dir($DFile)) {
        $CleanDir = $ScanTemp.'/'.$DFile;
        chmod ($CleanDir, 0755);
        cleanFiles($CleanDir); } 
      cleanFiles($ScanTemp); } } }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The following code is performed when a user initiates a file upload.
if(!empty($_FILES)) {
  $txt = ('OP-Act: Initiated Uploader on '.$Time.'.');
  $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND);
  if (!is_array($_FILES['file']['name'])) $_FILES['file']['name'] = array($_FILES['file']['name']); 
  foreach ($_FILES['file']['name'] as $key=>$file) {
    if (in_array($file, $ReservedFiles) or $file == '.' or $file == '..' or $file == 'index.html') continue;     
    $file = htmlentities(str_replace(str_split('\\/[](){};:$!#^&%@>*<'), '', $file), ENT_QUOTES, 'UTF-8');
    foreach ($DangerousFiles as $DangerousFile) if (strpos($file, $DangerousFile) == TRUE) continue 2;  
    $F0 = pathinfo($file, PATHINFO_EXTENSION);
    if (in_array($F0, $DangerousFiles)) { 
      $txt = ("ERROR!!! HRScan2103, Unsupported file format, $F0 on $Time.");
      $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND);
      echo nl2br($txt."\n"); 
      continue; }
    $F2 = pathinfo($file, PATHINFO_BASENAME);
    $F3 = str_replace(' ', '_', str_replace('//', '/', $ScanDir.'/'.$F2));
    if($file == "") {
      $txt = ("ERROR!!! HRScan2160, No file specified on $Time.");
      $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND);
      echo nl2br($txt."\n"); 
      die(); }
    $COPY_TEMP = copy($_FILES['file']['tmp_name'], $F3);
    if (file_exists($F3) or $COPY_TEMP === FALSE) {
      $txt = ('OP-Act: '."Uploaded $file to $F3 on $Time".'.');
      echo nl2br ($txt."\n"); }
    if (!file_exists($F3)) {
      $txt = ('ERROR!!!HRScan2230, Could not upload '.$file.' to '.$F3.' on '.$Time.'!');
      echo nl2br ($txt."\n"); }
    $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND);
    chmod($F3, 0755); } 
  // / Free un-needed memory.
  $txt = $file = $F0 = $F2 = $F3 = $Upload = $MAKELogFile = null;
  unset ($txt, $file, $F0, $F2, $F3, $Upload, $MAKELogFile); }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The following code is performed when a user selects to scan ALL the files they've uploaded with either ClamAV or PHP-AV
if (isset($_POST['scanAll'])) { 
  $ConsolidateLogs = 1;
  if (file_exists($ConsolidatedLogFile)) @unlink($ConsolidatedLogFile);
  if ($_POST['ClamScanAll'] == 'clamScanAll') $_POST['clamScanButton'] = 1;
  if ($_POST['PHPAVScanAll'] == 'phpavScanAll') $_POST['phpavScanButton'] = 1; }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The following code is performed when a user selects to Clamscan the files they've uploaded with ClamAV.
if (isset($_POST["clamScanButton"])) {
  $_POST['clamScanButton'] = str_replace('//', '/', str_replace('///', '/', str_replace(str_split('[]{};:$!#^&%@>*<'), '', $_POST['clamScanButton'])));
  $txt = ('OP-Act: Initiated ClamScanner on '.$Time.'.');
  $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND);
  $MAKELogFile = file_put_contents($ClamLogFile, $txt.PHP_EOL, FILE_APPEND);
  if (!is_array($_POST['filesToScan'])) $_POST['filesToScan'] = array($_POST['filesToScan']);
  if (isset($_POST["filesToScan"])) {
    foreach (($_POST['filesToScan']) as $File) {
      if (in_array($File, $defaultApps) or in_array($File, $ReservedFiles) or $File == '.' or $File == '..' or $File == '') continue;
      $txt = 'OP-Act: Scanning '.$File.'.';
      $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND);
      $MAKELogFile = file_put_contents($ClamLogFile, $txt.PHP_EOL, FILE_APPEND);
      if (!file_exists($ScanDir.'/'.$File)) { 
        $txt = 'ERROR!!! HRScan2244, '.$File.' does not exist on '.$Time.'!';
        $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND);
        $MAKELogFile = file_put_contents($ClamLogFile, $txt.PHP_EOL, FILE_APPEND);
        continue; }
      shell_exec(str_replace('  ', ' ', str_replace('   ', ' ', 'clamscan -r '.$Thorough.' '.$ScanDir.'/'.$File.' | grep FOUND >> '.$ClamLogFile)));
      $ClamLogFileDATA = @file_get_contents($ClamLogFile);
      if (strpos($ClamLogFileDATA, 'FOUND') == FALSE) { 
        $MAKELogFile = file_put_contents($LogFile, 'OP-Act: No infection detected in '.$File.' on '.$Time.'.'.PHP_EOL, FILE_APPEND);
        $MAKELogFile = file_put_contents($ClamLogFile, 'OP-Act: No infection detected in '.$File.' on '.$Time.'.'.PHP_EOL, FILE_APPEND); }
      if (strpos($ClamLogFileDATA, 'Virus Detected') == TRUE or strpos($ClamLogFileDATA, 'FOUND') == TRUE) { 
        $MAKELogFile = file_put_contents($LogFile, 'WARNING!!! HRScan2338, Potential infection found in '.$File.' on '.$Time.'.'.PHP_EOL, FILE_APPEND);
        $MAKELogFile = file_put_contents($ClamLogFile, 'WARNING!!! HRScan2338, Potential infection found in '.$File.' on '.$Time.'.'.PHP_EOL, FILE_APPEND); } } } 
  $MAKELogFile = file_put_contents($ClamLogFile, PHP_EOL, FILE_APPEND); }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The following code is performed when a user selects to scan the files they've uploaded with PHP-AV.
if (isset($_POST['phpavScanButton'])) { 
  $DefFile = 'Resources/virus.def';
  $_POST['phpavScanButton'] = str_replace('//', '/', str_replace('///', '/', str_replace(str_split('[]{};:$!#^&%@>*<'), '', $_POST['phpavScanButton'])));
  $txt = ('OP-Act: Initiated PHPAVScanner on '.$Time.'.');
  $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND);
  $MAKELogFile = file_put_contents($PHPAVLogFile, $txt.PHP_EOL, FILE_APPEND); 
  require('PHP-AV-Lib.php');
  $defs = load_defs($DefFile, $CONFIG['debug']);
  $defData = hash_file('sha256', $DefFile);
  if (!is_array($_POST['filesToScan'])) $_POST['filesToScan'] = array($_POST['filesToScan']);
  foreach ($_POST['filesToScan'] as $File) {
    if (in_array($File, $defaultApps) or in_array($File, $ReservedFiles) or $File == '.' or $File == '..' or $File == '') continue;
    $txt = ('OP-Act: Scanning file '.$File.' on '.$Time.'.');
    $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND);
    $MAKELogFile = file_put_contents($PHPAVLogFile, $txt.PHP_EOL, FILE_APPEND); 
    if (!file_exists($ScanDir.'/'.$File)) { 
      $txt = 'ERROR!!! HRScan2276, '.$File.' does not exist on '.$Time.'!';
      $MAKELogFile = file_put_contents($LogFile, $txt.PHP_EOL, FILE_APPEND);
      $MAKELogFile = file_put_contents($PHPAVLogFile, $txt.PHP_EOL, FILE_APPEND);
      continue; }
    $infected = virus_check($ScanDir.'/'.$File, $defs, $CONFIG['debug'], $defData, $AVLogFile); 
    if ($infected === 0) {
      $MAKELogFile = file_put_contents($LogFile, 'OP-Act: No infection detected in '.$File.' on '.$Time.'.'.PHP_EOL, FILE_APPEND); 
      $MAKELogFile = file_put_contents($PHPAVLogFile, 'OP-Act: No infection detected in '.$File.' on '.$Time.'.'.PHP_EOL, FILE_APPEND); }
    if ($infected > 0) {
      $MAKELogFile = file_put_contents($LogFile, 'WARNING!!! HRScan2351, Potential infection found in '.$File.' on '.$Time.'.'.PHP_EOL, FILE_APPEND);     
      $MAKELogFile = file_put_contents($PHPAVLogFile, 'WARNING!!! HRScan2351, Potential infection found in '.$File.' on '.$Time.'.'.PHP_EOL, FILE_APPEND); }
    $dirCount = $fileCount = $infected = 0; } 
  $MAKELogFile = file_put_contents($PHPAVLogFile, PHP_EOL, FILE_APPEND); } 
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The following code consolididates the logfiles from ClamAV and PHP-AV when the ScanAll button is selected.
if ($ConsolidateLogs === 1) { 
  $spacer = '----------';
  $txt1 = 'OP-Act: User selected to scan all files on '.$Time.'.';
  $MAKEConsolidatedLogFile = file_put_contents($ConsolidatedLogFile, $txt1.PHP_EOL.$spacer.PHP_EOL, FILE_APPEND);
  if (file_exists($ClamLogFile)) { 
    $ClamLogDATA = file_get_contents($ClamLogFile); 
    $MAKEConsolidatedLogFile = file_put_contents($ConsolidatedLogFile, $ClamLogDATA.PHP_EOL.$spacer.PHP_EOL, FILE_APPEND); 
    @unlink($ClamLogFile); } 
  if (file_exists($PHPAVLogFile)) { 
    $PHPAVLogDATA = file_get_contents($PHPAVLogFile); 
    $MAKEConsolidatedLogFile = file_put_contents($ConsolidatedLogFile, $PHPAVLogDATA.PHP_EOL.$spacer.PHP_EOL, FILE_APPEND); } 
    @unlink($PHPAVLogFile); }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The following code consolidates and copies any logfiles that were generated.
if (file_exists($ClamLogFile)) $COPYClamLog = copy($ClamLogFile, $ClamLogTempFile); 
if (file_exists($PHPAVLogFile)) $COPYPHPAVLogFile = copy($PHPAVLogFile, $PHPAVLogTempFile);
if (file_exists($ConsolidatedLogFile)) $COPYConsolidatedLogFile = copy($ConsolidatedLogFile, $ConsolidatedLogTempFile); 
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / The following code loads the GUI.
if (isset($_GET['showFiles']) or isset($_POST['showFiles'])) require_once('scanGui2.php'); 
if (!isset($_GET['showFiles'])) require_once('scanGui1.php'); 
// / -----------------------------------------------------------------------------------
?>