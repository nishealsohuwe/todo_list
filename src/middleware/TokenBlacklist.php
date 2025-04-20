<?php
    namespace TodoList\Middleware;

    use Predis\Client;

    class TokenBlacklist {
        private Client $redis;

        public function __construct() {
            $this->redis = new Client([
                'scheme' => 'tcp',
                'host'   => $_ENV['REDIS_HOST'],
                'port'   => $_ENV['REDIS_PORT']
            ]);
        }

        public function add(string $token, int $exp): void {
            $this->redis->setex("blacklist:$token", $exp - time(), '1');
        }

        public function isBlacklisted(string $token): bool {
            return (bool) $this->redis->exists("blacklist:$token");
        }
    }