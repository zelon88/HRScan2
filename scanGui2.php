<?php
$Alert = 'Cannot scan this file at this time! Please try again later.';
$Alert1 = 'Cannot scan these files at this time! Please try again later.';
$Files = array_values(array_diff(scandir($ScanDir), array('..', '.')));
$fileCount = count($Files);
$fcPlural1 = 's';
$fcPlural2 = 's are';
if (!is_numeric($fileCount)) $fileCount = 'an unknown number of';
if ($fileCount == 1) {
  $fcPlural1 = '';
  $fcPlural2 = ' is'; }
include ('header.php');
?>
  <body>
    <script type="text/javascript" src="Resources/jquery-3.3.1.min.js"></script>
    <div id="header-text" style="max-width:1000px; margin-left:auto; margin-right:auto; text-align:center;">
      <h1>HRScan2</h1>
      <hr />
      <h3>File Scan Options</h3>
      <p>You have uploaded <?php echo $fileCount; ?> valid file<?php echo $fcPlural1; ?> to HRScan2.</p> 
      <p>Your file<?php echo $fcPlural2; ?> now ready to scan using the options below.</p>
    </div>

    <div id="scanAll" name="scanAll" style="max-width:1000px; margin-left:auto; margin-right:auto; text-align:center;">
      <button id="backButton" name="backButton" style="width:50px;" class="info-button" onclick="window.history.back();">&#x2190;</button>
      <button id="refreshButton" name="refreshButton" style="width:50px;" class="info-button" onclick="javascript:location.reload(true);">&#x21BB;</button>
      <br /> <br /> 
      <button id="scanMoreOptionsButton" name="scanMoreOptionsButton" class="info-button" onclick="toggle_visibility_inline('scanAllOptions'); 
       toggle_visibility_inline('scanMoreOptionsButton'); toggle_visibility_inline('scanLessOptionsButton');">Scan All - Options</button> 
      <button id="scanLessOptionsButton" name="scanLessOptionsButton" class="info-button" style="display:none;" onclick="toggle_visibility_inline('scanAllOptions'); 
       toggle_visibility_inline('scanMoreOptionsButton'); toggle_visibility_inline('scanLessOptionsButton');">Hide Options</button> 
      <br />
      <div align="center">
        <p><img id='loadingCommandDiv' name='loadingCommandDiv' src='Resources/pacman.gif' style="max-width:64px; max-height:64px; display:none;"/></p>
      </div>
      <div id="scanAllOptions" name="scanAllOptions" align="center" style="display:none;">
        <hr />
        <p>Scan w/ClamAV <input type="checkbox" id="clamScanAll" value="clamScanAll" name="clamScan" checked></p>
        <p>Scan w/PHP-AV <input type="checkbox" id="phpavScanAll" value="phpavScanAll" name="phpavScan" checked></p>
        <p><input type="submit" id="scanAllButton" name="scanAllButton" class="info-button" value='Scan All' onclick="toggle_visibility_inline('loadingCommandDiv');"></p>
        <script type="text/javascript">
        $(document).ready(function () {
          $('#scanAllButton').click(function() {
            var scanfiles = <?php echo json_encode($Files); ?>;
            $.ajax({
              type: "POST",
              url: 'scanCore.php',
              data: {
                Token1:'<?php echo $Token1; ?>',
                Token2:'<?php echo $Token2; ?>',
                scanAll:'1',
                filesToScan: scanfiles,
                ClamScanAll: (function() { 
                  if($("input#clamScanAll").is(":checked")) {
                    return $("input#clamScanAll").val(); } })(),
                PHPAVScanAll: (function() { 
                  if($("input#phpavScanAll").is(":checked")) {
                    return $("input#phpavScanAll").val(); } })() },
                success: function(ReturnData) {
                  toggle_visibility('loadingCommandDiv');
                  window.location.href = "<?php echo 'DATA/'.$SesHash3.'/'.$ConsolidatedLogFileName; ?>";
                },
                error: function(ReturnData) {
                  alert("<?php echo $Alert1; ?>"); }
            });
          });
        });
        </script>
        <hr />
      </div>
    </div>
    <br />
    <div style="max-width:1000px; margin-left:auto; margin-right:auto;">
      <hr />
      <?php
      foreach ($Files as $File) {
        if (in_array($Files, $ReservedFiles) or in_array($Files, $defaultApps)) continue;
        $extension = getExtension($ScanDir.'/'.$File);
        $FileNoExt = str_replace($extension, '', $File);
        $ScanGuiCounter1++;
      ?>
      <div id="file<?php echo $ScanGuiCounter1; ?>" name="<?php echo $ScanGuiCounter1; ?>">
        <p href=""><strong><?php echo $ScanGuiCounter1; ?>.</strong> <u><?php echo $File; ?></u></p>
        <img id="clamscanButton<?php echo $ScanGuiCounter1; ?>" name="clamscanButton<?php echo $ScanGuiCounter1; ?>" src="Resources/clamav.png" alt="Scan '<?php echo $File; ?>' with ClamAV." title="Scan '<?php echo $File; ?>' with ClamAV." onclick="toggle_visibility('loadingCommandDiv');"/>
        <img id="phpavscanButton<?php echo $ScanGuiCounter1; ?>" name="phpavscanButton<?php echo $ScanGuiCounter1; ?>" src="Resources/phpav.png" alt="Scan '<?php echo $File; ?>' with PHP-AV." title="Scan '<?php echo $File; ?>' with PHP-AV." onclick="toggle_visibility('loadingCommandDiv');"/>
        <hr />
      
          <script type="text/javascript">
          $(document).ready(function () {
            $('#clamscanButton<?php echo $ScanGuiCounter1; ?>').click(function() {
              $.ajax({
                type: "POST",
                url: 'scanCore.php',
                data: {
                  Token1:'<?php echo $Token1; ?>',
                  Token2:'<?php echo $Token2; ?>',
                  clamScanButton:'<?php echo $File; ?>', 
                  filesToScan: '<?php echo $File; ?>'},
                  success: function(ReturnData) {
                    $.ajax({
                    type: 'POST',
                    url: 'scanCore.php',
                    data: { 
                      Token1:'<?php echo $Token1; ?>',
                      Token2:'<?php echo $Token2; ?>'},
                    success: function(returnFile) {
                      toggle_visibility('loadingCommandDiv');
                      window.location.href = "<?php echo 'DATA/'.$SesHash3.'/'.$ClamLogFileName; ?>"; }
                    }); },
                  error: function(ReturnData) {
                    alert("<?php echo $Alert; ?>"); }
              });
            });
            $('#phpavscanButton<?php echo $ScanGuiCounter1; ?>').click(function() {
              $.ajax({
                type: "POST",
                url: 'scanCore.php',
                data: {
                  Token1:'<?php echo $Token1; ?>',
                  Token2:'<?php echo $Token2; ?>',
                  phpavScanButton:'<?php echo $File; ?>', 
                  filesToScan: '<?php echo $File; ?>' },
                  success: function(ReturnData) {
                    $.ajax({
                    type: 'POST',
                    url: 'scanCore.php',
                    data: { 
                      Token1:'<?php echo $Token1; ?>',
                      Token2:'<?php echo $Token2; ?>'},
                    success: function(returnFile) {
                      toggle_visibility('loadingCommandDiv');
                      window.location.href = "<?php echo 'DATA/'.$SesHash3.'/'.$PHPAVLogFileName; ?>"; }
                    }); },
                  error: function(ReturnData) {
                    alert("<?php echo $Alert; ?>"); }
              });
            });            
          });
          </script>

      <?php } ?>
      </div>
    
    <?php
    include ('footer.php');
    ?>