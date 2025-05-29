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
        global $PAGE;

        if($this->content !== NULL) {
            return $this->content;
        } else {
            $this->content = new stdClass;
        }

        $ajaxUrl = new moodle_url('/blocks/rvc_survey/classes/external_ajax_request.php'); 
        $PAGE->requires->js_call_amd('block_rvc_survey/init_survey', 'init', array($ajaxUrl->out(false)));

        $content = get_string('introduction', 'block_rvc_survey') . " <br />";
        $content .= "<div class='stusurvey' id='student_survey_url'>";
        $content .= "<p class='surveypara'>Checking for surveys - please check back soon... </p>";
        $content .= "</div>";

        $this->content->text = $content;
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
