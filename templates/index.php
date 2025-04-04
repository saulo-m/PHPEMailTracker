<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Email Tracker</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom styles -->
    <link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="#">
                    <i class="fas fa-envelope-open-text me-2"></i>
                    Email Tracker
                </a>
                <div class="d-flex">
                    <a href="https://github.com/saulo-m/PHPEMailTracker" class="btn btn-outline-light btn-sm" target="_blank">
                        <i class="fab fa-github me-1"></i> GitHub
                    </a>
                </div>
            </div>
        </nav>

        <div class="row mt-4">
            <!-- Combined Create & Track Section -->
            <div class="col-md-6">
                <div class="card main-card">
                    <div class="card-body">
                        <!-- Create Section -->
                        <div id="create-section">
                            <h5 class="card-title">Create Email Tracking Pixel</h5>
                            <p class="card-text">Generate a unique tracking token and embed the provided image tag or URL in your email.</p>

                            <button class="btn btn-primary mb-3" id="newtoken" onclick="getNewToken()">
                                <i class="fas fa-plus-circle me-1"></i> Generate New Token
                            </button>

                            <div id="token-result" class="mt-3" style="display: none;">
                                <div class="alert alert-info">
                                    <div class="mb-3">
                                        <strong>HTML Image Tag:</strong>
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm" id="htmlTag" readonly>
                                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copyToClipboard('htmlTag', this)">
                                                <i class="fas fa-copy me-1"></i> Copy HTML
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Direct Image URL:</strong>
                                        <div class="input-group">
                                            <input type="text" class="form-control form-control-sm" id="imageUrl" readonly>
                                            <button class="btn btn-outline-secondary btn-sm" type="button" onclick="copyToClipboard('imageUrl', this)">
                                                <i class="fas fa-copy me-1"></i> Copy URL
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <strong>Token:</strong> <code id="tokenValueDisplay"></code>
                                        <input type="hidden" id="tokenValueHidden"> <!-- Hidden input to store token for JS -->
                                    </div>
                                    <!-- Download Button -->
                                    <div>
                                        <button class="btn btn-outline-success btn-sm" type="button" onclick="downloadHtmlFile()">
                                            <i class="fas fa-download me-1"></i> Download HTML for Outlook
                                        </button>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <p><small><strong>How to use:</strong> Copy the HTML tag and paste it into your email's HTML source, or use the direct URL if needed.</small></p>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Lookup Section -->
                        <div id="lookup-section">
                            <h5 class="card-title">Track Email Opens</h5>
                            <p class="card-text">Enter your tracking token to see the results.</p>

                            <div class="mb-3">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="tokenLookup" placeholder="Enter tracking token">
                                    <button class="btn btn-primary" onclick="getInfo()">
                                        <i class="fas fa-search me-1"></i> Track
                                    </button>
                                </div>
                            </div>

                            <!-- History section can remain if desired, or be removed for further simplification -->
                            <!-- <div class="mt-3">
                                <h6>Recent Tokens</h6>
                                <div id="history-list" class="list-group">
                                    <div class="text-center p-3 text-muted">
                                        <small>Your recent tokens will appear here</small>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results Section (Remains the same structure, but ensure IDs match JS) -->
            <div class="col-md-6">
                <div class="card main-card" id="results-section" style="display:none;">
                    <div class="card-header bg-primary text-white"> <!-- Changed header color for consistency -->
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-bar me-2"></i>
                            Tracking Results
                        </h5>
                    </div>
                    <div class="card-body">
                        <div id="summary" class="mb-4">
                            <div class="row text-center">
                                <div class="col-md-6">
                                    <div class="statistic">
                                        <h2 id="total-opens">--</h2>
                                        <p>Total Opens</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="statistic">
                                        <h2 id="first-opened">--</h2>
                                        <p>First Opened</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h6>Latest Open Information</h6>
                        <ul class="list-group mb-4">
                            <li class="list-group-item"><strong>IP Address:</strong> <span id="info0"></span></li>
                            <li class="list-group-item"><strong>Device:</strong> <span id="info2"></span></li>
                            <li class="list-group-item"><strong>Geo Location:</strong> <span id="info3"></span></li>
                        </ul>
                        
                        <h6>Open History</h6>
                        <div id="open-history" class="timeline">
                            <!-- Timeline entries will be inserted here -->
                        </div>
                    </div>
                </div>
                
                <div id="error-display" class="alert alert-danger mt-3" style="display:none"></div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Moment.js for date formatting -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <!-- Custom script -->
    <script src="assets/js/script.js"></script>
</body>
</html>
