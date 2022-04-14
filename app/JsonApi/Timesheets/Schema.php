<?php

namespace App\JsonApi\Timesheets;

use Neomerx\JsonApi\Schema\SchemaProvider;

class Schema extends SchemaProvider
{

    /**
     * @var string
     */
    protected $resourceType = 'timesheets';

    /**
     * @param \App\Models\Timesheet $resource
     *      the domain record being serialized.
     * @return string
     */
    public function getId($resource)
    {
        return (string) $resource->getRouteKey();
    }

    /**
     * @param \App\Models\Timesheet $resource
     *      the domain record being serialized.
     * @return array
     */
    public function getAttributes($resource)
    {
        return [
            'created' => $resource->created_at->toISO8601String(),
            'changed' => $resource->updated_at->toISO8601String(),
            'submitted' => $resource->submitted_at
                ? $resource->submitted_at->toISO8601String()
                : null,
            'email_sent_at' => $resource->email_sent_at
                ? $resource->email_sent_at->toISO8601String()
                : null,
            'comment' => $resource->comment,
        ];
    }

    public function getRelationships($resource, $isPrimary, array $includeRelationships)
    {
        return [
            'user' => [
                self::SHOW_SELF => true,
                self::SHOW_RELATED => true,
            ],
            'shifts' => [
                self::SHOW_SELF => true,
                self::SHOW_RELATED => true,
                self::SHOW_DATA => isset($includeRelationships['shifts']),
                self::DATA => function () use ($resource) {
                    return $resource->shifts;
                },
            ],
            'absences' => [
                self::SHOW_SELF => true,
                self::SHOW_RELATED => true,
                self::SHOW_DATA => isset($includeRelationships['absences']),
                self::DATA => function () use ($resource) {
                    return $resource->absences;
                },
            ]
        ];
    }
}
