<?php
// Include Moodle config file. Adjust the path according to your Moodle installation.
require_once(__DIR__ . '/../../config.php'); // This path might need adjustment.

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

// User is logged in, continue with your logic

// Placeholder for connection details and data retrieval
// $connection = new external_connection();
// $data = $connection->get_data();
// echo json_encode($data);

$dbparams   =   array();

$dbparams['dbname']   =   get_config('rvc_survey','dbname');
$dbparams['user']     =   get_config('rvc_survey','dbuser');
$dbparams['pass']     =   get_config('rvc_survey','dbpass');
$dbparams['host']     =   get_config('rvc_survey','dbhost');
$dbparams['type']     =   get_config('rvc_survey','dbconnectiontype');

$extcon  =   new     external_connection($dbparams);

$tablename          =       get_config('rvc_survey','table');

$surveyuser         =       get_config('rvc_survey','surveyuser');

$surveys            =        $extcon->return_table_values($tablename,array($surveyuser=>array('='=>"'$USER->username'")));

$content            =   "";

foreach($surveys    as      $s)   {
    $closedate      =   date('d/m/Y',strtotime($s[$close]));
    $content        .=      "<p class='surveypara'><a href='".$this->rvc_url($s[$surveyid])."' class='titlelink'>".$s[$title]."</a><br />".get_string('closes','block_rvc_survey')." ".$closedate."</p>";
}

// Prepare the content as a JSON object and return it
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['survey_links_html' => $content]);