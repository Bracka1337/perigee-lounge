<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyEarning;
use App\Models\Order;
use App\Models\PromoCodeUsage;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->query('role', 'all');

        $query = User::query();

        switch ($role) {
            case 'admin':
                $query->where('role', 'admin');
                break;
            case 'staff':
                $query->where('role', 'staff');
                break;
            case 'client':
                $query->where('role', 'client');
                break;
            case 'all':
            default:
                break;
        }

        $users = $query
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'currentFilter' => $role,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(['admin', 'staff'])],
        ]);

        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];
        $user->password = bcrypt($data['password']);

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('admin-users-status-created'));
    }

    public function edit(string $locale, User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(Request $request, string $locale, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => ['required', Rule::in(['admin', 'staff', 'client'])],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->role = $data['role'];

        if (isset($data['password']) && $data['password'] !== '' && $data['password'] !== null) {
            $user->password = bcrypt($data['password']);
        }

        $user->save();

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('admin-users-status-updated'));
    }

    public function destroy(string $locale, User $user): RedirectResponse
    {
        if (Auth::id() === $user->getKey()) {
            return back()->with('error', __('admin-users-error-self-delete'));
        }

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('status', __('admin-users-status-deleted'));
    }

    public function show(string $locale, User $user): View|RedirectResponse
    {
        // Only clients can be viewed in the history page.
        if (! $user->isClient()) {
            return redirect()->route('admin.users.index')
                ->with('error', __('admin-users-show-forbidden'));
        }

        // Orders
        $orders = $user->orders()
            ->with(['table'])
            ->orderByDesc('created_at')
            ->paginate(20);

        // Loyalty earnings (positive for accruals, negative for redemptions)
        $loyaltyEarnings = $user->loyaltyEarnings()
            ->with(['order'])
            ->orderByDesc('created_at')
            ->paginate(20);

        // Promo codes claimed via pivot
        $claimedPromoCodes = $user->promoCodes()
            ->with(['menuCategory', 'menuItem'])
            ->orderByDesc('pivot_claimed_at')
            ->get();

        // Promo code usages with amounts
        $promoUsages = PromoCodeUsage::where('user_id', $user->id)
            ->with(['promoCode', 'order'])
            ->orderByDesc('created_at')
            ->paginate(20);

        // Statistics summary
        $ordersCount = $user->orders()->count();
        $completedTotal = $user->orders()
            ->where('status', Order::STATUS_COMPLETED)
            ->sum('total');

        $loyaltyEarned = LoyaltyEarning::where('user_id', $user->id)
            ->where('amount', '>', 0)
            ->sum('amount');

        $loyaltyRedeemed = abs(LoyaltyEarning::where('user_id', $user->id)
            ->where('amount', '<', 0)
            ->sum('amount'));

        $promoClaimedCount = $user->promoCodes()->count();
        $promoUsedCount = PromoCodeUsage::where('user_id', $user->id)->count();
        $promoDiscountTotal = PromoCodeUsage::where('user_id', $user->id)->sum('discount_amount');

        $stats = [
            'orders_count' => $ordersCount,
            'completed_total' => $completedTotal,
            'loyalty_earned' => $loyaltyEarned,
            'loyalty_redeemed' => $loyaltyRedeemed,
            'promo_claimed' => $promoClaimedCount,
            'promo_used' => $promoUsedCount,
            'promo_discount_total' => $promoDiscountTotal,
            'loyalty_balance' => $user->loyalty_balance,
        ];

        return view('admin.users.show', [
            'user' => $user,
            'orders' => $orders,
            'loyaltyEarnings' => $loyaltyEarnings,
            'claimedPromoCodes' => $claimedPromoCodes,
            'promoUsages' => $promoUsages,
            'stats' => $stats,
        ]);
    }
}
