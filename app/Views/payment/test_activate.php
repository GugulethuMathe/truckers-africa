<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Subscription Activation - Truckers Africa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Test Subscription Activation
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Development tool to manually activate subscriptions
                </p>
                <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Development Only!</strong> This tool only works on localhost.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 space-y-6">
                <div class="rounded-md shadow-sm space-y-4">
                    <div>
                        <label for="merchant_id" class="block text-sm font-medium text-gray-700">
                            Merchant ID (optional)
                        </label>
                        <input id="merchant_id" name="merchant_id" type="number" 
                               class="mt-1 appearance-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                               placeholder="Leave empty to use logged-in merchant">
                    </div>
                </div>

                <div>
                    <button onclick="activateSubscription()" 
                            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Activate Subscription
                    </button>
                </div>

                <div id="result" class="hidden mt-4"></div>
            </div>

            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-gray-100 text-gray-500">Quick Actions</span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    <a href="<?= site_url('merchant/dashboard') ?>" 
                       class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        Dashboard
                    </a>
                    <a href="<?= site_url('merchant/subscription') ?>" 
                       class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        Subscription
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function activateSubscription() {
            const merchantId = document.getElementById('merchant_id').value;
            const url = merchantId 
                ? '<?= site_url('payment/test-activate') ?>/' + merchantId
                : '<?= site_url('payment/test-activate') ?>';

            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<div class="text-center"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div><p class="mt-2 text-sm text-gray-600">Activating subscription...</p></div>';
            resultDiv.classList.remove('hidden');

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = `
                            <div class="rounded-md bg-green-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-green-800">Success!</h3>
                                        <div class="mt-2 text-sm text-green-700">
                                            <p>${data.message}</p>
                                            <ul class="list-disc list-inside mt-2">
                                                <li>Subscription ID: ${data.subscription.id}</li>
                                                <li>Merchant ID: ${data.subscription.merchant_id}</li>
                                                <li>Old Status: ${data.subscription.old_status}</li>
                                                <li>New Status: ${data.subscription.new_status}</li>
                                            </ul>
                                        </div>
                                        <div class="mt-4">
                                            <a href="<?= site_url('merchant/dashboard') ?>" class="text-sm font-medium text-green-800 hover:text-green-700">
                                                Go to Dashboard →
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        resultDiv.innerHTML = `
                            <div class="rounded-md bg-red-50 p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-red-800">Error</h3>
                                        <div class="mt-2 text-sm text-red-700">
                                            <p>${data.message}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `
                        <div class="rounded-md bg-red-50 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">Request Failed</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <p>${error.message}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
        }
    </script>
</body>
</html>

