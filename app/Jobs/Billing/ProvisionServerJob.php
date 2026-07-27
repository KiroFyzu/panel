<?php

namespace Pterodactyl\Jobs\Billing;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Models\Allocation;
use Pterodactyl\Models\Invoice;
use Pterodactyl\Models\Server;
use Pterodactyl\Services\Servers\ServerCreationService;

class ProvisionServerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 300;

    public function __construct(public int $invoiceId)
    {
    }

    public function handle(ServerCreationService $creationService): void
    {
        $invoice = Invoice::with(['package', 'user', 'node', 'egg'])->find($this->invoiceId);
        if (!$invoice || $invoice->isPaid() === false || $invoice->server_id !== null) {
            return;
        }

        $package = $invoice->package;
        $user = $invoice->user;
        $node = $invoice->node;
        $egg = $invoice->egg;

        if (!$package || !$user || !$node || !$egg) {
            return;
        }

        // Auto-pick a free allocation on the chosen node.
        $allocation = Allocation::query()
            ->where('node_id', $node->id)
            ->whereNull('server_id')
            ->inRandomOrder()
            ->first();

        if (!$allocation) {
            throw new DisplayException("No free allocations available on node '{$node->name}' for invoice {$invoice->order_id}.");
        }

        $serverName = $package->name . '-' . $user->username . '-' . substr($invoice->order_id, -4);

        $data = [
            'name' => $serverName,
            'description' => "Auto-provisioned from invoice {$invoice->order_id}",
            'owner_id' => $user->id,
            'node_id' => $node->id,
            'allocation_id' => $allocation->id,
            'nest_id' => $egg->nest_id,
            'egg_id' => $egg->id,
            // Pterodactyl expects memory & disk in MB.
            'memory' => $package->ram * 1024, // GB -> MB
            'swap' => 0,
            'disk' => $package->disk * 1024, // GB -> MB
            'io' => 500,
            'cpu' => $package->cpu, // %
            'threads' => null,
            'oom_disabled' => true,
            'startup' => $egg->startup,
            'image' => $egg->docker_image,
            'database_limit' => 1,
            'allocation_limit' => 1,
            'backup_limit' => 1,
            'environment' => [],
            'start_on_completion' => true,
        ];

        /** @var Server $server */
        $server = $creationService->handle($data);

        $invoice->server_id = $server->id;
        $invoice->save();
    }
}