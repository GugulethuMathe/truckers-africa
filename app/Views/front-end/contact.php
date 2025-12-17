<style>
    button.w-full.bg-secondary.text-gray-900.font-bold.py-3.px-6.rounded-lg.hover\:bg-opacity-90.transition.flex.items-center.justify-center.gap-2 {
    color: white;
}
</style>
<section class="min-h-screen py-16 px-4">
    <div class="container mx-auto max-w-6xl">
        <!-- Page Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-white mb-4">Contact Truckers Africa</h1>
            <p class="text-slate-400 max-w-2xl mx-auto">We're here to assist you. Whether you're a service provider, transporter, driver, or partner looking to collaborate, feel free to reach out to us through any of the channels below.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            <!-- Contact Information -->
            <div class="space-y-8">
                <!-- General Enquiries -->
                <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
                    <div class="flex items-start gap-4">
                        <div class="bg-secondary/20 p-3 rounded-lg">
                            <i class="ri-mail-line text-2xl text-secondary"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-white mb-2">General Enquiries</h3>
                            <p class="text-slate-400 mb-2">Have questions about the platform, listings, or how Truckers Africa works?</p>
                            <a href="mailto:admin@truckersafrica.com" class="text-secondary hover:underline">admin@truckersafrica.com</a>
                        </div>
                    </div>
                </div>

                <!-- Service Provider Support -->
                <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
                    <div class="flex items-start gap-4">
                        <div class="bg-blue-500/20 p-3 rounded-lg">
                            <i class="ri-customer-service-2-line text-2xl text-blue-400"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-white mb-2">Service Provider Support</h3>
                            <p class="text-slate-400 mb-2">For help with onboarding, listings, or subscription plans:</p>
                            <a href="mailto:support@truckersafrica.com" class="text-blue-400 hover:underline">support@truckersafrica.com</a>
                        </div>
                    </div>
                </div>

                <!-- WhatsApp Support -->
                <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
                    <div class="flex items-start gap-4">
                        <div class="bg-green-500/20 p-3 rounded-lg">
                            <i class="ri-whatsapp-line text-2xl text-green-400"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-white mb-2">WhatsApp Support</h3>
                            <p class="text-slate-400 mb-2">Get quick assistance via WhatsApp.</p>
                            <a href="https://wa.me/27687781223" target="_blank" class="text-green-400 hover:underline">+27 68 778 1223</a>
                        </div>
                    </div>
                </div>

                <!-- Head Office -->
                <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
                    <div class="flex items-start gap-4">
                        <div class="bg-purple-500/20 p-3 rounded-lg">
                            <i class="ri-building-line text-2xl text-purple-400"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-white mb-2">Head Office</h3>
                            <p class="text-slate-400">Johannesburg, South Africa</p>
                        </div>
                    </div>
                </div>

                <!-- Operating Hours -->
                <div class="bg-gray-800/50 rounded-xl p-6 border border-gray-700">
                    <div class="flex items-start gap-4">
                        <div class="bg-yellow-500/20 p-3 rounded-lg">
                            <i class="ri-time-line text-2xl text-yellow-400"></i>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-white mb-2">Operating Hours</h3>
                            <div class="text-slate-400 space-y-1">
                                <p><span class="text-white">Monday to Friday:</span> 08:00 – 17:00</p>
                                <p><span class="text-white">Saturday:</span> 09:00 – 13:00</p>
                                <p><span class="text-white">Sundays & Public Holidays:</span> Closed</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="bg-gray-800/50 rounded-xl p-8 border border-gray-700 h-fit">
                <h2 class="text-2xl font-bold text-white mb-6">Send us a Message</h2>
                
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="bg-green-500/20 border border-green-500 text-green-400 px-4 py-3 rounded-lg mb-6">
                        <?= session()->getFlashdata('success') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('error')): ?>
                    <div class="bg-red-500/20 border border-red-500 text-red-400 px-4 py-3 rounded-lg mb-6">
                        <?= session()->getFlashdata('error') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="bg-red-500/20 border border-red-500 text-red-400 px-4 py-3 rounded-lg mb-6">
                        <ul class="list-disc list-inside">
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= site_url('contact-us') ?>" method="POST" class="space-y-6">
                    <?= csrf_field() ?>
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-300 mb-2">Name <span class="text-red-400">*</span></label>
                        <input type="text" id="name" name="name" required
                            class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary"
                            placeholder="Your full name" value="<?= old('name') ?>">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Email <span class="text-red-400">*</span></label>
                        <input type="email" id="email" name="email" required
                            class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary"
                            placeholder="your.email@example.com" value="<?= old('email') ?>">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-slate-300 mb-2">Phone Number</label>
                        <input type="tel" id="phone" name="phone"
                            class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary"
                            placeholder="+27 XX XXX XXXX" value="<?= old('phone') ?>">
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-medium text-slate-300 mb-2">Message <span class="text-red-400">*</span></label>
                        <textarea id="message" name="message" rows="5" required
                            class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-secondary focus:ring-1 focus:ring-secondary resize-none"
                            placeholder="How can we help you?"><?= old('message') ?></textarea>
                    </div>

                    <button type="submit" class="w-full bg-secondary text-gray-900 font-bold py-3 px-6 rounded-lg hover:bg-opacity-90 transition flex items-center justify-center gap-2">
                        <i class="ri-send-plane-fill"></i>
                        Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

