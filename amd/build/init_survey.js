define([], function() {
    return {
        init: function(ajaxUrl) {
            console.log('AMD module initialized with URL:', ajaxUrl);
            console.log('Document ready state:', document.readyState);
            
            /**
             * Function to load survey data
             */
            function loadSurveyData() {
                console.log('loadSurveyData function called');
                
                const surveyElement = document.getElementById('student_survey_url');
                
                // Check if the element exists before proceeding
                if (!surveyElement) {
                    console.warn('Survey element with ID "student_survey_url" not found');
                    console.log('Available elements with IDs:', Array.from(document.querySelectorAll('[id]')).map(el => el.id));
                    return;
                }

                console.log('Survey element found, starting fetch request');
                
                // Show loading state
                surveyElement.innerHTML = '<p class="surveypara">Loading surveys...</p>';

                console.log('Sending AJAX request to:', ajaxUrl);
                
                fetch(ajaxUrl, {
                    method: 'GET',
                    credentials: 'same-origin', // Important for Moodle session handling
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    console.log('Response received with status:', response.status);
                    console.log('Response headers:', response.headers);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('JSON data received:', data);
                    
                    if (data && data.survey_links_html) {
                        console.log('Survey HTML content found, updating element');
                        surveyElement.innerHTML = data.survey_links_html;
                    } else {
                        console.log('No survey HTML content in response');
                        surveyElement.innerHTML = '<p class="surveypara">No survey data available.</p>';
                    }
                })
                .catch(error => {
                    console.error("Failed to fetch survey links", error);
                    console.log('Error details:', {
                        message: error.message,
                        stack: error.stack,
                        url: ajaxUrl
                    });
                    surveyElement.innerHTML = '<p class="surveypara">Error loading surveys. Please try again later.</p>';
                });
            }

            // Check if DOM is already loaded
            if (document.readyState === 'loading') {
                console.log('DOM is still loading, adding event listener');
                // DOM is still loading, wait for it
                document.addEventListener('DOMContentLoaded', function() {
                    console.log('DOMContentLoaded event fired');
                    loadSurveyData();
                });
            } else {
                console.log('DOM is already loaded, executing immediately');
                // DOM is already loaded, execute immediately
                loadSurveyData();
            }
        }
    };
});