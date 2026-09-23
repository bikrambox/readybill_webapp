<?php

namespace Modules\GroceryIndia\Http\Controllers\API;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\GroceryIndia\Entities\Notification;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Modules\Core\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * GET /api/notifications
     * Fetch paginated notifications for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();

            $notifications = Notification::where('user_id', $user->user_id)
                ->latest()
                ->paginate($request->get('per_page', 20));

            return ResponseHelper::responseFn(1, 200, __('validation.Data fetched successfully'), $notifications);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized'),
        ], 401);
    }

    /**
     * GET /api/notifications/all
     * Fetch all notifications without pagination (for dropdown/bell icon)
     */
    public function all(): JsonResponse
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();

            $notifications = Notification::where('user_id', $user->user_id)
                ->latest()
                ->get();

            return ResponseHelper::responseFn(1, 200, __('validation.Data fetched successfully'), $notifications);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized'),
        ], 401);
    }

    /**
     * GET /api/notifications/unread-count
     * Get total unread notification count
     */
    public function unreadCount(): JsonResponse
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();

            $count = Notification::where('user_id', $user->user_id)
                ->where('is_read', false)
                ->count();

            return ResponseHelper::responseFn(1, 200, __('validation.Data fetched successfully'), [['unread_count' => $count]]);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized'),
        ], 401);
    }

    /**
     * GET /api/notifications/unread
     * Fetch only unread notifications
     */
    public function unread(): JsonResponse
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();

            $notifications = Notification::where('user_id', $user->user_id)
                ->where('is_read', false)
                ->latest()
                ->get();

            return ResponseHelper::responseFn(1, 200, __('validation.Data fetched successfully'), $notifications);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized'),
        ], 401);
    }

    /**
     * GET /api/notifications/filter?type=stock_alert
     * Filter notifications by type
     */
    public function filter(Request $request): JsonResponse
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();

            $request->validate([
                'type' => 'required|in:subscription_expiry,stock_alert',
            ]);

            $notifications = Notification::where('user_id', $user->user_id)
                ->where('type', $request->type)
                ->latest()
                ->get();

            return ResponseHelper::responseFn(1, 200, __('validation.Data fetched successfully'), $notifications);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized'),
        ], 401);
    }

    /**
     * PUT /api/notifications/{id}/read
     * Mark a single notification as read
     */
    public function markRead(int $id): JsonResponse
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();

            $notification = Notification::where('user_id', $user->user_id)
                ->where('id', $id)
                ->first();

            if (!$notification) {
                return ResponseHelper::responseFn(0, 404, __('validation.Data not found'), []);
            }

            $notification->update(['is_read' => true]);

            return ResponseHelper::responseFn(1, 200, __('validation.Updated successfully'), []);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized'),
        ], 401);
    }

    /**
     * PUT /api/notifications/mark-all-read
     * Mark all notifications as read
     */
    public function markAllRead(): JsonResponse
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();

            Notification::where('user_id', $user->user_id)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return ResponseHelper::responseFn(1, 200, __('validation.Updated successfully'), []);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized'),
        ], 401);
    }

    /**
     * DELETE /api/notifications/{id}
     * Delete a single notification
     */
    public function destroy(int $id): JsonResponse
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();

            $notification = Notification::where('user_id', $user->user_id)
                ->where('id', $id)
                ->first();

            if (!$notification) {
                return ResponseHelper::responseFn(0, 404, __('validation.Data not found'), []);
            }

            $notification->delete();

            return ResponseHelper::responseFn(1, 200, __('validation.Deleted successfully'), []);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized'),
        ], 401);
    }

    /**
     * DELETE /api/notifications/clear-all
     * Delete all notifications for authenticated user
     */
    public function clearAll(): JsonResponse
    {
        if (Auth::guard('api')->check()) {
            $user = Auth::guard('api')->user();

            Notification::where('user_id', $user->user_id)->delete();

            return ResponseHelper::responseFn(1, 200, __('validation.Deleted successfully'), []);
        }

        return response()->json([
            'status' => 'failed',
            'message' => __('validation.Unauthorized'),
        ], 401);
    }
    
}
