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

// Set execution time limit to prevent hanging (3 seconds max)
set_time_limit(3);

// Set socket timeout for database connections
ini_set('default_socket_timeout', 2);

$dbparams   =   array();

$dbparams['dbname']   =   get_config('rvc_survey','dbname');
$dbparams['user']     =   get_config('rvc_survey','dbuser');
$dbparams['pass']     =   get_config('rvc_survey','dbpass');
$dbparams['host']     =   get_config('rvc_survey','dbhost');
$dbparams['type']     =   get_config('rvc_survey','dbconnectiontype');

// Add timeout wrapper for the entire operation
$start_time = time();
$timeout = 2; // 2 seconds maximum for entire operation

try {
    // Check if we have required configuration
    if (empty($dbparams['host']) || empty($dbparams['type'])) {
        throw new Exception('Database configuration incomplete');
    }

    $extcon = new external_connection($dbparams);

    if (!$extcon || !empty($extcon->errorlist)) {
        $error_msg = !empty($extcon->errorlist) ? implode('; ', $extcon->errorlist) : 'Connection failed';
        error_log('RVC Survey block connection error: ' . $error_msg);
        throw new Exception('Database connection failed');
    }

    // Check timeout before proceeding with query
    if (time() - $start_time > $timeout) {
        throw new Exception('Operation timeout exceeded');
    }

    $tablename = get_config('rvc_survey','table');
    $surveyuser = get_config('rvc_survey','surveyuser');
    
    if (empty($tablename) || empty($surveyuser)) {
        throw new Exception('Survey configuration incomplete');
    }

    $surveys = $extcon->return_table_values($tablename,array($surveyuser=>array('='=>"'$USER->username'")));
    
    // Final timeout check
    if (time() - $start_time > $timeout) {
        throw new Exception('Query timeout exceeded');
    }

} catch (Exception $e) {
    error_log('RVC Survey block error: ' . $e->getMessage() . ' for user: ' . $USER->username);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['survey_links_html' => get_string("nodata",'block_rvc_survey')]);
    exit;
}

try {
    $surveyid = get_config('rvc_survey','surveyid');
    $title = get_config('rvc_survey','surveytitle');
    $close = get_config('rvc_survey','surveyclose');

    if (empty($surveyid) || empty($title) || empty($close)) {
        throw new Exception('Survey display configuration incomplete');
    }

    $content = "";
    if (!empty($surveys)) {
        foreach($surveys as $s) {
            if (!isset($s[$close]) || !isset($s[$surveyid]) || !isset($s[$title])) {
                continue; // Skip malformed survey data
            }
            $closedate = date('d/m/Y', strtotime($s[$close]));
            $content .= "<p class='surveypara'><a href='" . block_rvc_survey_get_url($s[$surveyid]) . "' class='titlelink'>" . htmlspecialchars($s[$title]) . "</a><br />" . get_string('closes','block_rvc_survey') . " " . $closedate . "</p>";
        }
    }
    
    if (empty($content)) {
        $content = get_string("nodata",'block_rvc_survey');
    }

    // Prepare the content as a JSON object and return it
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['survey_links_html' => $content]);
    
} catch (Exception $e) {
    error_log('RVC Survey block content generation error: ' . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['survey_links_html' => get_string("nodata",'block_rvc_survey')]);
}