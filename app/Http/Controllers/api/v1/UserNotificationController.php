<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{

    /**
     * list
     *
     * @return void
     */
    public function list()
    {
        return response()->json(auth()->user()->notifications, 200);
    }

    /**
     * readList
     *
     * @return void
     */
    public function readList()
    {
        return response()->json(auth()->user()->readNotifications, 200);
    }

    /**
     * unreadList
     *
     * @return void
     */
    public function unreadList()
    {
        return response()->json(auth()->user()->unreadNotifications, 200);
    }

    /**
     * show
     *
     * @param  String $id
     * @return void
     */
    public function show(String $id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        return response()->json($notification, 200);
    }

    /**
     * markAsRead
     *
     * @param  String $id
     * @return void
     */
    public function markAsRead(String $id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Successfully marked as read'], 200);
    }

    /**
     * markAllAsRead
     *
     * @return void
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications()->update(['read_at' => now()]);
        return response()->json(['message' => 'Successfully marked all as read'], 200);
    }

    /**
     * destroy
     *
     * @param  String $id
     * @return void
     */
    public function destroy(String $id)
    {
        $notification = auth()->user()->notifications()->find($id);

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->delete();

        return response()->json(['message' => 'Successfully removed the notification'], 200);
    }

    /**
     * destroyAllRead
     *
     * @return void
     */
    public function destroyAllRead()
    {
        auth()->user()->readnotifications()->delete();
        return response()->json(['message' => 'Successfully removed all read notofication'], 200);
    }

    /**
     * destroyAllUnread
     *
     * @return void
     */
    public function destroyAllUnread()
    {
        auth()->user()->unreadnotifications()->delete();
        return response()->json(['message' => 'Successfully removed all unread notofication'], 200);
    }

    /**
     * destroyAll
     *
     * @return void
     */
    public function destroyAll()
    {
        auth()->user()->notifications()->delete();
        return response()->json(['message' => 'Successfully removed all notofication'], 200);
    }
}
