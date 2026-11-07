<?php

namespace App\Http\Middleware;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Table;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTableBinding
{
    /**
     * Check if the authenticated user is already bound to another table.
     * If so, redirect them back with an error.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only check for authenticated clients
        if ($user === null || ! $user->isClient()) {
            return $next($request);
        }

        // Get the table code from the route
        $code = $request->route('code');
        if ($code === null) {
            return $next($request);
        }

        // Resolve table from code
        $requestedTable = $this->resolveTableFromCode($code);
        if ($requestedTable === null) {
            return $next($request);
        }

        // Check if user has an active order for a DIFFERENT table
        $existingOrder = Order::where('user_id', $user->id)
            ->whereNotIn('status', OrderStatus::closed())
            ->first();

        if ($existingOrder !== null && $existingOrder->table_id !== $requestedTable->id) {
            $boundTable = $existingOrder->table;
            $boundTableName = $boundTable?->name ?? "#{$existingOrder->table_id}";

            $locale = $request->route('locale') ?? app()->getLocale();

            return redirect()->route('menu', ['locale' => $locale])
                ->with('error', __('error-already-bound-to-table', ['table' => $boundTableName]));
        }

        return $next($request);
    }

    protected function resolveTableFromCode(string $code): ?Table
    {
        // First, try match by table name (case-insensitive), e.g. "z4" → "Z4"
        $byName = Table::whereRaw('LOWER(name) = ?', [strtolower($code)])->first();
        if ($byName !== null) {
            return $byName;
        }

        // Fallback: support numeric id (e.g. "4") or prefixed like "z4" meaning id=4
        $normalized = $code;
        if (strlen($normalized) > 1 && strtolower($normalized[0]) === 'z') {
            $normalized = substr($normalized, 1) ?: '';
        }
        if (ctype_digit($normalized)) {
            return Table::find((int) $normalized);
        }

        return null;
    }
}
