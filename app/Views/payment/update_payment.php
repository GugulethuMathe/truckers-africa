<div class="container mx-auto px-4 py-16 text-center">
    <h1 class="text-3xl font-bold text-white mb-4">Updating Payment Method</h1>
    <p class="text-slate-400 mb-4">You are being redirected to PayFast to securely update your payment details.</p>

    <div class="bg-green-900/30 border border-green-500/50 rounded-lg p-4 max-w-md mx-auto mb-8">
        <div class="flex items-center justify-center text-green-400">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="font-semibold">No charge will be made</p>
        </div>
        <p class="text-slate-300 text-sm mt-2">Your subscription will continue without interruption.</p>
    </div>

    <div class="flex justify-center items-center text-blue-400">
        <i class="ri-loader-4-line text-6xl animate-spin"></i>
        <p class="ml-4 text-xl">Please wait...</p>
    </div>

    <form action="<?= esc($payfast_url) ?>" method="post" id="payfast-form">
        <?php foreach ($payfast_data as $key => $value): ?>
            <input type="hidden" name="<?= esc($key) ?>" value="<?= esc($value) ?>">
        <?php endforeach; ?>
    </form>

    <script type="text/javascript">
        // Automatically submit the form when the page loads
        window.onload = function(){
            document.getElementById('payfast-form').submit();
        };
    </script>
</div>
