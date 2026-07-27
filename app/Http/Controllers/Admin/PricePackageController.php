<?php

namespace Pterodactyl\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Prologue\Alerts\AlertsMessageBag;
use Pterodactyl\Http\Controllers\Controller;
use Pterodactyl\Http\Requests\Admin\PricePackageFormRequest;
use Pterodactyl\Models\Egg;
use Pterodactyl\Models\Node;
use Pterodactyl\Models\PricePackage;

class PricePackageController extends Controller
{
    public function __construct(private AlertsMessageBag $alert)
    {
    }

    public function index(): View
    {
        $packages = PricePackage::orderBy('sort')->orderBy('id')->paginate(25);

        return view('admin.packages.index', [
            'packages' => $packages,
        ]);
    }

    public function create(): View
    {
        return view('admin.packages.form', [
            'package' => new PricePackage(),
            'nodes' => Node::all(),
            'eggs' => Egg::all(),
            'selectedNodes' => [],
            'selectedEggs' => [],
        ]);
    }

    public function store(PricePackageFormRequest $request): RedirectResponse
    {
        $data = $this->extract($request);
        $package = PricePackage::create($data);
        $package->nodes()->sync($request->input('nodes', []));
        $package->eggs()->sync($request->input('eggs', []));

        $this->alert->success("Paket '{$package->name}' berhasil dibuat.")->flash();

        return redirect()->route('admin.packages.index');
    }

    public function edit(PricePackage $package): View
    {
        return view('admin.packages.form', [
            'package' => $package,
            'nodes' => Node::all(),
            'eggs' => Egg::all(),
            'selectedNodes' => $package->nodes()->pluck('nodes.id')->all(),
            'selectedEggs' => $package->eggs()->pluck('eggs.id')->all(),
        ]);
    }

    public function update(PricePackageFormRequest $request, PricePackage $package): RedirectResponse
    {
        $data = $this->extract($request);
        $package->update($data);
        $package->nodes()->sync($request->input('nodes', []));
        $package->eggs()->sync($request->input('eggs', []));

        $this->alert->success("Paket '{$package->name}' berhasil diperbarui.")->flash();

        return redirect()->route('admin.packages.index');
    }

    public function destroy(PricePackage $package): RedirectResponse
    {
        $name = $package->name;
        $package->delete();

        $this->alert->success("Paket '{$name}' berhasil dihapus.")->flash();

        return redirect()->route('admin.packages.index');
    }

    private function extract(PricePackageFormRequest $request): array
    {
        return [
            'name' => $request->input('name'),
            'slug' => $request->input('slug'),
            'description' => $request->input('description'),
            'price' => (int) $request->input('price'),
            'old_price' => $request->input('old_price') !== null && $request->input('old_price') !== '' ? (int) $request->input('old_price') : null,
            'ram' => (int) $request->input('ram'),
            'cpu' => (int) $request->input('cpu'),
            'disk' => (int) $request->input('disk'),
            'sort' => (int) $request->input('sort', 0),
            'is_active' => (bool) $request->input('is_active', false),
        ];
    }
}