<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Отображение списка логов активности
     */
    public function index(Request $request): View
    {
        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        // Фильтр по пользователю
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Фильтр по типу модели
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // Фильтр по действию
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Фильтр по дате
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Поиск по ID модели
        if ($request->filled('model_id')) {
            $query->where('model_id', $request->model_id);
        }

        $logs = $query->paginate(50);

        // Получаем уникальные типы моделей для фильтра
        $modelTypes = ActivityLog::distinct()
            ->pluck('model_type')
            ->mapWithKeys(function ($type) {
                $modelMap = [
                    'App\\Models\\Order' => 'Заказ',
                    'App\\Models\\MenuItem' => 'Блюдо',
                    'App\\Models\\MenuCategory' => 'Категория меню',
                    'App\\Models\\Reservation' => 'Бронирование',
                    'App\\Models\\Table' => 'Столик',
                    'App\\Models\\User' => 'Пользователь',
                    'App\\Models\\PromoCode' => 'Промокод',
                ];
                return [$type => $modelMap[$type] ?? class_basename($type)];
            });

        return view('staff.logs.index', compact('logs', 'modelTypes'));
    }
}
