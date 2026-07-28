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
        $invoice = Invoice::with(['package', 'user', 'node', 'egg.variables'])->find($this->invoiceId);
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
            'image' => $this->firstDockerImage($egg),
            'database_limit' => 1,
            'allocation_limit' => 1,
            'backup_limit' => 1,
            'environment' => $this->buildEnvironment($egg),
            'start_on_completion' => true,
        ];

        try {
            /** @var Server $server */
            $server = $creationService->handle($data);
            $invoice->server_id = $server->id;
            $invoice->save();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation issue (e.g., missing egg variable). Re-throw so the job retries / fails.
            throw $e;
        } catch (\Throwable $e) {
            // Likely a side-effect failure (email/notification). Don't lose the server — find the just-created one
            // by matching name + owner + egg, then attach it to the invoice.
            $created = Server::query()
                ->where('owner_id', $user->id)
                ->where('egg_id', $egg->id)
                ->where('node_id', $node->id)
                ->where('name', $serverName)
                ->latest('id')
                ->first();
            if ($created) {
                $invoice->server_id = $created->id;
                $invoice->save();
                return;
            }
            throw $e;
        }
    }

    protected function firstDockerImage(\Pterodactyl\Models\Egg $egg): string
    {
        $images = $egg->docker_images;
        if (is_string($images)) {
            $decoded = json_decode($images, true);
            $images = is_array($decoded) ? $decoded : [];
        }
        if (is_array($images) && !empty($images)) {
            return (string) reset($images);
        }
        return (string) $images;
    }

    protected function buildEnvironment(\Pterodactyl\Models\Egg $egg): array
    {
        $env = [];
        foreach ($egg->variables as $variable) {
            $value = $variable->default_value;
            if ($value === null || $value === '') {
                $value = $this->inferDefault($variable);
            }
            $env[$variable->env_variable] = $value;
        }

        return $env;
    }

    protected function inferDefault(\Pterodactyl\Models\EggVariable $variable): string
    {
        $rules = (string) $variable->rules;

        // numeric|integer|max:N|min:N → 0
        if (preg_match('/\b(integer|numeric|digits)\b/', $rules)) {
            if (preg_match('/\bbetween:(\d+),(\d+)\b/', $rules, $m)) {
                return (string) ((int) $m[1] + (int) $m[2] / 2);
            }
            if (preg_match('/\bmax:(\d+)\b/', $rules, $m)) {
                return $m[1] === '1' ? '0' : '1';
            }
            return '0';
        }

        // boolean → "0"
        if (preg_match('/\bboolean\b/', $rules)) {
            return '0';
        }

        // url → http://localhost
        if (preg_match('/\burl\b/', $rules)) {
            return 'http://localhost';
        }

        // email
        if (preg_match('/\bemail\b/', $rules)) {
            return 'noreply@example.com';
        }

        // in:a,b,c → first option
        if (preg_match('/\bin:([^|]+)/', $rules, $m)) {
            $opts = explode(',', trim($m[1]));
            return trim($opts[0] ?? '');
        }

        // regex:/.../ → look for hard-coded literal
        if (preg_match('/regex:\/([^\/]+)\/([a-z]*)/', $rules, $m)) {
            $literal = $m[1];
            if (str_contains($literal, 'server.jar')) {
                return 'server.jar';
            }
            // Domain-shaped regex (CERTBOT_DOMAIN style: /^([\w\/\.\-\\:]+)$/)
            if (preg_match('/[a-zA-Z0-9_\\.\\\\\\/\\-\\:]/', $literal) && (str_contains($literal, '.') || str_contains($literal, ':'))) {
                return 'example.com';
            }
            return 'placeholder';
        }

        // string|max:N
        if (preg_match('/\bstring\b/', $rules)) {
            return '0';
        }

        return '';
    }
}