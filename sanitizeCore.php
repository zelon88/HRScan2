<?php
// / -----------------------------------------------------------------------------------
// / This file is intended to be included in PHP files that require safe sanitization of 
// / supported POST and GET inputs. 

// / This file also dictates the basic HRConvert2 API. (NOT INLCLUDING APP-SPECIFIC API's)

// / If you're looking to add code to sanitize additional 
// / POST or GET inputs, you should put it in this file and then require this file into
// / your code project, or app.
// / -----------------------------------------------------------------------------------



// / -----------------------------------------------------------------------------------
// / Developers add your code between the following comment lines.....



$your_code_here = null;



// / Developers DO NOT add your code below this comment line.
// / -----------------------------------------------------------------------------------



// / -----------------------------------------------------------------------------------
set_time_limit(0);
// / OFFICIAL HRSCAN2 SANITIZED API INPUTS

// / The following blocks of code each represent a distnct HRScan2 API input.
// / To use the official API, satisfy the corresponding POST or GET variables below.
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / Sanitize the Token GET variable.
if (isset($_POST['Token1'])) {
  $Token1 = str_replace('//', '/', str_replace('..', '', str_replace(str_split('|~#[](){};:$!#^&%@>*<"\''), '', $_POST['Token1']))); }
if (isset($_POST['Token2'])) {
  $Token2 = str_replace('//', '/', str_replace('..', '', str_replace(str_split('|~#[](){};:$!#^&%@>*<"\''), '', $_POST['Token2']))); }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
// / Sanitize the noGui GET variable to disable the descriptive header text.
// / Good for usage in a small iframe.
if (isset($_POST['noGui'])) {
  $_GET = str_replace('//', '/', str_replace('..', '', str_replace(str_split('|~#[](){};:$!#^&%@>*<"\''), '', $_GET['noGui']))); }
// / -----------------------------------------------------------------------------------

// / -----------------------------------------------------------------------------------
set_time_limit(0);
// / -----------------------------------------------------------------------------------
?>