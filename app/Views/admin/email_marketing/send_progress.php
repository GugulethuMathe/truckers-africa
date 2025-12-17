<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-lg shadow-md p-8">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-800 mb-2">Sending Campaign</h1>
                <p class="text-gray-600"><?= esc($campaign['campaign_name']) ?></p>
            </div>

            <!-- Progress Bar -->
            <div class="mb-6">
                <div class="flex justify-between text-sm text-gray-600 mb-2">
                    <span>Progress</span>
                    <span id="progress-text">0%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-4">
                    <div id="progress-bar" class="bg-blue-600 h-4 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-green-50 rounded-lg p-4 text-center">
                    <div id="sent-count" class="text-2xl font-bold text-green-600">0</div>
                    <div class="text-sm text-gray-600">Sent</div>
                </div>
                <div class="bg-red-50 rounded-lg p-4 text-center">
                    <div id="failed-count" class="text-2xl font-bold text-red-600">0</div>
                    <div class="text-sm text-gray-600">Failed</div>
                </div>
                <div class="bg-gray-50 rounded-lg p-4 text-center">
                    <div id="remaining-count" class="text-2xl font-bold text-gray-600"><?= $pending_count ?></div>
                    <div class="text-sm text-gray-600">Remaining</div>
                </div>
            </div>

            <!-- Status Message -->
            <div id="status-message" class="text-center mb-6">
                <div class="flex items-center justify-center gap-2 text-blue-600">
                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Sending emails...</span>
                </div>
            </div>

            <!-- Actions -->
            <div id="action-buttons" class="text-center hidden">
                <a href="<?= site_url('admin/email-marketing/campaigns') ?>" class="inline-block bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">
                    Back to Campaigns
                </a>
            </div>
        </div>
    </div>
</div>

<script>
const campaignId = <?= $campaign['id'] ?>;
const totalEmails = <?= $pending_count ?>;
let processedEmails = 0;

function updateProgress() {
    const percentage = totalEmails > 0 ? Math.round((processedEmails / totalEmails) * 100) : 0;
    document.getElementById('progress-bar').style.width = percentage + '%';
    document.getElementById('progress-text').textContent = percentage + '%';
}

function sendBatch() {
    fetch('<?= site_url('admin/email-marketing/campaigns/send-batch/') ?>' + campaignId, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('sent-count').textContent = data.total_sent || 0;
            document.getElementById('failed-count').textContent = data.total_failed || 0;
            document.getElementById('remaining-count').textContent = data.remaining || 0;
            
            processedEmails = (data.total_sent || 0) + (data.total_failed || 0);
            updateProgress();

            if (data.completed) {
                // Campaign finished
                document.getElementById('status-message').innerHTML = `
                    <div class="flex items-center justify-center gap-2 text-green-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="font-semibold">${data.message}</span>
                    </div>
                `;
                document.getElementById('action-buttons').classList.remove('hidden');
            } else {
                // Continue sending
                setTimeout(sendBatch, 500); // Small delay between batches
            }
        } else {
            document.getElementById('status-message').innerHTML = `
                <div class="text-red-600">
                    <span class="font-semibold">Error: ${data.error || 'Unknown error occurred'}</span>
                </div>
            `;
            document.getElementById('action-buttons').classList.remove('hidden');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('status-message').innerHTML = `
            <div class="text-red-600">
                <span class="font-semibold">Network error. Please try again.</span>
            </div>
        `;
        document.getElementById('action-buttons').classList.remove('hidden');
    });
}

// Start sending when page loads
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(sendBatch, 1000);
});
</script>

