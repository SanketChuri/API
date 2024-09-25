function makeRequest() {
    // Get the form element
    const form = document.getElementById('apiRequestForm');
    // Get the selected request method
    const method = form.requestType.value;
    // Get the API endpoint path from the form
    const apiPath = form.apiPath.value;
    // Construct the full URL by adding the base URL and the API path
    const url = 'https://student.csc.liv.ac.uk/~sgschuri/v2' + apiPath;
    // Get the request body data from the form
    const data = form.data.value; 

    // Prepare the options for the fetch request
    const options = {
        method: method,
        headers: {
            'Content-Type': 'application/json',
        },
    };
    // Only add body for non-GET requests
    if (method !== 'GET' && data) {
      try{
        // Attempt to parse the data as JSON to validate it
        JSON.parse(data);
        options.body = data;
      }catch(error){
        // Alert the user if the JSON is invalid
        alert('Invalid Json format');
        return;
      }
    }

    // Make the API request using fetch
    fetch(url, options)
    .then(response => {
        // Display the status code
        document.getElementById('statusCode').textContent = response.status;
        // Use text() to handle different response types
        return response.text();  
    })
    .then(text => {
        try {
            // Attempt to parse the response as JSON
            const json = JSON.parse(text);
            // Display formatted JSON in the response body
            document.getElementById('responseBody').textContent = JSON.stringify(json, null, 2);
        } catch (e) {
            // If parsing fails, display the raw text response
            document.getElementById('responseBody').textContent = text;
        }
    })
    .catch(error => {
        // Handle any errors that occur during the request
        document.getElementById('statusCode').textContent = 'Error';
        document.getElementById('responseBody').textContent = 'Error: ' + error.message;
    });
}

// Function to clear the output fields
function resetOutput() {
    document.getElementById('statusCode').textContent = '';
    document.getElementById('responseBody').textContent = '';
}