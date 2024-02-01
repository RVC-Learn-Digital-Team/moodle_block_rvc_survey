M.block_block_rvc_survey = {};

M.block_block_rvc_survey.init = function(Y, ajaxUrl) {
    Y.on('domready', function() {
        Y.io(ajaxUrl, {
            method: 'GET',
            on: {
                success: function(id, response) {
                    try {
                        var data = Y.JSON.parse(response.responseText);
                        // Assuming data contains {'survey_links_html': '<html goes here>'}
                        if(data.survey_links_html) {
                            // Update the content of the div with id "student_survey_url"
                            Y.one('#student_survey_url').setHTML(data.survey_links_html);
                        }
                    } catch(e) {
                        console.error("Parsing error:", e);
                    }
                },
                failure: function() {
                    console.error("Failed to fetch survey links");
                }
            }
        });
    });
};