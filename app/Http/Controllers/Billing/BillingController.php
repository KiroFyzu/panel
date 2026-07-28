<?php

namespace Pterodactyl\Http\Controllers\Billing;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Models\Egg;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\PricePackage;
use Pterodactyl\Services\Billing\InvoiceService;

class BillingController extends Controller
{
    public function __construct(
        private AlertsMessageBag $alert,
        private InvoiceService $invoiceService,
    ) {
    }

    /**
     * Show all active packages + user's purchase history.
     */
    public function packages(Request $request): View
    {
        $packages = PricePackage::where('is_active', true)->orderBy('sort')->get();

        $invoices = $request->user()->invoices()
            ->with(['package', 'server'])
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('billing.packages', [
            'packages' => $packages,
            'invoices' => $invoices,
            'appName' => config('app.name', 'Pterodactyl'),
        ]);
    }

    /**
     * Show checkout page: pick node + egg for the chosen package.
     */
    public function checkout(Request $request, string $slug): View|RedirectResponse
    {
        $package = PricePackage::with(['nodes', 'eggs'])->where('slug', $slug)->where('is_active', true)->firstOrFail();

        if ($package->nodes->isEmpty() || $package->eggs->isEmpty()) {
            $this->alert->warning("Paket '{$package->name}' belum memiliki node/egg yang tersedia. Hubungi admin.")->flash();

            return redirect()->route('index');
        }

        return view('billing.checkout', [
            'package' => $package,
            'nodes' => $package->nodes,
            'eggs' => $package->eggs,
        ]);
    }

    /**
     * Process order: validate node+egg against package relations, create invoice.
     */
    public function pay(Request $request, string $slug): RedirectResponse
    {
        $package = PricePackage::with(['nodes', 'eggs'])->where('slug', $slug)->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'node_id' => 'required|integer',
            'egg_id' => 'required|integer',
        ]);

        // Ensure node + egg belong to this package.
        if (!$package->nodes->contains($validated['node_id'])) {
            return redirect()->back()->withErrors(['node_id' => 'Node tidak tersedia untuk paket ini.'])->withInput();
        }
        if (!$package->eggs->contains($validated['egg_id'])) {
            return redirect()->back()->withErrors(['egg_id' => 'Egg tidak tersedia untuk paket ini.'])->withInput();
        }

        $node = Node::findOrFail($validated['node_id']);
        $egg = Egg::findOrFail($validated['egg_id']);

        try {
            $invoice = $this->invoiceService->create($request->user(), $package, $node, $egg);
        } catch (\Throwable $e) {
            $this->alert->danger('Gagal membuat invoice: ' . $e->getMessage())->flash();

            return redirect()->back()->withInput();
        }

        return redirect()->route('billing.invoice.show', ['invoice' => $invoice->id]);
    }
}