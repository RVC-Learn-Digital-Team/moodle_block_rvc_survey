<?php
// Include Moodle config file. Adjust the path according to your Moodle installation.
require_once(__DIR__ . '/../../../config.php');

global $COURSE,$CFG,$USER;

// Include the external_connection class
require_once($CFG->dirroot . '/blocks/rvc_survey/classes/external_connection.php');

// Check if the user is logged in and has a valid session
require_login(); // This will redirect the user to the login page if not logged in.

// Alternatively, to return a JSON response instead of redirecting to the login page:
if (!isloggedin() || isguestuser()) {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['error' => 'Not logged in']);
    exit;
}

function block_rvc_survey_get_url($surveyid) {
    //Update Link to oss RH
    return  "https://oss.rvc.ac.uk/Default.aspx?SurveyID={$surveyid}&redirect=L";
}

$dbparams   =   array();

$dbparams['dbname']   =   get_config('rvc_survey','dbname');
$dbparams['user']     =   get_config('rvc_survey','dbuser');
$dbparams['pass']     =   get_config('rvc_survey','dbpass');
$dbparams['host']     =   get_config('rvc_survey','dbhost');
$dbparams['type']     =   get_config('rvc_survey','dbconnectiontype');

$extcon  =   new     external_connection($dbparams);

if (!$extcon || !empty($extcon->errorlist)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['survey_links_html' => get_string("nodata",'block_rvc_survey')]);
    exit;
}

$tablename          =       get_config('rvc_survey','table');

$surveyuser         =       get_config('rvc_survey','surveyuser');

$surveys            =        $extcon->return_table_values($tablename,array($surveyuser=>array('='=>"'$USER->username'")));

$surveyid           =       get_config('rvc_survey','surveyid');
$title              =       get_config('rvc_survey','surveytitle');
$close              =       get_config('rvc_survey','surveyclose');

if (!empty($surveys)) {
    $content            =   "";
    foreach($surveys    as      $s)   {
        $closedate      =   date('d/m/Y',strtotime($s[$close]));
        $content        .=      "<p class='surveypara'><a href='".block_rvc_survey_get_url($s[$surveyid])."' class='titlelink'>".$s[$title]."</a><br />".get_string('closes','block_rvc_survey')." ".$closedate."</p>";
    }
} else {
    $content        =   get_string("nodata",'block_rvc_survey');
}

// Prepare the content as a JSON object and return it
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['survey_links_html' => $content]);