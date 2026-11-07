<?php

namespace App\Http\Controllers;

use App\Actions\Orders\ConfirmTableCart;
use App\Exceptions\BusinessRuleException;
use App\Http\Requests\Client\AddToTableCartRequest;
use App\Http\Requests\Client\UpdateTableCartRequest;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Table;
use App\Services\TableCart;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;

/**
 * Ordering from a table's QR code: the client fills a session cart, then sends it
 * to the open order staff started for that table.
 */
class ClientTableOrderController extends Controller
{
    public function __construct(private TableCart $cart) {}

    public function show(string $locale, string $code): View|RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('menu', ['locale' => $locale]);
        }

        $table = Table::findByQrCode($code);
        if ($table === null) {
            return $this->tableNotFound($locale);
        }

        $redirect = $this->redirectIfBoundElsewhere($locale, $table);
        if ($redirect !== null) {
            return $redirect;
        }

        return view('pages.menu', [
            'categories' => $this->menu(),
            'qrTable' => $table,
            'qrCode' => $code,
        ]);
    }

    public function addToCart(AddToTableCartRequest $request, string $locale, string $code): RedirectResponse
    {
        $table = Table::findByQrCode($code);
        if ($table === null) {
            return $this->tableNotFound($locale);
        }

        if (! $table->is_active) {
            return redirect()->route('client.table.menu', ['locale' => $locale, 'code' => $code])
                ->with('error', __('table-unavailable'));
        }

        if (auth()->check()) {
            $redirect = $this->redirectIfBoundElsewhere($locale, $table);
            if ($redirect !== null) {
                return $redirect;
            }

            $order = Order::active()->where('table_id', $table->id)->first();

            if ($order !== null && $order->user_id !== null && $order->user_id !== auth()->id()) {
                return redirect()->route('client.table.menu', ['locale' => $locale, 'code' => $code])
                    ->with('error', __('table-occupied'));
            }
        }

        $menuItemId = (int) $request->validated('menu_item_id');

        if (! MenuItem::with('category')->findOrFail($menuItemId)->isOrderable()) {
            return back()->with('error', __('menu-unavailable'));
        }

        $this->cart->add($table->id, $menuItemId, (int) ($request->validated('quantity') ?? 1));

        return back()->with('success', __('item-added-to-cart'));
    }

    public function cart(string $locale, string $code): View|RedirectResponse
    {
        if (! auth()->check()) {
            return redirect()->route('menu', ['locale' => $locale]);
        }

        $table = Table::findByQrCode($code);
        if ($table === null) {
            return $this->tableNotFound($locale);
        }

        $summary = $this->cart->summary($table->id);

        return view('client.table.cart', [
            'table' => $table,
            'cartItems' => $summary['lines'],
            'cartTotal' => $summary['total'],
            'code' => $code,
            'categories' => $this->menu(),
        ]);
    }

    public function confirm(string $locale, string $code, ConfirmTableCart $confirmTableCart): RedirectResponse
    {
        $table = Table::findByQrCode($code);
        if ($table === null) {
            return $this->tableNotFound($locale);
        }

        if (! $table->is_active) {
            return $this->backToCart($locale, $code, 'table-unavailable');
        }

        $redirect = auth()->check() ? $this->redirectIfBoundElsewhere($locale, $table) : null;
        if ($redirect !== null) {
            return $redirect;
        }

        try {
            $order = $confirmTableCart->handle($table, auth()->user());
        } catch (BusinessRuleException $e) {
            return $this->backToCart($locale, $code, $e->translationKey);
        }

        // Unverified clients can't open their order page yet, so they go back to the menu.
        $target = auth()->user()?->hasVerifiedEmail()
            ? redirect()->route('client.orders.show', ['locale' => $locale, 'order' => $order])
            : redirect()->route('client.table.menu', ['locale' => $locale, 'code' => $code]);

        return $target->with('success', __('order-sent-to-waiter'));
    }

    public function updateCart(UpdateTableCartRequest $request, string $locale, string $code, int $itemId): RedirectResponse
    {
        $table = Table::findByQrCode($code);
        if ($table === null) {
            return $this->tableNotFound($locale);
        }

        $this->cart->setQuantity($table->id, $itemId, (int) $request->validated('qty'));

        return redirect()->route('client.table.cart.show', ['locale' => $locale, 'code' => $code]);
    }

    public function removeFromCart(string $locale, string $code, int $itemId): RedirectResponse
    {
        $table = Table::findByQrCode($code);
        if ($table === null) {
            return $this->tableNotFound($locale);
        }

        $this->cart->remove($table->id, $itemId);

        return redirect()->route('client.table.cart.show', ['locale' => $locale, 'code' => $code]);
    }

    private function redirectIfBoundElsewhere(string $locale, Table $table): ?RedirectResponse
    {
        $otherTable = auth()->user()?->tableBoundOtherThan($table->id);

        return $otherTable === null ? null : redirect()->route('menu', ['locale' => $locale])
            ->with('error', __('error-already-bound-to-table', ['table' => $otherTable]));
    }

    private function tableNotFound(string $locale): RedirectResponse
    {
        return redirect()->route('menu', ['locale' => $locale])->with('error', __('table-not-found'));
    }

    private function backToCart(string $locale, string $code, string $error): RedirectResponse
    {
        return redirect()->route('client.table.cart.show', ['locale' => $locale, 'code' => $code])
            ->with('error', __($error));
    }

    /**
     * @return Collection<int, MenuCategory>
     */
    private function menu()
    {
        return MenuCategory::with(['items' => fn ($query) => $query->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();
    }
}
