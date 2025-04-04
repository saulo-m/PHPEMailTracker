// Execute when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Load token history
    loadHistory();
});

// Get the base URL of the application
function getBaseUrl() {
    return window.location.href.replace(/\/index\.php$/, '').replace(/\/$/, '');
}

// Copy text to clipboard
function copyToClipboard(elementId, buttonElement) { // Added buttonElement parameter
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999); // For mobile devices

    // Check if Clipboard API is available (requires HTTPS or localhost)
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(element.value).then(() => {
            // Visual feedback on the specific button clicked
            const originalHtml = buttonElement.innerHTML;
            buttonElement.innerHTML = '<i class="fas fa-check"></i> Copied!';
            buttonElement.classList.add('btn-success');
            buttonElement.classList.remove('btn-outline-secondary');

            setTimeout(() => {
                buttonElement.innerHTML = originalHtml;
                buttonElement.classList.remove('btn-success');
                buttonElement.classList.add('btn-outline-secondary');
            }, 1500);
        }).catch(err => {
            console.error('Async: Could not copy text: ', err);
            alert('Failed to copy text.'); // Inform user if async fails
        });
    } else {
        // Fallback for non-secure contexts or older browsers
        try {
            const successful = document.execCommand('copy');
            if (successful) {
                // Visual feedback
                const originalHtml = buttonElement.innerHTML;
                buttonElement.innerHTML = '<i class="fas fa-check"></i> Copied!';
                buttonElement.classList.add('btn-success');
                buttonElement.classList.remove('btn-outline-secondary');

                setTimeout(() => {
                    buttonElement.innerHTML = originalHtml;
                    buttonElement.classList.remove('btn-success');
                    buttonElement.classList.add('btn-outline-secondary');
                }, 1500);
            } else {
                 console.error('Fallback: Unable to copy');
                 alert('Failed to copy text using fallback.');
            }
        } catch (err) {
            console.error('Fallback: Oops, unable to copy', err);
            alert('Failed to copy text using fallback.');
        }
    }
}

// Function to download the HTML file
function downloadHtmlFile() {
    const imageUrl = document.getElementById('imageUrl').value;
    const token = document.getElementById('tokenValueHidden').value; // Get token for filename

    if (!imageUrl || !token) {
        alert('Please generate a token first.');
        return;
    }

    // Construct the HTML content
    const htmlContent = `<!DOCTYPE html>
<html>
<head>
<title>Tracking Pixel</title>
</head>
<body>
<img src="${imageUrl}" alt="Tracking Pixel" width="1" height="1">
</body>
</html>`;

    // Create a Blob
    const blob = new Blob([htmlContent], { type: 'text/html' });

    // Create a link element
    const link = document.createElement('a');

    // Set the download attribute with a filename
    link.download = `tracking_pixel_${token.substring(0, 8)}.html`;

    // Create a URL for the Blob and set it as the href
    link.href = window.URL.createObjectURL(blob);

    // Append the link to the body (required for Firefox)
    document.body.appendChild(link);

    // Programmatically click the link to trigger the download
    link.click();

    // Clean up by removing the link and revoking the URL
    document.body.removeChild(link);
    window.URL.revokeObjectURL(link.href);
}

// Generate a new tracking token (Restored to correct location)
function getNewToken() {
    const baseUrl = getBaseUrl();

    // Show loading indicator
    document.getElementById('newtoken').innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generating...';
    document.getElementById('newtoken').disabled = true;

    fetch(baseUrl + "/api/NewToken")
        .then(response => response.json())
        .then(json => {
            // Hide loading indicator
            document.getElementById('newtoken').innerHTML = '<i class="fas fa-plus-circle me-1"></i> Generate New Token';
            document.getElementById('newtoken').disabled = false;

            if (json.error) {
                 showError(json.error);
                 return;
            }
            if (!json.token) {
                 showError("Received invalid response from server.");
                 return;
            }

            // Construct URLs and HTML tag
            const imageUrl = `${baseUrl}/image/?token=${json.token}`;
            // Use textContent to avoid HTML injection issues if displaying the tag directly
            // For input value, keep the actual tag
            const htmlTagValue = `<img src="${imageUrl}" width="1" height="1" alt="">`;

            // Update UI with token information
            document.getElementById("imageUrl").value = imageUrl;
            document.getElementById("htmlTag").value = htmlTagValue; // Set the value for the input
            document.getElementById("tokenValueDisplay").textContent = json.token; // Display token in <code>
            document.getElementById("tokenValueHidden").value = json.token; // Store token for potential future use

            // Show the result section
            document.getElementById("token-result").style.display = "block";

            // No need to reload history here, it loads on page load
            // setTimeout(loadHistory, 500); // Removed this line
        })
        .catch(error => {
            console.error("Error generating token:", error);
            document.getElementById('newtoken').innerHTML = '<i class="fas fa-plus-circle me-1"></i> Generate New Token';
            document.getElementById('newtoken').disabled = false;

            // Show error message
            showError("Failed to generate token. Please try again.");
        });
}

// Get information for a token
function getInfo() {
    const baseUrl = getBaseUrl();
    
    // Clear previous results
    document.getElementById("info0").innerText = "";
    document.getElementById("info2").innerText = "";
    document.getElementById("info3").innerText = "";
    document.getElementById("total-opens").innerText = "--";
    document.getElementById("first-opened").innerText = "--";
    document.getElementById("open-history").innerHTML = "";

    // Get token from the correct input field
    let key = document.getElementById("tokenLookup").value.trim();

    // Hide previous results and errors
    document.getElementById("results-section").style.display = "none";
    document.getElementById("error-display").style.display = "none";
    
    // Validate token format
    if (!key.match(/^[0-9a-f]{40}$/i)) {
        showError("Invalid token format. Token should be 40 hexadecimal characters.");
        return;
    }

    // Show loading indicator - Use the correct selector for the button within the lookup section
    const searchButton = document.querySelector('#lookup-section .input-group button');
    // Add a check to ensure the button was found before proceeding
    if (!searchButton) {
        console.error("Search button not found with selector '#lookup-section .input-group button'");
        showError("An unexpected error occurred in the UI.");
        return;
    }
    const originalButtonText = searchButton.innerHTML;
    searchButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
    searchButton.disabled = true;

    // Fetch data
    fetch(`${baseUrl}/api/GetInfo?token=${key}`)
        .then(response => response.json())
        .then(data => {
            // Reset button
            searchButton.innerHTML = originalButtonText;
            searchButton.disabled = false;
            
            if (data.Ok === false) {
                // Error
                showError(data.Error);
                return;
            }
            
            // Show results section
            document.getElementById("results-section").style.display = "block";
            
            // Display summary stats
            document.getElementById("total-opens").innerText = data.total_opens || "1";
            
            const firstOpenDate = new Date(data.all_records[data.all_records.length - 1].time);
            document.getElementById("first-opened").innerText = moment(firstOpenDate).format('MMM D, YYYY');
            
            // Display latest open information
            document.getElementById("info0").innerText = data.ipaddr;
            document.getElementById("info2").innerText = formatDeviceInfo(data.deviceinfo);
            document.getElementById("info3").innerText = formatGeoLocation(data.glocation);
            
            // Build timeline of all opens
            const historyContainer = document.getElementById("open-history");
            historyContainer.innerHTML = '';
            
            data.all_records.forEach((record, index) => {
                const date = new Date(record.time);
                const timelineItem = document.createElement('div');
                timelineItem.className = 'timeline-item';
                
                timelineItem.innerHTML = `
                    <div class="timeline-content">
                        <div class="timeline-date">${moment(date).format('MMM D, YYYY - h:mm:ss A')}</div>
                        <div>IP: ${record.ipaddr}</div>
                        <div>Device: ${formatDeviceInfo(record.deviceinfo).split('\n')[0]}</div>
                    </div>
                `;
                
                historyContainer.appendChild(timelineItem);
            });
            
            // No need to reload history here
            // setTimeout(loadHistory, 500); // Removed this line
        })
        .catch(error => {
            console.error("Error fetching data:", error);
            searchButton.innerHTML = originalButtonText;
            searchButton.disabled = false;
            showError("Failed to retrieve information. Please try again.");
        });
}

// Format device info for better display
function formatDeviceInfo(deviceInfo) {
    if (!deviceInfo) return "Unknown";
    
    // Remove "Name:" and other prefixes
    return deviceInfo
        .replace('Name: ', '')
        .replace('OS: ', '')
        .replace('Mobile: ', 'Mobile: ')
        .replace('Tablet: ', 'Tablet: ');
}

// Format geolocation for better display
function formatGeoLocation(geoLocation) {
    if (!geoLocation) return "Unknown";
    
    return geoLocation
        .replace('(Information provided by ip-api.com)', '')
        .trim();
}

// Show error message
function showError(message) {
    const errorDisplay = document.getElementById("error-display");
    errorDisplay.textContent = message;
    errorDisplay.style.display = "block";
}

// Load token history from API (Restored to only load history)
function loadHistory() {
    const baseUrl = getBaseUrl();
    const historyList = document.getElementById("history-list"); // Get reference outside fetch

    // Check if history list element exists before fetching
    if (!historyList) {
        // console.log("History list element not found, skipping history load.");
        return; // Exit if the element isn't on the page (e.g., if commented out)
    }

    fetch(`${baseUrl}/api/GetHistory`)
        .then(response => {
             if (!response.ok) {
                 throw new Error(`HTTP error! status: ${response.status}`);
             }
             return response.json();
         })
        .then(data => {
            const historyList = document.getElementById("history-list");
            
            if (data.history && data.history.length > 0) {
                historyList.innerHTML = '';
                
                data.history.forEach(item => {
                    const historyItem = document.createElement('a');
                    historyItem.href = "#";
                    historyItem.className = 'list-group-item list-group-item-action token-history-item';
                    historyItem.dataset.token = item.token;
                    
                    const created = new Date(item.created_at);
                    const lastViewed = new Date(item.last_viewed);
                    
                    historyItem.innerHTML = `
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">${item.token.substring(0, 10)}...</h6>
                            <small>${item.total_opens} opens</small>
                        </div>
                        <small>Created: ${moment(created).format('MMM D, YYYY')}</small>
                        <small class="d-block text-muted">Last viewed: ${moment(lastViewed).fromNow()}</small>
                    `;
                    
                    historyItem.addEventListener('click', function(e) {
                        e.preventDefault();
                        // Update the correct input field
                        document.getElementById("tokenLookup").value = this.dataset.token;
                        getInfo();
                        // No need to switch tabs anymore
                    });

                    historyList.appendChild(historyItem);
                });
            } else {
                historyList.innerHTML = `
                    <div class="text-center p-3 text-muted">
                        <small>No token history found</small>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error("Error loading history:", error);
            // Silently fail - history is not critical functionality
        });
}
