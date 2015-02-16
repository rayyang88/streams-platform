<?php namespace Anomaly\Streams\Platform\Ui\Table\Component\Filter\Handler;

use Anomaly\Streams\Platform\Ui\Table\Component\Filter\Contract\FilterInterface;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class GenericFilterHandler
 *
 * @link          http://anomaly.is/streams-platform
 * @author        AnomalyLabs, Inc. <hello@anomaly.is>
 * @author        Ryan Thompson <ryan@anomaly.is>
 * @package       Anomaly\Streams\Platform\Ui\Table\Component\Filter\Handler
 */
class GenericFilterHandler
{

    /**
     * Handle the filter.
     *
     * @param Builder         $query
     * @param FilterInterface $filter
     */
    public function handle(Builder $query, FilterInterface $filter)
    {
        $query->where($filter->getSlug(), 'LIKE', "%{$filter->getValue()}%");
    }
}