<?php
/**
 * Created by JetBrains PhpStorm.
 * User: nigel.daley
 * Date: 5/8/14
 * Time: 4:45 PM
 * To change this template use File | Settings | File Templates.
 */

class block_rvc_survey extends block_base    {

    function init() {
        $this->title = get_string('pluginname', 'block_rvc_survey');
    }

    function get_content()  {

        global $COURSE,$CFG,$USER;

        if($this->content !== NULL) {
            return $this->content;
        }

        global $PAGE, $CFG;
        
        $ajaxUrl = new moodle_url('/blocks/rvc_survey/classes/external_ajax_request.php'); 
        // Use js_call_amd to include and initialize your AMD module with the AJAX URL
        $PAGE->requires->js_call_amd('block_rvc_survey/init_survey', 'init', array($ajaxUrl->out(false)));

        $this->content->text    =   "";

        require_once($CFG->dirroot.'/blocks/rvc_survey/classes/external_connection.php');

        $dbparams   =   array();

        $dbparams['dbname']     =   get_config('rvc_survey','dbname');
        $dbparams['user']     =   get_config('rvc_survey','dbuser');
        $dbparams['pass']     =   get_config('rvc_survey','dbpass');
        $dbparams['host']     =   get_config('rvc_survey','dbhost');
        $dbparams['type']     =   get_config('rvc_survey','dbconnectiontype');

        $extcon  =   new     external_connection($dbparams);

        $tablename          =       get_config('rvc_survey','table');

        $surveyuser         =       get_config('rvc_survey','surveyuser');

        $surveys            =        $extcon->return_table_values($tablename,array($surveyuser=>array('='=>"'$USER->username'")));

        $content            =   "";

        if  (!empty($surveys))  {

            $surveyid           =       get_config('rvc_survey','surveyid');
            $title              =       get_config('rvc_survey','surveytitle');
            $close              =       get_config('rvc_survey','surveyclose');
            $content            =       get_string('introduction', 'block_rvc_survey')." <br />";
            $content            .=      "<div class='stusurvey' id='student_survey_url'>";
            $content            .=      "<p class='surveypara'>Checking for surveys - please check back soon... </p>";
            /* Uncomment this to replace ajax call with database request on page load
            foreach($surveys    as      $s)   {
                $closedate      =   date('d/m/Y',strtotime($s[$close]));
                $content        .=      "<p class='surveypara'><a href='".$this->rvc_url($s[$surveyid])."' class='titlelink'>".$s[$title]."</a><br />".get_string('closes','block_rvc_survey')." ".$closedate."</p>";
            }
            */
            $content            .=       "</div>";

        } else {
            $content        =   get_string("nodata",'block_rvc_survey');
        }

        $this->content->text    =   $content;

        return $this->content;
    }


    /**
     * Prevent the user from having more than one instance of the block on each
     * course.
     *
     * @return bool false
     */
    function instance_allow_multiple() {
        return false;
    }

    function has_config() {
        return true;
    }


    function rvc_url($surveyid) {
        //Update Link to oss RH
        return  "https://oss.rvc.ac.uk/Default.aspx?SurveyID={$surveyid}&redirect=L";
    }
}
