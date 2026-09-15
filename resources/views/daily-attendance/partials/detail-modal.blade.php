<div class="modal fade" id="attendanceDetailModal" tabindex="-1" aria-labelledby="attendanceDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="attendanceDetailModalLabel">Attendance Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body position-relative min-vh-50">
                <!-- Loader -->
                <div id="modalLoader" class="position-absolute top-50 start-50 translate-middle">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <!-- Error State -->
                <div id="modalError" class="d-none text-center py-5">
                    <i data-feather="alert-triangle" class="text-danger mb-3" style="width: 48px; height: 48px;"></i>
                    <h5 class="text-danger">Unable to load attendance details.</h5>
                    <p class="text-muted">Please try again or contact administration.</p>
                </div>

                <!-- Content -->
                <div id="modalContent" class="d-none">
                    <div class="row g-4">
                        <!-- Left Column -->
                        <div class="col-md-5">
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body">
                                    <h6 class="text-muted text-uppercase small mb-2">Employee Info</h6>
                                    <h5 id="detailEmpName" class="mb-0 fw-bold">--</h5>
                                    <div id="detailEmpId" class="text-muted small mb-3">--</div>
                                    
                                    <h6 class="text-muted text-uppercase small mb-1 mt-4">Date & Shift</h6>
                                    <div id="detailDate" class="fw-bold">--</div>
                                    <div id="detailShift" class="text-muted small">--</div>
                                </div>
                            </div>
                            
                            <h6 class="mb-3 fw-bold border-bottom pb-2 mt-4">Why this result?</h6>
                            <div id="explanationList" class="bg-light p-3 rounded">
                                <!-- Explanations will be populated here -->
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-7">
                            
                            <!-- Timeline -->
                            <h6 class="mb-3 fw-bold border-bottom pb-2">Visual Timeline</h6>
                            <ul id="timelineList" class="list-group list-group-flush mb-4 rounded border">
                                <!-- Timeline items here -->
                            </ul>

                            <!-- Raw Punches -->
                            <h6 class="mb-3 fw-bold border-bottom pb-2">Raw Punches (Read Only)</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Time</th>
                                            <th>Direction</th>
                                            <th>Method</th>
                                            <th>Source</th>
                                        </tr>
                                    </thead>
                                    <tbody id="rawPunchesBody">
                                        <!-- Raw punches here -->
                                    </tbody>
                                </table>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>