<?php

namespace App\EventSubscriber;

use App\Event\GameUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Cache\CacheItemPoolInterface;

class GameEventSubscriber implements EventSubscriberInterface
{
    private $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            GameUpdatedEvent::NAME => 'onGameUpdated',
        ];
    }

    public function onGameUpdated(GameUpdatedEvent $event)
    {
        $game = $event->getGame();
        $cacheKey = 'game_' . $game->getId();
        $cacheItem = $this->cache->getItem($cacheKey);

        $cacheItem->set($game);
        $cacheItem->expiresAfter(86400); // TTL 24 часа
        $this->cache->save($cacheItem);
    }
}
