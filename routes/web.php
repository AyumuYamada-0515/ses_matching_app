<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Engineer\SalesRepresentativeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Sales\AssignmentInvitationController;
use App\Http\Controllers\Sales\EngineerController;
use App\Http\Controllers\Sales\InterestController;
use App\Http\Controllers\Sales\ProjectController;
use App\Models\Project;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', function () {
        $user = auth()->user();

        return $user->isSales() ? view('sales.dashboard', ['projects' => $user->projects()->count(), 'pending' => $user->projects()->withCount(['interests' => fn ($q) => $q->where('status', 'pending')])->get()->sum('interests_count'), 'engineers' => $user->assignedEngineers()->count()]) : view('engineer.dashboard', ['open' => Project::where('status', 'open')->whereDate('application_deadline', '>=', today())->count(), 'interests' => $user->interests()->count(), 'matched' => $user->hasActiveMatch()]);
    })->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::prefix('sales')->name('sales.')->middleware('role:sales')->group(function () {
        Route::get('engineers', [EngineerController::class, 'index'])->name('engineers.index');
        Route::get('engineers/{engineer}', [EngineerController::class, 'show'])->name('engineers.show');
        Route::get('assignment-invitations', [AssignmentInvitationController::class, 'index'])->name('assignment-invitations.index');
        Route::post('assignment-invitations/{engineer}', [AssignmentInvitationController::class, 'store'])->name('assignment-invitations.store');
        Route::resource('projects', ProjectController::class);
        Route::get('interests', [InterestController::class, 'index'])->name('interests.index');
        Route::patch('interests/{interest}', [InterestController::class, 'update'])->name('interests.update');
    });
    Route::prefix('engineer')->name('engineer.')->middleware('role:engineer')->group(function () {
        Route::get('sales-representative', [SalesRepresentativeController::class, 'show'])->name('sales-representative.show');
        Route::get('assignment-invitations', [App\Http\Controllers\Engineer\AssignmentInvitationController::class, 'index'])->name('assignment-invitations.index');
        Route::patch('assignment-invitations/{invitation}', [App\Http\Controllers\Engineer\AssignmentInvitationController::class, 'update'])->name('assignment-invitations.update');
        Route::get('projects', [App\Http\Controllers\Engineer\ProjectController::class, 'index'])->name('projects.index');
        Route::get('projects/{project}', [App\Http\Controllers\Engineer\ProjectController::class, 'show'])->name('projects.show');
        Route::post('projects/{project}/interests', [App\Http\Controllers\Engineer\InterestController::class, 'store'])->name('interests.store');
        Route::get('interests', [App\Http\Controllers\Engineer\InterestController::class, 'index'])->name('interests.index');
        Route::delete('interests/{interest}', [App\Http\Controllers\Engineer\InterestController::class, 'destroy'])->name('interests.destroy');
    });
});
