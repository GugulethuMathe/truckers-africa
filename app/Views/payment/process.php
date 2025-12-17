<div class="container mx-auto px-4 py-16 text-center">
    <h1 class="text-3xl font-bold text-white mb-4">Redirecting to Secure Payment</h1>
    <p class="text-slate-400 mb-8">You are being redirected to our secure payment partner, PayFast, to complete your subscription.</p>
    <div class="flex justify-center items-center text-blue-400">
        <i class="ri-loader-4-line text-6xl animate-spin"></i>
        <p class="ml-4 text-xl">Please wait...</p>
    </div>

    <!-- Debug: Show form data (remove in production) -->
    <?php if (ENVIRONMENT === 'development'): ?>
    <details class="text-left bg-gray-800 p-4 rounded mt-4 max-w-4xl mx-auto">
        <summary class="cursor-pointer text-yellow-400 font-semibold">🔍 Debug: PayFast Data (Click to expand)</summary>
        <pre class="text-xs text-gray-300 mt-2 overflow-auto"><?= print_r($payfast_data, true) ?></pre>
    </details>
    <?php endif; ?>

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
