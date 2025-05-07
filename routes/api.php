<?php

use App\Models\User;
use Illuminate\Http\Request;
use App\Mail\BulkPropertyStored;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
use Illuminate\Mail\Mailables\Attachment;
use App\Http\Controllers\api\v1\AuthController;
use App\Http\Controllers\api\v1\PropertyController;
use App\Notifications\BulkPropertyStoredNotification;
use App\Http\Controllers\api\v1\UserNotificationController;

Route::prefix('v1')->group(function () {
    Route::any('/test', function(){
        dump(Attachment::fromPath(storage_path('/my/path')));
        dump(Attachment::fromPath(public_path('local', '/my/path')));
        dd(Attachment::fromPath(base_path('/my/path')));
    });

    Route::get('/mailable', function () {
        $user = User::find(1);

        return (new BulkPropertyStored($user, '9ed6b5f1-f487-4c87-a8a6-005b824ae9b7'))->render();
    });

    Route::get('/notification', function () {
        $user = User::find(1);

        return (new BulkPropertyStoredNotification($user))->toMail($user);
    });

    Route::post('/login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::prefix('user/notifications')->controller(UserNotificationController::class)->group(function () {
            Route::get('list', 'list');
            Route::get('read-list', 'readList');
            Route::get('unread-list', 'unreadList');
            Route::get('show/{id}', 'show');
            Route::post('mark-as-read/{id}', 'markAsRead');
            Route::post('mark-all-as-read', 'markAllAsRead');
            Route::delete('destroy/{id}', 'destroy');
            Route::delete('destroy-all-read', 'destroyAllRead');
            Route::delete('destroy-all-unread', 'destroyAllUnread');
            Route::delete('destroy-all', 'destroyAll');
        });
        Route::resource('property', PropertyController::class);
        Route::post('property/bulk-upload', [PropertyController::class, 'bulkUpload']);
    });
    Route::get('/batch-info/{id}', [PropertyController::class, 'batchInfo']);
});
