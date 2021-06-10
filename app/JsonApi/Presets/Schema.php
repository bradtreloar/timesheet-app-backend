<?php

namespace App\JsonApi\Presets;

use Neomerx\JsonApi\Schema\SchemaProvider;

class Schema extends SchemaProvider
{

    /**
     * @var string
     */
    protected $resourceType = 'presets';

    /**
     * @param \App\Models\Preset $resource
     *      the domain record being serialized.
     * @return string
     */
    public function getId($resource)
    {
        return (string) $resource->getRouteKey();
    }

    /**
     * @param \App\Models\Preset $resource
     *      the domain record being serialized.
     * @return array
     */
    public function getAttributes($resource)
    {
        return [
            'created' => $resource->created_at->toISO8601String(),
            'changed' => $resource->updated_at->toISO8601String(),
            'values' => $resource->values,
        ];
    }

    public function getRelationships($resource, $isPrimary, array $includeRelationships)
    {
        return [
            'user' => [
                self::SHOW_SELF => true,
                self::SHOW_RELATED => true,
            ],
        ];
    }
}
