<?php 
include ('header.php');
?>
  <body>
    <?php 
    if (!isset($_GET['noGui'])) { ?>
    <div id="header-text" style="max-width:1000px; margin-left:auto; margin-right:auto; text-align:center;">
      <h1>HRScan2</h1>
      <h3>Online Virus & Malware Scanner</h3>
      <hr />
    </div>
    <div id="main" align="center">
      <div id="overview" style="max-width:1000px; text-align:left; margin:25px;">
      	<p id="info" style="display:block;">HRScan2 is an open-source web-app that scans files for virus or malware infection without tracking users across the net or infringing on your intellectual property.</p>
        <button id="more-info-button" class="info-button" onclick="toggle_visibility('more-info'); toggle_visibility('more-info-button'); toggle_visibility('less-info-button');" 
         style="text-align:center; display:block; margin-left:auto; margin-right:auto;"><i>More Info ...</i></button>
        <button id="less-info-button" class="info-button" onclick="toggle_visibility('more-info'); toggle_visibility('more-info-button'); toggle_visibility('less-info-button');" 
         style="text-align:center; display:none; margin-left:auto; margin-right:auto;"><i>Less Info ...</i></button>
        <div id="more-info" style="display:none;">
          <hr />
          <h3>About HRScan2:</h3>
          <p style="margin-left:15px;">HRScan2 uses a combination of ClamAV and built-in heuristic techniques to safely detect dangerous or infected files supplied by users.</p>
          <p style="margin-left:15px;">All user-supplied data is erased automatically, so you don't need to worry about forfeiting your personal information or property while using our services.</p>
          <h3>About ClamAV:</h3>
          <p style="margin-left:15px;"><a href="https://www.clamav.net">ClamAV</a> is an open-source virus scanner that's respected by server admins all over the world for it's dependibility and performance.</p>
          <h3>About PHP-AV:</h3>
          <p style="margin-left:15px;"><a href="https://github.com/zelon88/HRCloud2/tree/master/Applications/PHP-AV">PHP-AV</a> is an open-source virus scanner and vulnerability checker built specificially for web servers. The original code dates back to 2006, but our 2018 redesign enables modern functionality.</p>
          <br>
        </div>
        <hr />
      </div>
      <?php } ?>
      <div id="call-to-action1" style="max-width:1000px; text-align:center;">
        <p>Select files by clicking, tapping, or dropping files into the box below.</p>
      </div>
      <div id="dropzone" style="max-height:2000px; max-width:1000px; margin:25px;">
        <form action="scanCore.php" class="dropzone" id="filesToUpload" name="filesToUpload" method="post" enctype="multipart/form-data">
        <input type="hidden" id="token1" name="Token1" value="<?php echo $Token1; ?>">
        <input type="hidden" id="token2" name="Token2" value="<?php echo $Token2; ?>">
        </form>
      </div>
    </div>
    <div align="center">
      <form action="scanCore.php?showFiles=1" method="post">
        <input type="hidden" id="token1" name="Token1" value="<?php echo $Token1; ?>">
        <input type="hidden" id="token2" name="Token2" value="<?php echo $Token2; ?>">
        <input type="submit" id="continue-button" class="info-button" value="Continue ...">
      </form>
      </form>
      
    </div>
    <br />
    <hr />
    <?php
    include ('footer.php');
    ?>