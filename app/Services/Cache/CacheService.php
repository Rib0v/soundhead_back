<?php

namespace App\Services\Cache;

use App\Contracts\CacheRepository;

class CacheService
{
    public function __construct(
        protected CacheRepository $cache,
        public bool $enableCache = true,
    ) {}

    /**
     * Возвращает данные из кеша. Если данных
     * в кеше нет, то предварительно сохраняет их
     * 
     * @param string $key  ключ для сохранения данных
     * @param \Closure $callback  замыкание для отложенного получения данных
     * @param int $seconds время  кеширования в секундах, 0 - без ограничений
     * @return mixed  закешированные данные
     */
    public function cacheAndGet(string $key, \Closure $callback, int $seconds = 0)
    {
        if (!$this->enableCache) {
            return $callback();
        }

        if (!$this->cache->has($key)) {
            $this->cache->set($key, $callback(), $seconds);
        }

        return $this->cache->get($key);
    }

    /**
     * Сохраняет данные в кеш только при их отсутствии
     * 
     * @param string $key  ключ для сохранения данных
     * @param \Closure $callback  замыкание для отложенного получения данных
     * @param int $seconds время  кеширования в секундах, 0 - без ограничений
     * @return bool  результат выполнения
     */
    public function cacheOnce(string $key, \Closure $callback, int $seconds = 0): bool
    {
        if (!$this->enableCache) {
            return true;
        }

        if (!$this->cache->has($key)) {
            return $this->cache->set($key, $callback(), $seconds);
        }

        return true;
    }

    /**
     * Сохраняет данные в кеш при каждом вызове
     * 
     * @param string $key  ключ для сохранения данных
     * @param \Closure $callback  замыкание для отложенного получения данных
     * @param int $seconds время  кеширования в секундах, 0 - без ограничений
     * @return bool  результат выполнения
     */
    public function cacheOnEveryCall(string $key, \Closure $callback, int $seconds = 0): bool
    {
        if (!$this->enableCache) {
            return true;
        }

        return $this->cache->set($key, $callback(), $seconds);
    }

    /**
     * Проверяет существование данных по ключу
     * 
     * @param string $key  ключ для проверки данных
     * @return bool  результат выполнения
     */
    public function has(string $key): bool
    {
        if (!$this->enableCache) {
            return true;
        }

        return $this->cache->has($key);
    }

    /**
     * Удаляет данные по ключу
     * 
     * @param string $key  ключ для удаления данных
     * @return bool  результат выполнения
     */
    public function del(string $key): bool
    {
        if (!$this->enableCache) {
            return true;
        }

        return $this->cache->del($key);
    }

    /**
     * Удаляет коллекцию по названию
     * 
     * @param string $name  название коллекции
     * @return int  количество удалённых записей
     */
    public function delCollection(string $name): int
    {
        $count = 0;

        $allKeys = $this->cache->getCollectionKeys($name);

        foreach ($allKeys as $key) {
            $count += (int)$this->cache->del($key);
        }

        return $count;
    }

    /**
     * Удаляет несколько коллекций по списку переданных названий
     * 
     * @param string[] $names  массив названий коллекций
     * @return int  количество удалённых записей
     */
    public function delCollections(array $names): int
    {
        $count = 0;

        foreach ($names as $name) {
            $count += $this->delCollection($name);
        }

        return $count;
    }
}
