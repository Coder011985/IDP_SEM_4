<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;

use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\PaymentController;

use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\ProjectController as ClientProjectController;

use App\Http\Controllers\Designer\ProjectController as DesignerProjectController;
use App\Http\Controllers\Designer\BidController;

use App\Http\Controllers\Public\DesignerController;

use App\Http\Controllers\DesignerPortfolioController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ChatReactionController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FavoriteController;

use App\Models\User;
use App\Models\Project;
use App\Models\Payment;
use App\Models\ActivityLog;

use App\Mail\ProjectNotification;


/*
|--------------------------------------------------------------------------
| Email Test Route
|--------------------------------------------------------------------------
*/

Route::get('/test-email', function () {

    Mail::to('idp.portal@gmail.com')->send(
        new ProjectNotification(
            'Interior Design Portal Test',
            'Your email system is working successfully.',
            url('/')
        )
    );

    return "Email Sent Successfully";

});


/*
|--------------------------------------------------------------------------
| Public Landing Page
|--------------------------------------------------------------------------
*/

Route::get('/', function () {

    $topRated = User::topRated();
    $trending = User::trending();
    $rising = User::risingTalent();
    $recommended = User::recommended();
    $onlineDesigners = User::where('role','designer')
    ->where('last_seen_at','>=',now()->subMinutes(2))
    ->select('id','name','specialization','last_seen_at')
    ->limit(6)
    ->get();

    $activities = ActivityLog::with(['user','project'])
    ->latest()
    ->limit(10)
    ->get();

    $stats = [
        'designers' => User::where('role','designer')->count(),
        'projects' => Project::count(),
        'completed' => Project::where('status','completed')->count(),
        'revenue' => Payment::where('status','released')->sum('amount')
    ];

return view('welcome', compact(
    'topRated',
    'trending',
    'rising',
    'recommended',
    'onlineDesigners',
    'activities',
    'stats'
));

});


/*
|--------------------------------------------------------------------------
| Public Marketplace
|--------------------------------------------------------------------------
*/

Route::prefix('designers')
    ->name('public.designers.')
    ->group(function () {

        Route::get('/', [DesignerController::class,'index'])->name('index');

        Route::get('/{designer}', [DesignerController::class,'show'])->name('show');

        Route::middleware('auth')->post(
        '/{designer}/favorite',
        [FavoriteController::class,'toggle']
        )->name('favorite');

        Route::middleware('auth')->get(
            '/{designer}/hire',
            [DesignerController::class,'hire']
        )->name('hire');

});


/*
|--------------------------------------------------------------------------
| Dashboard Redirect
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->get('/dashboard', function () {

    $role = Auth::user()->role;

    return match ($role) {
        'admin' => redirect()->route('admin.projects'),
        'client' => redirect()->route('client.dashboard'),
        'designer' => redirect()->route('designer.projects'),
        default => abort(403),
    };

})->name('dashboard');


/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/notifications',
        [NotificationController::class,'index'])
        ->name('notifications.index');

    Route::post('/notifications/{notification}/read',
        [NotificationController::class,'markRead'])
        ->name('notifications.read');

    Route::post('/notifications/read-all',
        [NotificationController::class,'markAllRead'])
        ->name('notifications.readAll');

    Route::post('/user/ping', function () {

        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user) {
            $user->update([
                'last_seen_at' => now()
            ]);
        }

        return response()->json([
            'status' => 'ok'
        ]);

    })->name('user.ping');

});   // ✅ THIS closing bracket is required

/*
|--------------------------------------------------------------------------
| Chat Reactions
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->post(
    '/chat/{chat}/react',
    [ChatReactionController::class,'toggle']
)->name('chat.react');

Route::middleware('auth')->post(
    '/projects/{project}/seen',
    [ChatController::class,'seen']
)->name('chat.seen');
/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth','admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        Route::get('/projects', [AdminProjectController::class,'index'])->name('projects');

        Route::get('/projects/{project}/approve',
            [AdminProjectController::class,'approve'])
            ->name('projects.approve');

        Route::get('/projects/{project}/reject',
            [AdminProjectController::class,'reject'])
            ->name('projects.reject');

        Route::post('/projects/{project}/milestones/{milestone}/release',
            [AdminProjectController::class,'releaseMilestone'])
            ->name('milestones.release');


        Route::get('/payments',
            [PaymentController::class,'index'])
            ->name('payments.index');

        Route::post('/payments/{milestone}/release',
            [PaymentController::class,'release'])
            ->name('payments.release');


        Route::get('/disputes',
            [AdminProjectController::class,'disputes'])
            ->name('disputes');

        Route::post('/disputes/{dispute}/resolve',
            [AdminProjectController::class,'resolveDispute'])
            ->name('disputes.resolve');


        Route::get('/analytics',
            [AnalyticsController::class,'index'])
            ->name('analytics');

        Route::get('/analytics/live',
            [AnalyticsController::class,'liveData'])
            ->name('analytics.live');


        Route::get('/revenue',
            [AdminProjectController::class,'revenue'])
            ->name('revenue');

        Route::get('/revenue/export/{year}',
            [AnalyticsController::class,'exportRevenue'])
            ->name('revenue.export');


        Route::get('/activity', function () {

            $activities = ActivityLog::with(['user','project'])
                ->latest()
                ->paginate(15);

            return view('admin.activity', compact('activities'));

        })->name('activity');


        Route::controller(UserController::class)->group(function () {

            Route::get('/users','index')->name('users.index');

            Route::get('/users/{user}/edit','edit')->name('users.edit');

            Route::patch('/users/{user}','update')->name('users.update');

        });

});


/*
|--------------------------------------------------------------------------
| Client Dispute
|--------------------------------------------------------------------------
*/

Route::middleware(['auth','client'])->post(
    '/projects/{project}/dispute',
    [DisputeController::class,'store']
)->name('dispute.store');


/*
|--------------------------------------------------------------------------
| Designer Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth','designer'])
    ->prefix('designer')
    ->name('designer.')
    ->group(function () {

        Route::get('/projects',
            [DesignerProjectController::class,'index'])
            ->name('projects');

        Route::get('/projects/assigned',
            [DesignerProjectController::class,'assigned'])
            ->name('projects.assigned');

        Route::get('/projects/{project}/chat',
            [DesignerProjectController::class,'chat'])
           ->name('projects.chat');

            Route::get('/projects/{project}/messages',
           [DesignerProjectController::class,'loadMessages'])
           ->name('projects.messages');

        Route::post('/projects/{project}/chat/send',
            [DesignerProjectController::class,'sendMessage'])
            ->name('projects.chat.send');

        Route::post('/projects/{project}/typing',
            [DesignerProjectController::class,'typing'])
            ->name('projects.typing');

        Route::post('/projects/{project}/milestones/{milestone}/complete',
            [DesignerProjectController::class,'completeMilestone'])
            ->name('projects.completeMilestone');

        Route::get('/earnings',
            [DesignerProjectController::class,'earnings'])
            ->name('earnings');

        Route::post('/projects/{project}/bid',
            [BidController::class,'submitBid'])
            ->name('projects.submitBid');

        Route::resource('portfolio', DesignerPortfolioController::class)
            ->except(['show']);

});


/*
|--------------------------------------------------------------------------
| Client Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth','client'])
    ->prefix('client')
    ->name('client.')
    ->group(function () {

        Route::get('/dashboard',
            [ClientDashboardController::class,'index'])
            ->name('dashboard');

        Route::get('/favorites',
       [FavoriteController::class,'index'])
       ->name('favorites.index');

        Route::resource('projects', ClientProjectController::class)
            ->except(['edit','update','destroy']);

        Route::get('/projects/{project}/chat',
            [ClientProjectController::class,'chat'])
            ->name('projects.chat');

        Route::post('/projects/{project}/chat/send',
            [ClientProjectController::class,'sendMessage'])
            ->name('projects.chat.send');

        Route::post('/projects/{project}/typing',
           [ClientProjectController::class,'typing'])
           ->name('projects.typing');

        Route::get('/projects/{project}/bids',
            [ClientProjectController::class,'viewBids'])
            ->name('projects.bids');

        Route::post('/projects/{project}/bids/{bid}/accept',
            [ClientProjectController::class,'acceptBid'])
            ->name('projects.acceptBid');

        Route::post('/milestones/{milestone}/fund',
            [ClientProjectController::class,'fundMilestone'])
            ->name('milestones.fund');

        Route::post('/projects/{project}/review',
            [ReviewController::class,'store'])
            ->name('projects.review');

});


/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile',
        [ProfileController::class,'edit'])
        ->name('profile.edit');

    Route::patch('/profile',
        [ProfileController::class,'update'])
        ->name('profile.update');

    Route::delete('/profile',
        [ProfileController::class,'destroy'])
        ->name('profile.destroy');

});


require __DIR__.'/auth.php';
