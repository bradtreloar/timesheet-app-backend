<?php

namespace App\JsonApi\Users;

use Neomerx\JsonApi\Schema\SchemaProvider;

class Schema extends SchemaProvider
{

    /**
     * @var string
     */
    protected $resourceType = 'users';

    /**
     * @param \App\Models\User $resource
     *      the domain record being serialized.
     * @return string
     */
    public function getId($resource)
    {
        return (string) $resource->getRouteKey();
    }

    /**
     * @param \App\Models\User $resource
     *      the domain record being serialized.
     * @return array
     */
    public function getAttributes($resource)
    {
        return [
            'created' => $resource->created_at->toISO8601String(),
            'changed' => $resource->updated_at->toISO8601String(),
            'email' => $resource->email,
            'name' => $resource->name,
            'phoneNumber' => $resource->phone_number ?: "",
            'acceptsReminders' => $resource->accepts_reminders,
            'isAdmin' => $resource->is_admin,
        ];
    }

    public function getRelationships($resource, $isPrimary, array $includeRelationships)
    {
        return [
            'timesheets' => [
                self::SHOW_SELF => true,
                self::SHOW_RELATED => true,
            ],
            'presets' => [
                self::SHOW_SELF => true,
                self::SHOW_RELATED => true,
                self::SHOW_DATA => isset($includeRelationships['presets']),
                self::DATA => function () use ($resource) {
                    return $resource->presets;
                },
            ],
            'default_preset' => [
                self::SHOW_SELF => true,
                self::SHOW_RELATED => true,
                self::SHOW_DATA => isset($includeRelationships['default_preset']),
                self::DATA => function () use ($resource) {
                    return $resource->defaultPreset;
                },
            ],
        ];
    }
}
