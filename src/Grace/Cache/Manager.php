<?php

namespace Grace\Cache;

class Manager implements ManagerInterface
{
    public function get($presetName, $key, $ttl = 0, callable $cacheSetter = null)
    {
        $r = $this->presets[$presetName]->get($key);
        if (!$r) {
            $r = call_user_func($cacheSetter);
            $this->set($presetName, $key, $r);
        }
        return $r;
    }
    public function set($presetName, $key, $value, $ttl = 0)
    {
        $this->presets[$presetName]->set($key, $value, $ttl);
    }

    /**
     * @var PresetInterface[]
     */
    protected $presets = array();
    public function addPreset($presetName, PresetInterface $preset)
    {
        $this->presets[$presetName] = $preset;
    }
}