<?php

namespace Pterodactyl\Console\Commands\Server;

use Carbon\CarbonImmutable;
use Pterodactyl\Models\Server;
use Illuminate\Console\Command;
use Pterodactyl\Services\Servers\SuspensionService;

class SuspendExpiredServersCommand extends Command
{
    protected $signature = 'p:server:suspend-expired {--dry-run : Show expired servers without suspending them.}';

    protected $description = 'Suspend servers that have passed their configured expiration date.';

    public function __construct(private SuspensionService $suspensionService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $query = Server::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', CarbonImmutable::now())
            ->where(function ($query) {
                $query->whereNull('status')->orWhere('status', '!=', Server::STATUS_SUSPENDED);
            })
            ->whereDoesntHave('transfer')
            ->orderBy('expires_at');

        $count = $query->count();
        if (!$count) {
            $this->info('There are no expired servers to suspend.');

            return self::SUCCESS;
        }

        if ($this->option('dry-run')) {
            $this->warn("Found $count expired server(s).");
            $query->each(function (Server $server) {
                $this->line(sprintf(
                    '[%d] %s expired at %s',
                    $server->id,
                    $server->name,
                    $server->expires_at->toDateTimeString()
                ));
            });

            return self::SUCCESS;
        }

        $suspended = 0;
        $failed = 0;

        $this->warn("Suspending $count expired server(s).");

        $query->each(function (Server $server) use (&$suspended, &$failed) {
            try {
                $this->suspensionService->toggle($server, SuspensionService::ACTION_SUSPEND);
                ++$suspended;
            } catch (\Throwable $exception) {
                ++$failed;
                $this->error(sprintf(
                    'Failed to suspend server %d (%s): %s',
                    $server->id,
                    $server->name,
                    $exception->getMessage()
                ));
            }
        });

        $this->info("Suspended $suspended expired server(s).");

        if ($failed > 0) {
            $this->warn("Failed to suspend $failed expired server(s).");
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
