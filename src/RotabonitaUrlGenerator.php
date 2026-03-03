<?php

declare(strict_types=1);

namespace Rotabonita;

use Illuminate\Contracts\Routing\UrlRoutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Arr;

/**
 * Rotabonita URL Generator.
 *
 * Extends Laravel's UrlGenerator to intercept the point where Model
 * instances are converted into URL parameter strings.
 *
 * Normally, `route('posts.show', $post)` calls `$post->getRouteKey()`,
 * which returns the numeric primary key → `/posts/1`.
 *
 * This override checks for a `public_id` attribute on the model first,
 * and if present, uses it instead → `/posts/BYPWtH2qYos`.
 *
 * The developer writes 100% standard Laravel code. Nothing changes for them.
 */
final class RotabonitaUrlGenerator extends UrlGenerator
{
    /**
     * Format the array of URL parameters.
     *
     * This is called by Laravel internally every time `route()` or `url()`
     * receives a Model instance as a parameter. We intercept it here to
     * transparently substitute `public_id` when available.
     *
     * @param  mixed  $parameters
     * @return array
     */
    public function formatParameters($parameters): array
    {
        $parameters = Arr::wrap($parameters);

        foreach ($parameters as $key => $parameter) {
            if ($parameter instanceof Model && ! empty($parameter->public_id)) {
                // Model has a public_id → use it transparently.
                // The developer passes $post as before, URL becomes /posts/BYPWtH2qYos.
                $parameters[$key] = $parameter->public_id;
            } elseif ($parameter instanceof UrlRoutable) {
                // Any other UrlRoutable (no public_id) → default behaviour.
                $parameters[$key] = $parameter->getRouteKey();
            }
        }

        return $parameters;
    }
}
