define([], function() {
    return {
        init: function(ajaxUrl) {
            document.addEventListener('DOMContentLoaded', function() {
                fetch(ajaxUrl)
                    .then(response => response.json())
                    .then(data => {
                        if (data && data.survey_links_html) {
                            document.getElementById('student_survey_url').innerHTML = data.survey_links_html;
                        }
                    })
                    .catch(error => console.error("Failed to fetch survey links", error));
            });
        }
    };
});