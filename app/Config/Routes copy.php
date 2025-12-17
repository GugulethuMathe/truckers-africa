<?php

// Routes for Registration
$routes->get('/', 'Home::index');
$routes->get('registration', 'Home::index');
$routes->post('registration/register', 'Registration::register');
$routes->get('success', 'Registration::success');
$routes->get('registration/payment/(:num)', 'Registration::payment/$1');
$routes->get('registration/paymentSuccess/(:num)', 'Registration::paymentSuccess/$1');
$routes->get('registration/paymentCancel/(:num)', 'Registration::paymentCancel/$1');
$routes->post('registration/paymentNotify', 'Registration::paymentNotify');
$routes->post('registration/uploadProof', 'Registration::uploadProof');

// Routes for Admin
$routes->get('admin', 'Admin::index');
$routes->get('all-administators', 'Manager::index');
$routes->post('add-manager', 'Manager::addManager');
$routes->get('staff', 'Manager::readAdmins');
$routes->get('read-admins', 'Manager::readAdmins');
$routes->get('signin', 'Admin::signin');
$routes->get('login', 'Admin::signin');
$routes->post('login', 'Login::auth');
$routes->get('logout', 'Login::logout');
$routes->get('dashboard', 'Admin::index');
$routes->get('pending', 'Admin::pending');
$routes->get('paid', 'Admin::paid');
$routes->get('waiting', 'Admin::waiting');
$routes->get('all', 'Admin::all');
$routes->get('all-users', 'Admin::allUsers');

$routes->get('admin/viewRegistration/(:num)', 'Admin::viewRegistration/$1');
$routes->post('admin/updateStatus/(:num)', 'Admin::updateStatus/$1');
$routes->get('admin/viewProof/(:any)', 'Admin::viewProof/$1');
$routes->get('admin/changePassword', 'Admin::changePassword');
$routes->post('admin/updatePassword', 'Admin::updatePassword');
$routes->get('admin/logout', 'Admin::logout');

// Route to view registration details
$routes->get('registration/view/(:num)', 'Admin::viewRegistration/$1');

// Route to update registration status via AJAX
$routes->post('registration/update-status/(:num)', 'Admin::updateStatus/$1');
// Routes for exports
$routes->get('export/excel', 'Admin::exportExcel');
$routes->get('export/pdf', 'Admin::exportPdf');