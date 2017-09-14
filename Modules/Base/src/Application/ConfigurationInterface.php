<?php

namespace Framework\Base\Application;

interface ConfigurationInterface
{
    public function __construct(array $configurationValues = []);

    public function setPathValue(string $dotPath, $value);

    public function getPathValue(string $dotPath);

    public function getAll();
}
