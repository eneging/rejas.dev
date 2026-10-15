<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Panel;
use App\Http\Controllers\Site;
use App\Support\Permissions as P;
use Illuminate\Support\Facades\Route;

/*
| Sitio público (portafolio + captación). No procesa pagos.
*/
Route::get('/', [Site\HomeController::class, 'index'])->name('home');
Route::get('/talleres', [Site\HomeController::class, 'workshop'])->name('workshop');
Route::get('/portafolio/{portfolioProject}', [Site\HomeController::class, 'caseStudy'])->name('portfolio.show');
Route::get('/cotizar', [Site\InquiryController::class, 'create'])->name('inquiry.create');
Route::post('/cotizar', [Site\InquiryController::class, 'store'])->middleware('throttle:5,1')->name('inquiry.store');
Route::view('/privacidad', 'site.privacy')->name('privacy');
Route::view('/terminos', 'site.terms')->name('terms');

/*
| Autenticación
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:10,1');
});
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

/*
| Panel privado
*/
Route::prefix('panel')->name('panel.')->middleware(['auth', 'active'])->group(function () {
    Route::get('/', Panel\DashboardController::class)->name('dashboard');

    Route::middleware('can:'.P::LEADS_MANAGE)->group(function () {
        Route::get('prospectos', [Panel\LeadController::class, 'index'])->name('leads.index');
        Route::get('prospectos/{lead}', [Panel\LeadController::class, 'show'])->name('leads.show');
        Route::patch('prospectos/{lead}', [Panel\LeadController::class, 'update'])->name('leads.update');
        Route::post('prospectos/{lead}/convertir', [Panel\LeadController::class, 'convert'])->name('leads.convert');
    });

    Route::middleware('can:'.P::CLIENTS_MANAGE)->group(function () {
        Route::resource('clientes', Panel\ClientController::class)
            ->parameters(['clientes' => 'client'])->names('clients')->except('destroy');
        Route::post('clientes/{client}/contactos', [Panel\ClientController::class, 'storeContact'])->name('clients.contacts.store');
    });

    Route::middleware('can:'.P::QUOTES_MANAGE)->group(function () {
        Route::resource('cotizaciones', Panel\QuoteController::class)
            ->parameters(['cotizaciones' => 'quote'])->names('quotes')->except('destroy');
        Route::get('cotizaciones/{quote}/imprimir', [Panel\QuoteController::class, 'print'])->name('quotes.print');
    });
    Route::middleware('can:'.P::QUOTES_SEND)->group(function () {
        Route::post('cotizaciones/{quote}/enviar', [Panel\QuoteController::class, 'send'])->name('quotes.send');
        Route::post('cotizaciones/{quote}/aceptar', [Panel\QuoteController::class, 'accept'])->name('quotes.accept');
        Route::post('cotizaciones/{quote}/rechazar', [Panel\QuoteController::class, 'reject'])->name('quotes.reject');
    });

    Route::get('proyectos', [Panel\ProjectController::class, 'index'])->name('projects.index');
    Route::get('proyectos/exportar', [Panel\ProjectController::class, 'export'])->name('projects.export');
    Route::middleware('can:'.P::PROJECTS_MANAGE)->group(function () {
        Route::get('proyectos/nuevo', [Panel\ProjectController::class, 'create'])->name('projects.create');
        Route::post('proyectos', [Panel\ProjectController::class, 'store'])->name('projects.store');
        Route::patch('proyectos/{project}', [Panel\ProjectController::class, 'update'])->name('projects.update');
        Route::put('proyectos/{project}/equipo', [Panel\ProjectController::class, 'members'])->name('projects.members');
    });
    Route::get('proyectos/{project}', [Panel\ProjectController::class, 'show'])->name('projects.show');

    Route::get('tareas', [Panel\TaskController::class, 'index'])->name('tasks.index');
    Route::post('proyectos/{project}/tareas', [Panel\TaskController::class, 'store'])->name('tasks.store');
    Route::patch('tareas/{task}', [Panel\TaskController::class, 'update'])->name('tasks.update');
    Route::patch('tareas/{task}/estado', [Panel\TaskController::class, 'status'])->name('tasks.status');
    Route::post('tareas/{task}/comentarios', [Panel\TaskController::class, 'comment'])->name('tasks.comment');

    Route::middleware('can:'.P::PAYMENTS_RECORD)->group(function () {
        Route::post('proyectos/{project}/pagos', [Panel\PaymentController::class, 'store'])->name('payments.store');
        Route::post('proyectos/{project}/plan-pagos', [Panel\PaymentController::class, 'storeSchedule'])->name('payments.schedule');
    });

    Route::get('finanzas', [Panel\FinanceController::class, 'index'])->middleware('can:'.P::FINANCE_VIEW)->name('finance.index');
    Route::post('finanzas/gastos', [Panel\FinanceController::class, 'storeExpense'])->middleware('can:'.P::FINANCE_VIEW_MARGINS)->name('finance.expenses.store');

    Route::middleware('can:'.P::SUBSCRIPTIONS_MANAGE)->group(function () {
        Route::resource('suscripciones', Panel\SubscriptionController::class)
            ->parameters(['suscripciones' => 'subscription'])->names('subscriptions')->only(['index', 'create', 'store', 'show', 'update']);
        Route::post('suscripciones-generar-cargos', [Panel\SubscriptionController::class, 'generate'])->name('subscriptions.generate');
        Route::post('cargos/{charge}/cobro', [Panel\SubscriptionController::class, 'pay'])->name('subscriptions.pay');
    });

    Route::get('calendario', [Panel\CalendarController::class, 'index'])->name('calendar.index');
    Route::get('calendario/exportar', [Panel\CalendarController::class, 'export'])->name('calendar.export');
    Route::post('calendario', [Panel\CalendarController::class, 'store'])->name('calendar.store');

    Route::middleware('can:'.P::PORTFOLIO_MANAGE)->group(function () {
        Route::resource('portafolio', Panel\PortfolioController::class)
            ->parameters(['portafolio' => 'portfolioProject'])->names('portfolio')->except(['show', 'destroy']);
    });

    Route::middleware('can:'.P::USERS_MANAGE)->group(function () {
        Route::resource('usuarios', Panel\UserController::class)
            ->parameters(['usuarios' => 'user'])->names('users')->except(['show', 'destroy']);
    });

    Route::get('auditoria', Panel\AuditController::class)->middleware('can:'.P::AUDIT_VIEW)->name('audit.index');
});
