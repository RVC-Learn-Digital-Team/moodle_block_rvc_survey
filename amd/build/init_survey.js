// File: /blocks/rvc_survey/amd/src/init_survey.js
define(['jquery', 'core/ajax'], function($, Ajax) {
    return {
        init: function(ajaxUrl) {
            $(document).ready(function() {
                $.ajax({
                    url: ajaxUrl,
                    headers: { 'Accept' : 'application/json'},
                    type: 'GET',
                    success: function(response) {
                        if(response && response.survey_links_html) {
                            $('#student_survey_url').html(response.survey_links_html);
                        }
                    },
                    error: function(xhr, status, error) {
                      console.error('AJAX Error:', status, error);
                      console.log('Response:', xhr.responseText);
                  }
                });
            });
        }
    };
});
