<?php
use App\Controllers\UserController;
use App\Controllers\MenuController;
use App\Controllers\OrderController;
use App\Controllers\TestimoniController;
use App\Controllers\ReportController;
use App\Middleware\RoleMiddleware;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use App\Controllers\AdminController;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->add(\Slim\Middleware\MethodOverrideMiddleware::class); 

// MIDDLEWARE UTAMA
$app->addRoutingMiddleware(); 
$app->addBodyParsingMiddleware(); 
$app->addErrorMiddleware(true, true, true); 

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
        ->withHeader('Access-Control-Allow-Origin', '*') 
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, X-Role')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$twig = Twig::create(__DIR__ . '/../src/Views', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

$adminController = new AdminController($twig);

$app->get('/login', function ($request, $response) use ($twig) {
    return $twig->render($response, 'login.php'); 
});

$app->get('/logout', function ($request, $response) {

    session_start();
    session_destroy();

    return $response
        ->withHeader('Location', '/login')
        ->withStatus(302);
});

$app->get('/dashboard/owner', function ($request, $response) use ($twig) {
    return $twig->render($response, 'dashboard_owner.php'); 
});

$app->get('/dashboard/kasir', function ($request, $response) use ($twig) {
    return $twig->render($response, 'dashboard_kasir.php'); 
});

$app->get('/report/penjualan', function ($request, $response) use ($twig) {
    return $twig->render($response, 'report_penjualan.php');
});

$app->get('/dashboard/summary', function ($request, $response) use ($twig) {
    return $twig->render($response, 'dashboard_summary.php');
});

$app->get('/manage/menu', function ($request, $response) use ($twig) {
    return $twig->render($response, 'manage_menu.php');
});

$app->get('/manage/pesanan', function ($request, $response) use ($twig) {
    return $twig->render($response, 'manage_pesanan.php');
});

$app->get('/manage/user', function ($request, $response) use ($twig) {
    return $twig->render($response, 'manage_user.php');
});

$app->get('/manage/testimoni', function ($request, $response) use ($twig) {
    return $twig->render($response, 'manage_testimoni.php');
});

$app->post('/users/register', UserController::class . ':register');
$app->post('/auth/login', UserController::class . ':login');
$app->get('/menu', MenuController::class . ':getAll'); 
$app->get('/testimoni', TestimoniController::class . ':getApproved'); 
$app->get('/users', UserController::class . ':getAllUsers'); 
$app->put('/users/{id}', UserController::class . ':updateUser'); 
$app->delete('/users/{id}', UserController::class . ':deleteUser'); 

$app->group('', function ($group) {
    $group->post('/order', OrderController::class . ':createOrder');
    $group->post('/testimoni', TestimoniController::class . ':create');
})->add(new RoleMiddleware(['pelanggan', 'kasir', 'pemilik']));

$app->group('/kasir', function ($group) {
    $group->get('/orders', OrderController::class . ':getAllOrders');
    $group->get('/order/{id}', OrderController::class . ':getOrderDetail');
    $group->put('/order/{id}/status', OrderController::class . ':updateStatus');
})->add(new RoleMiddleware(['kasir', 'pemilik']));

$app->group('/owner', function ($group) {
    $group->get('/laporan/harian', ReportController::class . ':dailyReport');
    $group->get('/laporan/bulanan', ReportController::class . ':monthlyReport');
    $group->post('/menu', MenuController::class . ':create');
    $group->map(['PUT', 'POST'], '/menu/{id}', MenuController::class . ':update');
    $group->delete('/menu/{id}', MenuController::class . ':delete');
    $group->put('/testimoni/{id}/approve', TestimoniController::class . ':approve');
    $group->delete('/testimoni/{id}', TestimoniController::class . ':delete');
})->add(new RoleMiddleware(['pemilik']));

$app->group('/admin', function ($group) use ($adminController) {
    $group->get('/dashboard', [$adminController, 'index']);
    $group->get('/menu', [$adminController, 'listMenu']);
    $group->get('/testimoni', [$adminController, 'listTestimoni']);
    $group->post('/order/update', [$adminController, 'updateStatus']);
});

$app->post('/payment/notification', \App\Controllers\MidtransHandler::class . ':handleNotification');
$app->post('/notification/handling', \App\Controllers\MidtransController::class . ':handleNotification');
$app->get('/orders/user/{id_user}', \App\Controllers\OrderController::class . ':getCustomerOrders');
$app->get('/orders/detail/{id}', \App\Controllers\OrderController::class . ':getOrderDetail');
$app->get('/admin/orders/date/{date}', \App\Controllers\OrderController::class . ':getOrdersByDate');
$app->get('/admin/orders', \App\Controllers\OrderController::class . ':getAllOrders');
$app->put('/admin/orders/complete/{id}', \App\Controllers\OrderController::class . ':updateStatusSelesai');
$app->get('/admin/reports', \App\Controllers\OrderController::class . ':getReports');
$app->put('/admin/testimoni/approve/{id}', \App\Controllers\TestimoniController::class . ':approveTestimoni');
$app->get('/owner/testimoni', [TestimoniController::class, 'getAll']);
$app->put('/users/update/{id}', \App\Controllers\UserController::class . ':updateProfile');

$app->run();