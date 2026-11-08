@extends('installer.layout', ['step' => 5])

@section('title', 'Installation Progress')

@section('content')
    <div class="card-header-installer">
        <h2>System installation</h2>
        <p>Running deployment stages sequentially within discrete atomic transactions.</p>
    </div>

    <div class="card-body-installer">
        <!-- Progress Bar & Percentage -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span id="currentStageLabel" class="small text-secondary font-mono">
                    <i class="fa-solid fa-arrows-rotate fa-spin me-2 text-dark"></i> Initializing deployment pipeline...
                </span>
                <span id="progressPercentage" class="sys-tag font-mono">0%</span>
            </div>
            <div class="progress" style="height: 6px; border-radius: 4px; background-color: var(--surface-inset);">
                <div id="progressBar" class="progress-bar bg-dark" role="progressbar" style="width: 0%; transition: width 0.3s ease;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
        </div>

        <!-- Failure Alert Banner -->
        <div id="errorBanner" class="alert alert-danger d-none mb-4 py-2 px-3 rounded-2 small" role="alert">
            <div class="d-flex align-items-start gap-2">
                <i class="fa-solid fa-triangle-exclamation text-danger mt-1"></i>
                <div class="flex-grow-1">
                    <div class="fw-semibold mb-1" id="errorTitle">Deployment interrupted</div>
                    <div class="mb-2 font-mono" id="errorMessage" style="font-size: 0.8rem;"></div>
                    <div class="d-flex gap-2">
                        <button type="button" id="retryBtn" class="btn btn-sm btn-zen">
                            <i class="fa-solid fa-rotate-right me-1" style="font-size: 0.76rem;"></i> Retry stage
                        </button>
                        <a href="{{ route('installer.database') }}" class="btn btn-sm btn-zen-outline">
                            Review database
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stages Pipeline Checklist -->
        <div class="card border mb-3 rounded-2">
            <div class="card-header bg-light py-2 px-3 fw-medium small text-secondary d-flex justify-content-between align-items-center font-mono" style="font-size: 0.76rem;">
                <span>EXECUTION PIPELINE</span>
                <span>[8 STAGES]</span>
            </div>
            <ul class="list-group list-group-flush" id="stagesList" style="font-size: 0.84rem;">
                @foreach($stages as $stageKey => $stageInfo)
                    @php
                        $isPreCompleted = in_array($stageKey, $completedStages);
                    @endphp
                    <li class="list-group-item d-flex align-items-center justify-content-between py-2 px-3" id="stage-item-{{ $stageKey }}">
                        <div class="d-flex align-items-center gap-3">
                            <span class="stage-icon" id="stage-icon-{{ $stageKey }}" style="width: 18px; text-align: center;">
                                @if($isPreCompleted)
                                    <i class="fa-solid fa-check text-success"></i>
                                @else
                                    <i class="fa-regular fa-circle text-muted" style="font-size: 0.72rem;"></i>
                                @endif
                            </span>
                            <div>
                                <span class="fw-medium text-dark stage-title" id="stage-title-{{ $stageKey }}">{{ $stageInfo['name'] }}</span>
                                <span class="text-muted ms-2 small d-none d-md-inline">&bull; {{ $stageInfo['description'] }}</span>
                            </div>
                        </div>
                        <span class="font-mono text-muted" style="font-size: 0.72rem;" id="stage-badge-{{ $stageKey }}">
                            {{ $isPreCompleted ? '[DONE]' : '[PENDING]' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Terminal Diagnostics Console -->
        <div class="card border rounded-2">
            <div class="card-header bg-dark text-white py-2 px-3 d-flex justify-content-between align-items-center font-mono" style="font-size: 0.76rem;">
                <span><i class="fa-solid fa-terminal me-2 text-secondary"></i>TELEMETRY CONSOLE</span>
                <span class="text-secondary">[STREAM: ACTIVE]</span>
            </div>
            <div class="card-body bg-dark text-light p-3 font-mono" style="max-height: 160px; overflow-y: auto; font-size: 0.76rem;" id="consoleBody">
                <div class="text-secondary">[BOOT] Session initialized. Starting dispatch sequence...</div>
            </div>
        </div>
    </div>

    <div class="card-footer-installer">
        <div class="text-muted small font-mono" style="font-size: 0.76rem;">
            [TX: ATOMIC] Transaction boundary verified
        </div>
        <div id="footerAction">
            <span class="text-muted small font-mono">Process running...</span>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const stages = @json(array_keys($stages));
        const stagesMetadata = @json($stages);
        const completedStages = @json($completedStages);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const progressBar = document.getElementById('progressBar');
        const progressPercentage = document.getElementById('progressPercentage');
        const currentStageLabel = document.getElementById('currentStageLabel');
        const consoleBody = document.getElementById('consoleBody');
        const errorBanner = document.getElementById('errorBanner');
        const errorMessage = document.getElementById('errorMessage');
        const retryBtn = document.getElementById('retryBtn');
        const footerAction = document.getElementById('footerAction');

        let currentStageIndex = 0;

        function logConsole(text, status = 'info') {
            const now = new Date().toLocaleTimeString();
            const line = document.createElement('div');
            let color = '#9ca3af';
            if (status === 'success') color = '#34d399';
            if (status === 'error') color = '#f87171';
            if (status === 'warn') color = '#fbbf24';

            line.innerHTML = `<span style="color: #6b7280;">[${now}]</span> <span style="color: ${color};">${text}</span>`;
            consoleBody.appendChild(line);
            consoleBody.scrollTop = consoleBody.scrollHeight;
        }

        function updateProgress(pct) {
            const clamped = Math.min(100, Math.max(0, pct));
            progressBar.style.width = clamped + '%';
            progressBar.setAttribute('aria-valuenow', clamped);
            progressPercentage.textContent = clamped + '%';
        }

        function setStageState(stageKey, state) {
            const icon = document.getElementById('stage-icon-' + stageKey);
            const badge = document.getElementById('stage-badge-' + stageKey);
            const title = document.getElementById('stage-title-' + stageKey);

            if (state === 'running') {
                icon.innerHTML = '<i class="fa-solid fa-arrows-rotate fa-spin text-dark"></i>';
                badge.className = 'font-mono text-dark fw-semibold';
                badge.textContent = '[RUNNING]';
                title.classList.add('fw-semibold');
            } else if (state === 'completed') {
                icon.innerHTML = '<i class="fa-solid fa-check text-success"></i>';
                badge.className = 'font-mono text-success';
                badge.textContent = '[DONE]';
            } else if (state === 'failed') {
                icon.innerHTML = '<i class="fa-solid fa-xmark text-danger"></i>';
                badge.className = 'font-mono text-danger fw-semibold';
                badge.textContent = '[FAIL]';
            }
        }

        function runNextStage() {
            if (currentStageIndex >= stages.length) {
                updateProgress(100);
                currentStageLabel.innerHTML = '<i class="fa-solid fa-check text-success me-2"></i> Deployment complete';
                logConsole('Pipeline finished successfully. Transferring to summary...', 'success');
                footerAction.innerHTML = '<a href="{{ route('installer.complete') }}" class="btn btn-zen btn-sm">View summary</a>';

                setTimeout(function () {
                    window.location.href = "{{ route('installer.complete') }}";
                }, 1000);
                return;
            }

            const stageKey = stages[currentStageIndex];
            const meta = stagesMetadata[stageKey];

            if (completedStages.includes(stageKey)) {
                setStageState(stageKey, 'completed');
                updateProgress(meta.weight);
                logConsole(`Stage '${meta.name}' previously executed. Skipping.`, 'info');
                currentStageIndex++;
                runNextStage();
                return;
            }

            setStageState(stageKey, 'running');
            currentStageLabel.innerHTML = `<i class="fa-solid fa-arrows-rotate fa-spin me-2 text-dark"></i> ${meta.name}...`;
            logConsole(`Executing: ${meta.name}`, 'info');

            fetch("{{ route('installer.install.process') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ stage: stageKey })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    setStageState(stageKey, 'completed');
                    updateProgress(meta.weight);
                    logConsole(`OK: ${data.message}`, 'success');

                    currentStageIndex++;
                    setTimeout(runNextStage, 250);
                } else {
                    handleStageFailure(stageKey, meta, data.message || 'Execution failed.');
                }
            })
            .catch(err => {
                handleStageFailure(stageKey, meta, 'Network or server exception: ' + err.message);
            });
        }

        function handleStageFailure(stageKey, meta, message) {
            setStageState(stageKey, 'failed');
            currentStageLabel.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-danger me-2"></i> Failed: ${meta.name}`;
            logConsole(`ERROR in [${stageKey}]: ${message}`, 'error');

            errorBanner.classList.remove('d-none');
            errorMessage.textContent = message;

            retryBtn.onclick = function() {
                errorBanner.classList.add('d-none');
                logConsole(`Retrying [${stageKey}]...`, 'warn');
                runNextStage();
            };
        }

        setTimeout(runNextStage, 400);
    });
</script>
@endsection
