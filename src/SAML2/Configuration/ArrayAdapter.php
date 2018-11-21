<?php

declare(strict_types=1);

namespace SAML2\Configuration;

/**
 * Default implementation for configuration
 */
class ArrayAdapter implements Queryable
{
    /**
     * @var array
     */
    private $configuration;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param string $key
     * @param mixed|null $defaultValue
     */
    public function get(string $key, $defaultValue = null)
    {
        if (!$this->has($key)) {
            return $defaultValue;
        }

        return $this->configuration[$key];
    }

    /**
     * @param string $key
     */
    public function has(string $key)
    {
        return array_key_exists($key, $this->configuration);
    }
}
