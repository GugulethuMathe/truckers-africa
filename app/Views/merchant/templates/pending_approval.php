<?= view('merchant/templates/header', ['page_title' => $page_title]) ?>

<div class="flex items-center justify-center h-full bg-gray-100">
    <div class="text-center p-12 bg-white rounded-lg shadow-lg max-w-lg mx-auto">
        <svg class="mx-auto h-16 w-16 text-yellow-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h1 class="mt-4 text-2xl font-bold text-gray-900">Account Pending Approval</h1>
        <p class="mt-2 text-gray-600">
            Thank you for submitting your profile. Our team is currently reviewing your information.
            You will be notified via email once your account has been approved. This usually takes 24-48 hours.
        </p>
        <div class="mt-6">
            <a href="<?= site_url('logout') ?>" class="text-brand-blue hover:underline font-medium">Logout</a>
        </div>
    </div>
</div>

<?= view('merchant/templates/footer') ?>